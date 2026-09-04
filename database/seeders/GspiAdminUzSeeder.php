<?php

namespace Database\Seeders;

use App\Models\Config;
use App\Models\ConfigGroup;
use Illuminate\Database\Seeder;

/**
 * Admin paneldagi bazadan keladigan nomlarni o'zbekchalashtiradi.
 *
 * Blade fayllaridagi matnlar kodda o'girilgan, ammo yon menyu bandlari va
 * ularning guruhlari `config` / `config_groups` jadvalidan o'qiladi —
 * shu sabab ular alohida yangilanadi.
 *
 * Nomlar model nomi bilan emas, saytdagi sahifa nomi bilan ataladi:
 * "Works" emas, "Fotogalereya"; "Services" emas, "Ilmiy jurnallar".
 *
 *   php artisan db:seed --class=GspiAdminUzSeeder
 */
class GspiAdminUzSeeder extends Seeder
{
    /** Yon menyu guruhlari. Layout `title` bo'yicha qidiradi. */
    private const GROUPS = [
        'Продукты'  => 'Mahsulotlar',
        'Посты'     => 'Yangiliklar',
        'Документы' => 'Hujjatlar',
        'Компания'  => 'Tashkilot',
    ];

    /** Yon menyu bandlari — marshrut kaliti bo'yicha. */
    private const ITEMS = [
        'applications'        => 'Arizalar',
        'products'            => 'Mahsulotlar',
        'products_categories' => 'Mahsulot turkumlari',
        'brands'              => 'Brendlar',
        'posts'               => 'Yangiliklar',
        'posts_categories'    => 'Yangilik turkumlari',
        'documents'           => 'Hujjatlar',
        'document_categories' => 'Hujjat turkumlari',
        'feedbacks'           => 'Fikrlar',
        'members'             => 'Jamoa',
        'partners'            => 'Hamkorlar',
        'works'               => 'Fotogalereya',
        'vacancies'           => 'Bo\'sh ish o\'rinlari',
        'questions'           => 'Ko\'p beriladigan savollar',
        'services'            => 'Ilmiy jurnallar',
        'certificates'        => 'Sertifikatlar',
    ];

    public function run(): void
    {
        $groups = 0;

        foreach (ConfigGroup::all() as $group) {
            $title = self::GROUPS[$group->title] ?? null;

            if ($title !== null && $group->title !== $title) {
                $group->update(['title' => $title]);
                $groups++;
            }
        }

        $items = 0;

        foreach (Config::all() as $item) {
            $title = self::ITEMS[$item->route] ?? null;

            if ($title !== null && $item->title !== $title) {
                $item->update(['title' => $title]);
                $items++;
            }
        }

        $this->command?->info("Yon menyu: {$groups} guruh, {$items} band o'zbekchalashtirildi.");
    }
}
