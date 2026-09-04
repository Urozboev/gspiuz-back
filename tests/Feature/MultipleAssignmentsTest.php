<?php

namespace Tests\Feature;

use App\Models\Employ;
use App\Models\EmployMeta;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Bitta xodim bir nechta boʻlimda ishlashi.
 *
 * Masalan prorektor ayni paytda institut kengashi aʼzosi ham boʻlishi
 * mumkin. Bunda:
 *   – xodim ikkala boʻlim sahifasida ham koʻrinishi kerak;
 *   – har bir sahifada aynan oʻsha boʻlimdagi lavozimi yozilishi kerak
 *     (ilgari doim birinchi tayinlov koʻrsatilardi);
 *   – xodim sahifasida barcha tayinlovlar roʻyxati boʻlishi kerak.
 */
class MultipleAssignmentsTest extends TestCase
{
    private ?EmployMeta $extra = null;

    /** Test oʻzi yaratgan yozuvlar — oxirida oʻchiriladi. */
    private ?EmployMeta $seededMeta = null;

    private ?Employ $seededEmploy = null;

    protected function tearDown(): void
    {
        $this->extra?->forceDelete();
        $this->seededMeta?->forceDelete();
        $this->seededEmploy?->forceDelete();

        parent::tearDown();
    }

    private function api(string $path, string $locale = 'uz')
    {
        $prefix = trim((string) config('api.prefix'), '/');

        return $this->withHeaders(['Accept-Language' => $locale])->getJson('/' . $prefix . $path);
    }

    /**
     * Sinov uchun xodim topadi, boʻlmasa oʻzi yaratadi.
     *
     * Baza boʻsh boʻlishi mumkin (namunaviy maʼlumot tozalangandan
     * keyin), shuning uchun test mavjud yozuvga tayanmaydi.
     */
    private function anyAssignment(): EmployMeta
    {
        $existing = EmployMeta::with('department')->whereNotNull('department_id')->orderBy('id')->first();

        if ($existing) {
            return $existing;
        }

        $department = DB::table('departments')->orderBy('id')->first();

        $this->assertNotNull($department, 'Boʻlim topilmadi — tuzilma boʻsh');

        $name = ['uz' => 'Sinov', 'ru' => 'Sinov', 'en' => 'Sinov'];

        $this->seededEmploy = Employ::create([
            'first_name' => $name,
            'last_name' => ['uz' => 'Xodimov', 'ru' => 'Xodimov', 'en' => 'Xodimov'],
            'surname' => $name,
            'slug' => 'sinov-xodimov-' . uniqid(),
        ]);

        return $this->seededMeta = EmployMeta::create([
            'employ_id'       => $this->seededEmploy->id,
            'department_id'   => $department->id,
            'position_id'     => 4,
            'employ_staff_id' => 1,
            'employ_form_id'  => 1,
            'employ_type_id'  => 2,
            'slug'            => 'birinchi-tayinlov-' . uniqid(),
            'active'          => 1,
            'order'           => 1,
        ]);
    }

    /** Xodimga ikkinchi boʻlimda tayinlov beradi. */
    private function assignSecondDepartment(): array
    {
        $first = $this->anyAssignment();

        $other = DB::table('departments')
            ->where('id', '!=', $first->department_id)
            ->orderBy('id')
            ->first();

        $this->assertNotNull($other, 'Ikkinchi boʻlim topilmadi');

        $this->extra = EmployMeta::create([
            'employ_id'       => $first->employ_id,
            'department_id'   => $other->id,
            'position_id'     => 4,
            'employ_staff_id' => 1,
            'employ_form_id'  => 1,
            'employ_type_id'  => 2,
            'slug'            => 'ikkinchi-tayinlov-' . uniqid(),
            'active'          => 1,
            'order'           => 2,
        ]);

        return [$first, $this->extra, $other];
    }

    public function test_employee_appears_in_both_departments(): void
    {
        [$first, $second] = $this->assignSecondDepartment();

        $employ = Employ::findOrFail($first->employ_id);
        $lastName = $employ->last_name['uz'] ?? '';

        foreach ([$first->department->slug, $second->department->slug] as $slug) {
            $response = $this->api('/department/' . $slug)->assertOk();

            $body = $response->getContent();

            $this->assertStringContainsString(
                $lastName,
                $body,
                "Xodim {$slug} boʻlimida koʻrinmadi"
            );
        }
    }

    public function test_each_department_page_shows_its_own_position(): void
    {
        [$first, $second] = $this->assignSecondDepartment();

        $employId = $first->employ_id;

        // Birinchi boʻlim sahifasi — birinchi tayinlov.
        $this->assertSame(
            $first->department_id,
            $this->metaDepartmentFor($first->department->slug, $employId),
            'Birinchi boʻlim sahifasida notoʻgʻri tayinlov'
        );

        // Ikkinchi boʻlim sahifasi — ikkinchi tayinlov.
        $this->assertSame(
            $second->department_id,
            $this->metaDepartmentFor($second->department->slug, $employId),
            'Ikkinchi boʻlim sahifasida ham birinchi tayinlov koʻrsatilyapti'
        );
    }

    /** Sahifadagi xodimning `employ_meta.department_id` qiymati. */
    private function metaDepartmentFor(string $departmentSlug, int $employId): ?int
    {
        $json = $this->api('/department/' . $departmentSlug)->assertOk()->json();

        $rows = array_filter(array_merge(
            [$json['department_boss'] ?? null],
            $json['simple_employee'] ?? []
        ));

        foreach ($rows as $row) {
            if (($row['id'] ?? null) === $employId) {
                return $row['employ_meta']['department_id'] ?? null;
            }
        }

        return null;
    }

    public function test_employee_page_lists_every_assignment(): void
    {
        [$first, $second] = $this->assignSecondDepartment();

        $response = $this->api('/leaderships/' . $first->slug)->assertOk();

        $assignments = $response->json('assignments');

        $this->assertIsArray($assignments);
        $this->assertGreaterThanOrEqual(2, count($assignments), 'Ikkala tayinlov ham chiqishi kerak');

        // Ochilgan tayinlov "current" deb belgilanadi.
        $current = collect($assignments)->where('current', true);

        $this->assertCount(1, $current, 'Aynan bitta tayinlov joriy boʻlishi kerak');
        $this->assertSame($first->slug, $current->first()['slug'] ?? null);

        // Ikkinchi tayinlov ham roʻyxatda.
        $slugs = collect($assignments)->pluck('slug')->all();

        $this->assertContains($second->slug, $slugs);
    }

    public function test_inactive_assignment_is_not_listed(): void
    {
        [$first, $second] = $this->assignSecondDepartment();

        $second->forceFill(['active' => 0])->save();

        $slugs = collect($this->api('/leaderships/' . $first->slug)->assertOk()->json('assignments'))
            ->pluck('slug')
            ->all();

        $this->assertNotContains($second->slug, $slugs, 'Nofaol tayinlov chiqmasligi kerak');
    }
}
