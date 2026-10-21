<?php

namespace Tests\Feature;

use App\Models\DinamikMenu;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Admin paneldan "hech narsani oʻzgartirmay saqlash" saytni oʻzgartirmasligi kerak.
 *
 * Foydalanuvchi: "sen qoʻshgan maʼlumotlarni admin paneldan tahrirlasam
 * frontda koʻrinmay qolyapti". Bu test formani xuddi brauzer kabi
 * ochadi, undagi barcha maydonlarni joriy qiymatlari bilan yuboradi va
 * saytdagi javob bir xil qolganini tekshiradi.
 *
 * Bazadagi haqiqiy sahifalar ishlatiladi, test oxirida hammasi asl
 * holiga qaytariladi.
 */
class UnchangedSaveRoundTripTest extends TestCase
{
    /** Asl holatni tiklash uchun saqlangan qatorlar. */
    private array $snapshots = [];

    protected function tearDown(): void
    {
        foreach ($this->snapshots as [$table, $id, $row]) {
            DB::table($table)->where('id', $id)->update($row);
        }

        // Test davomida oʻchgan elementlar boʻlsa — qaytaramiz.
        DB::table('form_menus')->where('deleted_at', '>=', now()->subMinutes(5))->update(['deleted_at' => null]);

        parent::tearDown();
    }

    private function admin(): User
    {
        return User::where('role', 'admin')->orderBy('id')->firstOrFail();
    }

    private function api(string $path)
    {
        $prefix = trim((string) config('api.prefix'), '/');

        return $this->withHeaders(['Accept-Language' => 'uz'])->getJson('/' . $prefix . $path);
    }

    private function remember(string $table, int $id): void
    {
        $this->snapshots[] = [$table, $id, (array) DB::table($table)->where('id', $id)->first()];
    }

    /**
     * Formadagi maydonlarni brauzer yuboradigan koʻrinishda yigʻadi:
     * belgilanmagan checkbox va tugmalar yuborilmaydi, select'dan
     * tanlangani olinadi.
     */
    private function formFields(string $html, string $action): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?>' . $html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $form = null;

        foreach ($xpath->query('//form') as $candidate) {
            if (str_contains(rtrim($candidate->getAttribute('action'), '/'), rtrim($action, '/'))) {
                $form = $candidate;
                break;
            }
        }

        $this->assertNotNull($form, "Forma topilmadi: {$action}");

        $pairs = [];

        foreach ($xpath->query('.//input|.//textarea|.//select', $form) as $field) {
            $name = $field->getAttribute('name');

            if ($name === '' || $field->hasAttribute('disabled')) {
                continue;
            }

            $tag = strtolower($field->nodeName);
            $type = strtolower($field->getAttribute('type'));

            if ($tag === 'input' && in_array($type, ['submit', 'button', 'file', 'image', 'reset'], true)) {
                continue;
            }

            if ($tag === 'input' && in_array($type, ['checkbox', 'radio'], true) && !$field->hasAttribute('checked')) {
                continue;
            }

            if ($tag === 'textarea') {
                $value = $field->textContent;
            } elseif ($tag === 'select') {
                $value = '';
                $options = $xpath->query('.//option', $field);

                foreach ($options as $index => $option) {
                    if ($index === 0) {
                        $value = $option->getAttribute('value');
                    }

                    if ($option->hasAttribute('selected')) {
                        $value = $option->getAttribute('value');
                        break;
                    }
                }
            } else {
                $value = $field->getAttribute('value');
            }

            $pairs[] = urlencode($name) . '=' . urlencode($value);
        }

        parse_str(implode('&', $pairs), $data);

        return $data;
    }

    /** Sahifani formasi orqali oʻzgarishsiz saqlaydi. */
    private function resavePage(DinamikMenu $page): void
    {
        $this->remember('dinamik_menus', $page->id);

        $action = route('dynamic-menus.update', $page->id);
        $html = $this->actingAs($this->admin())->get(route('dynamic-menus.edit', $page->id))->assertOk()->getContent();

        $data = $this->formFields($html, $action);
        unset($data['_method']);

        $this->actingAs($this->admin())->put($action, $data)->assertRedirect()->assertSessionHasNoErrors();
    }

    private function pageBySlug(string $slug): ?DinamikMenu
    {
        $menuIds = DB::table('menus')->where('slug', $slug)->pluck('id');

        return DinamikMenu::whereIn('menu_id', $menuIds)->first();
    }

    /** API javobidan vaqtga bogʻliq maydonlarni olib tashlaydi. */
    private function stable(array $json): array
    {
        array_walk_recursive($json, function (&$value, $key) {
            if (in_array($key, ['updated_at', 'created_at'], true)) {
                $value = null;
            }
        });

        return $json;
    }

    public static function pages(): array
    {
        return [
            'Institut haqida' => ['about'],
            'Institut tarixi' => ['institute-history'],
            'Bosh sahifa' => ['home'],
            'Qabul' => ['admissions'],
            'Ekofaol talabalar' => ['eco-students'],
        ];
    }

    /** @dataProvider pages */
    public function test_saving_a_page_unchanged_keeps_the_site_unchanged(string $slug): void
    {
        $page = $this->pageBySlug($slug);

        if (!$page) {
            $this->markTestSkipped("Sahifa bazada yoʻq: {$slug}");
        }

        $before = $this->stable($this->api('/pages/' . $slug)->assertOk()->json());

        $this->resavePage($page);

        $after = $this->stable($this->api('/pages/' . $slug)->assertOk()->json());

        $this->assertSame(
            json_encode($before, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            json_encode($after, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            "'{$slug}' sahifasi oʻzgarishsiz saqlanganda saytdagi koʻrinishi oʻzgardi"
        );
    }

    public function test_saving_a_department_unchanged_keeps_the_site_unchanged(): void
    {
        $department = DB::table('departments')
            ->join('employ_metas', 'employ_metas.department_id', '=', 'departments.id')
            ->whereNull('departments.deleted_at')
            ->select('departments.*')
            ->first();

        if (!$department) {
            $this->markTestSkipped('Xodimi bor boʻlim yoʻq.');
        }

        $this->remember('departments', $department->id);

        $before = $this->stable($this->api('/department/' . $department->slug)->assertOk()->json());

        $action = route('departaments.update', $department->id);
        $html = $this->actingAs($this->admin())->get(route('departaments.edit', $department->id))->assertOk()->getContent();
        $data = $this->formFields($html, $action);
        unset($data['_method']);

        $this->actingAs($this->admin())->put($action, $data)->assertRedirect()->assertSessionHasNoErrors();

        $after = $this->stable($this->api('/department/' . $department->slug)->assertOk()->json());

        $this->assertEquals($before, $after, 'Boʻlim oʻzgarishsiz saqlanganda saytdagi koʻrinishi oʻzgardi');
    }

    /**
     * "Boʻlim haqida" formatlangan matni (qalin, roʻyxat) saqlanganda
     * yoʻqolmasligi kerak.
     *
     * Xato: forma matnni `<textarea>` ichiga xom HTML qilib qoʻyardi.
     * Brauzer uni matn deb oʻqiydi, lekin `</textarea>` uchrasa forma
     * buziladi, ayrim hollarda esa teglar tushib qoladi. Toʻgʻrisi —
     * qochirilgan (`&lt;p&gt;`) koʻrinish, CKEditor uni qayta ochadi.
     */
    public function test_rich_department_description_survives_an_unchanged_save(): void
    {
        $department = DB::table('departments')->whereNull('deleted_at')->orderBy('id')->first();

        $this->remember('departments', $department->id);

        $html = '<p><strong>Qalin</strong> va <em>qiya</em>.</p><ul><li>Birinchi</li><li>Ikkinchi</li></ul><p>&lt;/textarea&gt; ham buzmasin</p>';

        DB::table('departments')->where('id', $department->id)->update([
            'description' => json_encode(['uz' => $html, 'ru' => $html, 'en' => $html], JSON_UNESCAPED_UNICODE),
        ]);

        $action = route('departaments.update', $department->id);
        $page = $this->actingAs($this->admin())->get(route('departaments.edit', $department->id))->assertOk()->getContent();

        $this->assertStringNotContainsString('<textarea class="form-control ckeditor" name="description[uz]" rows="8"><p>', $page,
            'Matn textarea ichiga xom HTML boʻlib tushgan');

        $data = $this->formFields($page, $action);
        unset($data['_method']);

        $this->assertSame($html, $data['description']['uz'] ?? null, 'Brauzer formadan boshqa matn oʻqiydi');

        $this->actingAs($this->admin())->put($action, $data)->assertRedirect()->assertSessionHasNoErrors();

        $saved = json_decode((string) DB::table('departments')->where('id', $department->id)->value('description'), true);

        $this->assertSame($html, $saved['uz']);
    }

    public function test_saving_an_employee_unchanged_keeps_the_site_unchanged(): void
    {
        $meta = DB::table('employ_metas')
            ->join('departments', 'departments.id', '=', 'employ_metas.department_id')
            ->whereNull('employ_metas.deleted_at')
            ->where('employ_metas.active', 1)
            ->select('employ_metas.employ_id', 'departments.slug')
            ->first();

        if (!$meta) {
            $this->markTestSkipped('Boʻlimga biriktirilgan xodim yoʻq.');
        }

        $this->remember('employs', $meta->employ_id);

        $before = $this->stable($this->api('/department/' . $meta->slug)->assertOk()->json());

        $action = route('employs.update', $meta->employ_id);
        $html = $this->actingAs($this->admin())->get(route('employs.edit', $meta->employ_id))->assertOk()->getContent();
        $data = $this->formFields($html, $action);
        unset($data['_method']);

        // Rasm brauzerda JavaScript orqali formaga qoʻshiladi — xuddi shunday qilamiz.
        $photo = DB::table('employs')->where('id', $meta->employ_id)->value('photo');
        if ($photo) {
            $data['dropzone_images'] = $photo;
        }

        $this->actingAs($this->admin())->put($action, $data)->assertRedirect()->assertSessionHasNoErrors();

        $after = $this->stable($this->api('/department/' . $meta->slug)->assertOk()->json());

        $this->assertEquals($before, $after, 'Xodim oʻzgarishsiz saqlanganda saytdagi koʻrinishi oʻzgardi');
    }
}
