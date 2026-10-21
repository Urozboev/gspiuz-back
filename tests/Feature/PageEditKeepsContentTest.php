<?php

namespace Tests\Feature;

use App\Models\DinamikMenu;
use App\Models\FormMenu;
use App\Models\Menu;
use App\Models\User;
use Tests\TestCase;

/**
 * Sahifani admin paneldan saqlash uning kontentini oʻchirmasligi kerak.
 *
 * Xato: `DynamicMenuController::update()` oxirida formada kelmagan
 * HAMMA elementlarni oʻchirardi. Forma esa faqat `formmenu`,
 * `formmenu1`, `formmenu3` turidagilarni yuklaydi — kartochkalar,
 * fayllar va bosh sahifa, "Institut haqida", qabul sahifasi bloklari
 * (`group` boʻyicha saqlanadi) unga kirmaydi. Natijada sahifa har
 * saqlanganda uning kontenti saytdan yoʻqolardi.
 *
 * Bundan tashqari forma `dropzone_images` ni yubormagani uchun sahifa
 * foni har saqlashda `null` ga aylanardi.
 */
class PageEditKeepsContentTest extends TestCase
{
    private ?DinamikMenu $page = null;

    private ?Menu $menu = null;

    protected function tearDown(): void
    {
        if ($this->page) {
            FormMenu::withTrashed()->where('dinamik_menu_id', $this->page->id)->forceDelete();
            $this->page->forceDelete();
        }

        $this->menu?->forceDelete();

        parent::tearDown();
    }

    private function admin(): User
    {
        return User::where('role', 'admin')->orderBy('id')->firstOrFail();
    }

    private function makePage(): DinamikMenu
    {
        $this->menu = Menu::create([
            'title' => ['uz' => 'Sinov sahifasi', 'ru' => 'Sinov', 'en' => 'Test'],
            'path' => '/sinov-sahifa-' . uniqid(),
            'slug' => 'sinov-sahifa-' . uniqid(),
            'active' => 1,
            'order' => 99,
        ]);

        return $this->page = DinamikMenu::create([
            'menu_id' => $this->menu->id,
            'layout' => 'single',
            'title' => ['uz' => 'Sinov', 'ru' => 'Sinov', 'en' => 'Test'],
            'text' => ['uz' => '<p>Eski matn</p>', 'ru' => '<p>Eski</p>', 'en' => '<p>Old</p>'],
            'background' => 'fon-rasmi.webp',
            'active' => 1,
        ]);
    }

    /** Admin formasi yuboradigan maydonlar — xuddi brauzerdagidek. */
    private function formPayload(): array
    {
        return [
            'menu_id' => $this->menu->id,
            'layout' => 'single',
            'title' => ['uz' => 'Yangi nom', 'ru' => 'Yangi', 'en' => 'New'],
            'short_title' => ['uz' => '', 'ru' => '', 'en' => ''],
            'text' => ['uz' => '<p>Yangi matn</p>', 'ru' => '<p>Yangi</p>', 'en' => '<p>New</p>'],
        ];
    }

    public function test_saving_a_page_keeps_its_cards_files_and_blocks(): void
    {
        $page = $this->makePage();

        // Uch xil element: oddiy kartochka, guruhli blok va fayl.
        $card = FormMenu::create(['dinamik_menu_id' => $page->id, 'title' => ['uz' => 'Kartochka'], 'order' => 1]);
        $block = FormMenu::create(['dinamik_menu_id' => $page->id, 'group' => 'mission', 'title' => ['uz' => 'Missiya'], 'order' => 1]);
        $file = FormMenu::create(['dinamik_menu_id' => $page->id, 'type' => 'files', 'title' => ['uz' => 'Hujjat'], 'order' => 1]);

        $this->actingAs($this->admin())
            ->put(route('dynamic-menus.update', $page->id), $this->formPayload())
            ->assertRedirect();

        foreach ([$card, $block, $file] as $item) {
            $this->assertNotSoftDeleted('form_menus', ['id' => $item->id]);
        }

        // Matn esa haqiqatan yangilangan boʻlishi kerak.
        $this->assertSame('<p>Yangi matn</p>', $page->fresh()->text['uz']);
    }

    public function test_saving_a_page_keeps_its_background_image(): void
    {
        $page = $this->makePage();

        $this->actingAs($this->admin())
            ->put(route('dynamic-menus.update', $page->id), $this->formPayload())
            ->assertRedirect();

        $this->assertSame('fon-rasmi.webp', $page->fresh()->background);
    }

    /** Forma oʻzi boshqaradigan elementlarni olib tashlash hamon ishlashi kerak. */
    public function test_removing_a_form_section_still_deletes_it(): void
    {
        $page = $this->makePage();

        $kept = FormMenu::create(['dinamik_menu_id' => $page->id, 'type' => 'formmenu', 'title' => ['uz' => 'Qoladi'], 'order' => 1]);
        $removed = FormMenu::create(['dinamik_menu_id' => $page->id, 'type' => 'formmenu', 'title' => ['uz' => 'Ketadi'], 'order' => 2]);

        $payload = $this->formPayload();
        $payload['formmenu'] = [[
            'id' => $kept->id,
            'title' => ['uz' => 'Qoladi'],
            'type' => 'formmenu',
            'order' => 1,
            'dinamik_menu_id' => $page->id,
        ]];

        $this->actingAs($this->admin())
            ->put(route('dynamic-menus.update', $page->id), $payload)
            ->assertRedirect();

        $this->assertNotSoftDeleted('form_menus', ['id' => $kept->id]);
        $this->assertSoftDeleted('form_menus', ['id' => $removed->id]);
    }
}
