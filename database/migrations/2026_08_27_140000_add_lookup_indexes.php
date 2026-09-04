<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Qidiruv ustunlariga indeks.
 *
 * Bu ustunlar bo'yicha har kuni qidiriladi (sahifa manzili, yangilik
 * manzili, faollik bayrog'i), ammo indeks yo'q edi — ya'ni har bir
 * so'rov butun jadvalni o'qirdi. Hozir jadvallar kichik va bu
 * sezilmaydi, lekin sayt to'lgan sari sekinlashadi: yangiliklar
 * to'planadi, fayllar har kuni qo'shiladi.
 *
 * `posts.video_link` (text) indekslanmadi — u faqat "bo'shmi" deb
 * tekshiriladi, indeksdan foyda yo'q.
 */
return new class extends Migration
{
    /** Jadval => [indeks nomi => ustunlar]. */
    private const INDEXES = [
        'menus' => [
            'menus_slug_index'      => ['slug'],
            'menus_path_index'      => ['path'],
            'menus_active_index'    => ['active'],
        ],
        'dinamik_menus' => [
            'dinamik_menus_menu_active_index' => ['menu_id', 'active'],
        ],
        'form_menus' => [
            'form_menus_page_active_index' => ['dinamik_menu_id', 'active'],
            'form_menus_date_index'        => ['date'],
        ],
        'posts' => [
            'posts_slug_index' => ['slug'],
            'posts_date_index' => ['date'],
        ],
        'employs' => [
            'employs_birthday_index' => ['birthday'],
        ],
        'employ_metas' => [
            'employ_metas_active_index' => ['active'],
            'employ_metas_slug_index'   => ['slug'],
        ],
        'departments' => [
            'departments_slug_index' => ['slug'],
        ],
        'events' => [
            'events_active_date_index' => ['active', 'date'],
        ],
        'reks' => [
            'reks_visible_index' => ['active', 'starts_at', 'ends_at'],
        ],
        'services' => [
            'services_slug_index' => ['slug'],
        ],
        'posts_categories' => [
            'posts_categories_slug_index' => ['slug'],
        ],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $indexes) {
                foreach ($indexes as $name => $columns) {
                    // Ustun yo'q bo'lsa o'tkazib yuboramiz.
                    foreach ($columns as $column) {
                        if (!Schema::hasColumn($table, $column)) {
                            return;
                        }
                    }

                    $blueprint->index($columns, $name);
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($indexes) {
                foreach (array_keys($indexes) as $name) {
                    $blueprint->dropIndex($name);
                }
            });
        }
    }
};
