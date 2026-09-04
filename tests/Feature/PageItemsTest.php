<?php

namespace Tests\Feature;

use App\Models\DinamikMenu;
use App\Models\FormMenu;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Sahifa yozuvlari — admin paneldan qoʻshib, saytda koʻrish.
 *
 * `form_menus` uchala koʻrinish uchun ham yozuv jadvali:
 *   files  — yuklab olinadigan fayllar (nomi, izohi, sanasi, muqovasi)
 *   cards  — kartochkalar (bosilganda alohida sahifa)
 *   single — matn ichidagi boʻlimlar
 */
class PageItemsTest extends TestCase
{
    private array $trash = [];

    private array $uploaded = [];

    protected function tearDown(): void
    {
        foreach ($this->trash as $model) {
            $model->forceDelete();
        }

        foreach ($this->uploaded as $name) {
            @unlink(public_path('upload/files/' . $name));
        }

        parent::tearDown();
    }

    private function admin(): User
    {
        return User::where('role', 'admin')->orderBy('id')->firstOrFail();
    }

    private function api(string $path, string $locale = 'uz')
    {
        $prefix = trim((string) config('api.prefix'), '/');

        return $this->withHeaders(['Accept-Language' => $locale])->getJson('/' . $prefix . $path);
    }

    /** Berilgan koʻrinishda sinov sahifasi yaratadi. */
    private function makePage(string $layout): DinamikMenu
    {
        $suffix = uniqid();

        $menu = Menu::create([
            'title'  => ['uz' => 'Sinov ' . $layout . ' ' . $suffix],
            'slug'   => 'sinov-' . $layout . '-' . $suffix,
            'path'   => '/sinov-' . $layout . '-' . $suffix,
            'order'  => 980,
            'active' => 0,
        ]);
        $this->trash[] = $menu;

        $page = DinamikMenu::create([
            'menu_id' => $menu->id,
            'title'   => ['uz' => 'Sinov sahifa'],
            'layout'  => $layout,
            'active'  => 1,
        ]);
        $this->trash[] = $page;

        return $page;
    }

    public function test_file_added_in_admin_appears_on_the_site(): void
    {
        $page = $this->makePage('files');
        $name = 'Oʻquv reja ' . uniqid();

        $this->actingAs($this->admin())
            ->post('/admin/dynamic-menus/' . $page->id . '/items', [
                'title'    => ['uz' => $name, 'ru' => $name, 'en' => $name],
                'desc'     => ['uz' => 'Qisqacha izoh', 'ru' => 'Izoh', 'en' => 'Note'],
                'date'     => '2026-03-15',
                'active'   => 1,
                'order'    => 1,
                // `create()` diskda boʻsh fayl qoldiradi — hajmni tekshirish
                // uchun haqiqiy mazmunli fayl kerak.
                'document' => UploadedFile::fake()->createWithContent(
                    'reja.pdf',
                    str_repeat('x', 4096)
                ),
            ])
            ->assertStatus(302);

        $item = FormMenu::where('dinamik_menu_id', $page->id)->first();

        $this->assertNotNull($item, 'Yozuv yaratilmadi');
        $this->assertNotNull($item->file, 'Fayl saqlanmadi');
        $this->uploaded[] = $item->file;

        $this->assertFileExists(public_path('upload/files/' . $item->file));

        // Fayl asl nomi bilan saqlanmasligi kerak — bir xil nomlilar
        // bir-birini oʻchirib yuborardi.
        $this->assertStringNotContainsString('reja.pdf', $item->file);

        $files = $this->api('/pages/' . $page->menu->slug)->assertOk()->json('data.files');

        $this->assertCount(1, $files);
        $this->assertSame($name, $files[0]['title']);
        $this->assertSame('Qisqacha izoh', $files[0]['desc']);
        $this->assertSame('2026-03-15', $files[0]['date']);
        $this->assertNotNull($files[0]['id'], 'Barqaror kalit boʻlishi kerak');
        $this->assertNotNull($files[0]['url']);
        $this->assertGreaterThan(0, $files[0]['size']);
    }

    public function test_files_are_returned_newest_first(): void
    {
        $page = $this->makePage('files');

        foreach (['2024-01-10', '2026-05-20', '2025-09-01'] as $index => $date) {
            $item = FormMenu::create([
                'dinamik_menu_id' => $page->id,
                'slug'            => 'fayl-' . $index . '-' . uniqid(),
                'title'           => ['uz' => 'Fayl ' . $date],
                'file'            => 'sinov-' . $index . '.pdf',
                'date'            => $date,
                'active'          => 1,
                'order'           => $index,
            ]);
            $this->trash[] = $item;
        }

        $dates = collect($this->api('/pages/' . $page->menu->slug)->assertOk()->json('data.files'))
            ->pluck('date')
            ->all();

        $this->assertSame(['2026-05-20', '2025-09-01', '2024-01-10'], $dates);
    }

    public function test_file_without_a_date_is_still_returned(): void
    {
        $page = $this->makePage('files');

        $item = FormMenu::create([
            'dinamik_menu_id' => $page->id,
            'slug'            => 'sanasiz-' . uniqid(),
            'title'           => ['uz' => 'Sanasiz fayl'],
            'file'            => 'sanasiz.pdf',
            'active'          => 1,
        ]);
        $this->trash[] = $item;

        $files = $this->api('/pages/' . $page->menu->slug)->assertOk()->json('data.files');

        $this->assertCount(1, $files);
        $this->assertNull($files[0]['date'], 'Sana boʻsh boʻlsa null qaytishi kerak');
    }

    public function test_hidden_file_is_not_returned(): void
    {
        $page = $this->makePage('files');

        $item = FormMenu::create([
            'dinamik_menu_id' => $page->id,
            'slug'            => 'yashirin-' . uniqid(),
            'title'           => ['uz' => 'Yashirin fayl'],
            'file'            => 'yashirin.pdf',
            'active'          => 0,
        ]);
        $this->trash[] = $item;

        $this->assertSame(
            [],
            $this->api('/pages/' . $page->menu->slug)->assertOk()->json('data.files')
        );
    }

    public function test_card_detail_returns_the_new_fields(): void
    {
        $page = $this->makePage('cards');

        $item = FormMenu::create([
            'dinamik_menu_id' => $page->id,
            'slug'            => 'kartochka-' . uniqid(),
            'title'           => ['uz' => 'Kartochka'],
            'subtitle'        => ['uz' => 'Qoʻshimcha sarlavha'],
            'text'            => ['uz' => 'Qisqacha'],
            'body'            => ['uz' => '<p>Toʻliq matn</p>'],
            'video'           => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'image'           => 'muqova.jpg',
            'photo'           => ['galereya-1.jpg', 'galereya-2.jpg'],
            'date'            => '2026-04-01',
            'active'          => 1,
        ]);
        $this->trash[] = $item;

        $data = $this->api('/pages/' . $page->menu->slug . '/' . $item->slug)
            ->assertOk()
            ->json('data');

        $this->assertSame('Qoʻshimcha sarlavha', $data['subtitle']);
        $this->assertSame('<p>Toʻliq matn</p>', $data['body']);
        $this->assertSame($item->video, $data['video']);
        $this->assertSame('2026-04-01', $data['date']);

        // Muqova alohida, galereya alohida — muqova takrorlanmaydi.
        $this->assertStringContainsString('muqova.jpg', $data['image']);
        $this->assertCount(2, $data['images']);
        $this->assertArrayHasKey('lg', $data['images'][0]);
    }

    public function test_page_level_video_and_gallery_are_served(): void
    {
        $page = $this->makePage('single');

        $page->forceFill([
            'video'  => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'images' => ['bir.jpg', 'ikki.jpg'],
        ])->save();

        $data = $this->api('/pages/' . $page->menu->slug)->assertOk()->json('data');

        $this->assertSame($page->video, $data['video']);
        $this->assertCount(2, $data['images']);
        $this->assertArrayHasKey('md', $data['images'][0]);
    }

    public function test_dangerous_file_type_is_rejected(): void
    {
        $page = $this->makePage('files');

        $this->actingAs($this->admin())
            ->post('/admin/dynamic-menus/' . $page->id . '/items', [
                'title'    => ['uz' => 'Zararli'],
                'document' => UploadedFile::fake()->create('zararli.php', 10, 'application/x-php'),
            ]);

        $this->assertSame(
            0,
            FormMenu::where('dinamik_menu_id', $page->id)->count(),
            'PHP fayl qabul qilinmasligi kerak'
        );
    }

    public function test_items_screen_opens_for_every_layout(): void
    {
        foreach (DinamikMenu::LAYOUTS as $layout) {
            $page = $this->makePage($layout);

            foreach (['', '/create'] as $suffix) {
                $this->actingAs($this->admin())
                    ->get('/admin/dynamic-menus/' . $page->id . '/items' . $suffix)
                    ->assertOk();
            }
        }
    }
}
