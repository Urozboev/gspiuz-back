<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Tests\TestCase;

/**
 * Band manzillar haqida ogohlantirish.
 *
 * Saytdagi ba'zi sahifalar kod bilan yozilgan (/news, /gallery). Admin
 * yangi menyu bandiga shunday manzil bersa va unga sahifa biriktirsa,
 * kod sahifasi ustun chiqadi — kiritilgan kontent hech qachon ko'rinmaydi.
 * Buni forma ogohlantirishi kerak.
 */
class ReservedPathsTest extends TestCase
{
    private function admin(): User
    {
        return User::where('role', 'admin')->orderBy('id')->firstOrFail();
    }

    public function test_reserved_paths_are_configured(): void
    {
        $paths = config('reserved_paths');

        $this->assertIsArray($paths);
        $this->assertGreaterThan(20, count($paths), 'Roʻyxat toʻldirilmagan');

        // Manzillar `/` siz saqlanadi — solishtirish shunga tayanadi.
        foreach ($paths as $path) {
            $this->assertStringStartsNotWith('/', $path, "Manzil `/` bilan boshlanmasligi kerak: {$path}");
        }

        // Saytdagi eng ko'p ishlatiladigan sahifalar ro'yxatda bo'lishi shart.
        foreach (['news', 'gallery', 'admissions', 'events'] as $expected) {
            $this->assertContains($expected, $paths);
        }
    }

    /**
     * Ogohlantirish ikkala formada ham boʻlishi kerak.
     *
     * Manzil roʻyxatga bogʻliq boʻlgani uchun data provider ishlatilmaydi —
     * u ilova ishga tushishidan oldin bajariladi va bazaga murojaat qila
     * olmaydi.
     */
    public function test_menu_forms_show_the_reserved_list(): void
    {
        $menuId = Menu::query()->orderBy('id')->value('id');

        $this->assertNotNull($menuId, 'Menyu bandi topilmadi');

        foreach (['/admin/menus/create', '/admin/menus/' . $menuId . '/edit'] as $url) {
            $html = $this->actingAs($this->admin())->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('Band manzillar roʻyxati', $html, $url);
            $this->assertStringContainsString('path-reserved-warning', $html, $url);
            $this->assertStringContainsString('/news', $html, $url);
        }
    }

    public function test_page_form_marks_menus_that_already_have_a_site_page(): void
    {
        // Kod sahifasiga ishora qiluvchi band bo'lishi kerak.
        $reserved = Menu::whereIn('path', ['/news', '/gallery', '/events'])->first();

        $this->assertNotNull($reserved, 'Sinov uchun band manzilli menyu topilmadi');

        $html = $this->actingAs($this->admin())
            ->get('/admin/dynamic-menus/create')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('saytda oʻz sahifasi bor', $html);
    }

    /**
     * Menyu manzili sahifa qaysi manzilda ochilishini belgilaydi.
     * Ilgari slug sarlavhadan yasalardi va sayt sahifani topa olmasdi.
     */
    public function test_menu_slug_follows_the_path(): void
    {
        $title = 'Manzil sinovi ' . uniqid();
        $path = '/sinov_manzil_' . uniqid();

        $this->actingAs($this->admin())->post('/admin/menus', [
            'title' => ['uz' => $title, 'ru' => $title, 'en' => $title],
            'path'  => $path,
            'order' => 900,
        ])->assertStatus(302);

        $menu = Menu::where('title->uz', $title)->first();

        $this->assertNotNull($menu, 'Menyu bandi yaratilmadi');

        $this->assertSame(
            ltrim($path, '/'),
            $menu->slug,
            'Slug manzildan olinishi kerak, sarlavhadan emas'
        );

        $menu->forceDelete();
    }

    public function test_menu_without_a_path_still_gets_a_slug(): void
    {
        // Dropdown sarlavhasining o'z manzili yo'q — slug sarlavhadan.
        $title = 'Manzilsiz band ' . uniqid();

        $this->actingAs($this->admin())->post('/admin/menus', [
            'title' => ['uz' => $title, 'ru' => $title, 'en' => $title],
            'order' => 901,
        ])->assertStatus(302);

        $menu = Menu::where('title->uz', $title)->first();

        $this->assertNotNull($menu);
        $this->assertNotEmpty($menu->slug);

        $menu->forceDelete();
    }
}
