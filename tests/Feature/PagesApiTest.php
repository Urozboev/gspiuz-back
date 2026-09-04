<?php

namespace Tests\Feature;

use App\Models\DinamikMenu;
use App\Models\FormMenu;
use App\Models\Menu;
use Tests\TestCase;

/**
 * Dinamik sahifalar API'si: GET /pages, /pages/{slug}, /pages/{slug}/{item}.
 */
class PagesApiTest extends TestCase
{
    private function api(string $path, string $locale = 'uz')
    {
        $prefix = trim((string) config('api.prefix'), '/');

        return $this->withHeaders(['Accept-Language' => $locale])->getJson('/' . $prefix . $path);
    }

    public function test_page_list_returns_slug_and_layout(): void
    {
        $response = $this->api('/pages');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['slug', 'path', 'layout', 'title']]]);

        $layouts = collect($response->json('data'))->pluck('layout')->unique();

        foreach ($layouts as $layout) {
            $this->assertContains($layout, DinamikMenu::LAYOUTS);
        }
    }

    public function test_single_page_returns_body_and_blocks(): void
    {
        $response = $this->api('/pages/admissions');

        $response->assertOk()
            ->assertJsonPath('data.slug', 'admissions')
            ->assertJsonPath('data.layout', 'single')
            ->assertJsonStructure([
                'data' => ['slug', 'layout', 'title', 'subtitle', 'body', 'blocks', 'files'],
            ]);

        $blocks = $response->json('data.blocks');

        $this->assertNotEmpty($blocks, 'Qabul sahifasida bloklar bo\'lishi kerak');

        // Tab kaliti — frontend beshta bo'limga ajratadi.
        $groups = collect($blocks)->pluck('group')->unique()->filter()->values();

        $this->assertEqualsCanonicalizing(
            ['commission', 'bachelor', 'master', 'second', 'foreign'],
            $groups->all()
        );

        // Har bir blokning manzili sahifa ichida unikal bo'lishi shart.
        $slugs = collect($blocks)->pluck('slug');

        $this->assertSame($slugs->count(), $slugs->unique()->count(), 'Blok manzillari takrorlanmasligi kerak');
    }

    /** @dataProvider locales */
    public function test_text_follows_accept_language(string $locale, string $expected): void
    {
        $this->api('/pages/admissions', $locale)
            ->assertOk()
            ->assertJsonPath('data.title', $expected);
    }

    public static function locales(): array
    {
        return [
            'uz' => ['uz', 'Qabul'],
            'ru' => ['ru', 'Приём'],
            'en' => ['en', 'Admission'],
        ];
    }

    public function test_missing_page_returns_404(): void
    {
        $this->api('/pages/bunday-sahifa-yoq')->assertNotFound();
    }

    public function test_card_page_item_opens_on_its_own_address(): void
    {
        // Kartochkali sahifa va undagi bitta yozuv.
        $menu = Menu::create([
            'title'  => ['uz' => 'Sinov sahifasi', 'ru' => 'Sinov', 'en' => 'Test'],
            'slug'   => 'sinov-kartochkalar',
            'path'   => '/sinov-kartochkalar',
            'order'  => 900,
            'active' => 0,
        ]);

        $page = DinamikMenu::create([
            'menu_id' => $menu->id,
            'title'   => ['uz' => 'Sinov sahifasi'],
            'layout'  => 'cards',
            'active'  => 1,
        ]);

        $block = FormMenu::create([
            'dinamik_menu_id' => $page->id,
            'slug'            => 'birinchi-yozuv',
            'title'           => ['uz' => 'Birinchi yozuv'],
            'text'            => ['uz' => 'Qisqacha matn'],
            'body'            => ['uz' => '<p>Toʻliq matn</p>'],
            'order'           => 1,
            'active'          => 1,
        ]);

        $this->api('/pages/sinov-kartochkalar')
            ->assertOk()
            ->assertJsonPath('data.layout', 'cards')
            ->assertJsonPath('data.blocks.0.slug', 'birinchi-yozuv');

        $this->api('/pages/sinov-kartochkalar/birinchi-yozuv')
            ->assertOk()
            ->assertJsonPath('data.title', 'Birinchi yozuv')
            ->assertJsonPath('data.body', '<p>Toʻliq matn</p>');

        $this->api('/pages/sinov-kartochkalar/yoq-bunday')->assertNotFound();

        $block->forceDelete();
        $page->forceDelete();
        $menu->forceDelete();
    }

    public function test_hidden_pages_are_not_in_the_site_menu(): void
    {
        $prefix = trim((string) config('api.prefix'), '/');

        $response = $this->withHeaders(['Accept-Language' => 'uz'])->getJson('/' . $prefix . '/menu');

        $response->assertOk();

        $hidden = Menu::where('active', 0)->pluck('slug');
        $shown = collect($response->json())
            ->flatMap(fn ($item) => array_merge(
                [$item['slug'] ?? null],
                collect($item['children'] ?? [])->pluck('slug')->all()
            ))
            ->filter()
            ->values();

        foreach ($hidden as $slug) {
            $this->assertNotContains($slug, $shown->all(), "Yashirin sahifa menyuda: {$slug}");
        }
    }
}
