<?php

namespace Tests\Feature;

use App\Models\DinamikMenu;
use App\Models\FormMenu;
use App\Models\Menu;
use App\Models\User;
use Tests\TestCase;

/**
 * Admin paneldan yaratilgan sahifa saytda ko'rinishi.
 *
 * Bu 7-bandning to'liq aylanasi: menyu bandi yaratiladi → unga sahifa
 * biriktiriladi → ko'rinish turi tanlanadi → sahifa `GET /pages/{slug}`
 * orqali chiqadi. Har bir bo'g'inda uzilish bo'lsa shu test ko'rsatadi.
 */
class PageRoundTripTest extends TestCase
{
    private array $trash = [];

    protected function tearDown(): void
    {
        foreach ($this->trash as $model) {
            $model->forceDelete();
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

    public function test_menu_created_in_admin_appears_in_the_site_menu(): void
    {
        $title = 'Sinov menyu bandi ' . uniqid();

        $this->actingAs($this->admin())->post('/admin/menus', [
            'title' => ['uz' => $title, 'ru' => $title, 'en' => $title],
            'order' => 99,
            'path'  => '/sinov-band',
        ]);

        $menu = Menu::where('title->uz', $title)->first();

        $this->assertNotNull($menu, 'Menyu bandi yaratilmadi');
        $this->trash[] = $menu;

        $shown = collect($this->api('/menu')->assertOk()->json())
            ->pluck('title');

        $this->assertContains($title, $shown->all(), 'Yangi band saytdagi menyuda koʻrinmadi');
    }

    /** @dataProvider layouts */
    public function test_page_created_in_admin_is_served_with_its_layout(string $layout): void
    {
        $suffix = uniqid();

        $menu = Menu::create([
            'title'  => ['uz' => 'Sinov sahifa ' . $suffix],
            'slug'   => 'sinov-sahifa-' . $suffix,
            'path'   => '/sinov-sahifa-' . $suffix,
            'order'  => 990,
            'active' => 1,
        ]);
        $this->trash[] = $menu;

        // Admin paneldagi forma yuboradigan maydonlar.
        $response = $this->actingAs($this->admin())->post('/admin/dynamic-menus', [
            'menu_id'     => $menu->id,
            'layout'      => $layout,
            'title'       => ['uz' => 'Sinov sahifa ' . $suffix],
            'short_title' => ['uz' => 'Qisqacha izoh'],
        ]);

        $response->assertStatus(302);

        $page = DinamikMenu::where('menu_id', $menu->id)->first();

        $this->assertNotNull($page, 'Sahifa yaratilmadi');
        $this->trash[] = $page;

        $this->assertSame($layout, $page->layout, 'Tanlangan koʻrinish saqlanmadi');

        $this->api('/pages/' . $menu->slug)
            ->assertOk()
            ->assertJsonPath('data.layout', $layout)
            ->assertJsonPath('data.slug', $menu->slug);
    }

    public static function layouts(): array
    {
        return [
            'bitta sahifa' => ['single'],
            'kartochkalar' => ['cards'],
            'fayllar'      => ['files'],
        ];
    }

    public function test_unknown_layout_falls_back_to_single(): void
    {
        $suffix = uniqid();

        $menu = Menu::create([
            'title'  => ['uz' => 'Notoʻgʻri tur ' . $suffix],
            'slug'   => 'notogri-tur-' . $suffix,
            'path'   => '/notogri-tur-' . $suffix,
            'order'  => 991,
            'active' => 1,
        ]);
        $this->trash[] = $menu;

        $this->actingAs($this->admin())->post('/admin/dynamic-menus', [
            'menu_id' => $menu->id,
            'layout'  => 'zararli-qiymat',
            'title'   => ['uz' => 'Notoʻgʻri tur ' . $suffix],
        ]);

        $page = DinamikMenu::where('menu_id', $menu->id)->first();
        $this->trash[] = $page;

        $this->assertSame('single', $page->layout, 'Notoʻgʻri qiymat single ga tushishi kerak');
    }

    public function test_page_text_survives_and_is_served_as_html(): void
    {
        $suffix = uniqid();

        $menu = Menu::create([
            'title'  => ['uz' => 'Matnli sahifa ' . $suffix],
            'slug'   => 'matnli-sahifa-' . $suffix,
            'path'   => '/matnli-sahifa-' . $suffix,
            'order'  => 992,
            'active' => 1,
        ]);
        $this->trash[] = $menu;

        $html = '<h2>Sarlavha</h2><p>Word hujjatidan koʻchirilgan <strong>matn</strong>.</p>';

        // Admin formasidagi "Sahifa matni" maydoni orqali. Bu maydon formada
        // umuman yoʻq edi: sahifa yaratilardi, lekin matnini yozadigan joy
        // boʻlmagani uchun "bitta sahifa" koʻrinishi doim boʻsh chiqardi.
        $this->actingAs($this->admin())->post('/admin/dynamic-menus', [
            'menu_id' => $menu->id,
            'layout'  => 'single',
            'title'   => ['uz' => 'Matnli sahifa'],
            'text'    => ['uz' => $html],
        ])->assertStatus(302);

        $page = DinamikMenu::where('menu_id', $menu->id)->first();

        $this->assertNotNull($page, 'Sahifa yaratilmadi');
        $this->trash[] = $page;

        $this->api('/pages/' . $menu->slug)
            ->assertOk()
            ->assertJsonPath('data.body', $html);
    }

    public function test_inactive_page_is_not_served(): void
    {
        $suffix = uniqid();

        $menu = Menu::create([
            'title'  => ['uz' => 'Yashirin sahifa ' . $suffix],
            'slug'   => 'yashirin-sahifa-' . $suffix,
            'path'   => '/yashirin-sahifa-' . $suffix,
            'order'  => 993,
            'active' => 1,
        ]);
        $this->trash[] = $menu;

        $page = DinamikMenu::create([
            'menu_id' => $menu->id,
            'title'   => ['uz' => 'Yashirin sahifa'],
            'layout'  => 'single',
            'active'  => 0,
        ]);
        $this->trash[] = $page;

        $this->api('/pages/' . $menu->slug)->assertNotFound();
    }

    public function test_block_slug_stays_unique_within_a_page(): void
    {
        $suffix = uniqid();

        $menu = Menu::create([
            'title'  => ['uz' => 'Kartochkali ' . $suffix],
            'slug'   => 'kartochkali-' . $suffix,
            'path'   => '/kartochkali-' . $suffix,
            'order'  => 994,
            'active' => 1,
        ]);
        $this->trash[] = $menu;

        $page = DinamikMenu::create([
            'menu_id' => $menu->id,
            'title'   => ['uz' => 'Kartochkali sahifa'],
            'layout'  => 'cards',
            'active'  => 1,
        ]);
        $this->trash[] = $page;

        $first = FormMenu::create([
            'dinamik_menu_id' => $page->id,
            'slug'            => 'bir-xil',
            'title'           => ['uz' => 'Birinchi'],
            'order'           => 1,
            'active'          => 1,
        ]);
        $this->trash[] = $first;

        // Bir sahifada bir xil manzil ikki marta bo'lmasligi kerak —
        // bazada unikal indeks turibdi.
        $this->expectException(\Illuminate\Database\QueryException::class);

        FormMenu::create([
            'dinamik_menu_id' => $page->id,
            'slug'            => 'bir-xil',
            'title'           => ['uz' => 'Ikkinchi'],
            'order'           => 2,
            'active'          => 1,
        ]);
    }
}
