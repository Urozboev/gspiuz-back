<?php

namespace App\Console\Commands;

use App\Models\DinamikMenu;
use App\Models\FormMenu;
use App\Models\Menu;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Fayllar sahifasini sinash uchun namunaviy yozuvlar.
 *
 * Saytda fayllar sana bo'yicha yillarga guruhlanadi — buni ko'rish uchun
 * turli yillardagi bir nechta fayl kerak. Bu buyruq shuni tayyorlaydi:
 * uchta yil, bittasi sanasiz, bittasi izoh va muqova rasmi bilan.
 *
 *   php artisan demo:page-files                  — study-plans sahifasiga
 *   php artisan demo:page-files citizen_appeal   — boshqa sahifaga
 *   php artisan demo:page-files --reset          — o'chiradi
 */
class DemoPageFiles extends Command
{
    protected $signature = 'demo:page-files
                            {slug=study-plans : Sahifa manzili}
                            {--reset : Namunaviy fayllarni oʻchiradi}';

    protected $description = 'Fayllar sahifasini sinash uchun namunaviy fayllar qoʻshadi';

    /** Namunaviy yozuvlar: [sarlavha, sana, izoh bormi]. */
    private const SAMPLES = [
        ['2026–2027 oʻquv yili rejasi',            '2026-08-15', true],
        ['Bakalavriat yoʻnalishlari jadvali',      '2026-03-02', false],
        ['Magistratura dasturlari roʻyxati',       '2026-01-20', false],
        ['2025–2026 oʻquv yili rejasi',            '2025-08-18', false],
        ['Kuzgi semestr dars jadvali',             '2025-09-05', true],
        ['Bahorgi semestr dars jadvali',           '2025-02-10', false],
        ['2024–2025 oʻquv yili rejasi',            '2024-08-20', false],
        ['Arxiv: eski oʻquv rejalari',             null,         false],
    ];

    public function handle(): int
    {
        $slug = (string) $this->argument('slug');
        $page = $this->pageFor($slug);

        if (!$page) {
            $this->error("Sahifa topilmadi: {$slug}");

            return self::FAILURE;
        }

        return $this->option('reset') ? $this->reset($page) : $this->apply($page);
    }

    private function pageFor(string $slug): ?DinamikMenu
    {
        $menuIds = Menu::where('slug', $slug)->orWhere('path', '/' . $slug)->pluck('id');

        if ($menuIds->isEmpty()) {
            return null;
        }

        return DinamikMenu::whereIn('menu_id', $menuIds)->orderBy('id')->first();
    }

    private function apply(DinamikMenu $page): int
    {
        if ($page->layout !== 'files') {
            $this->warn("Sahifa koʻrinishi '{$page->layout}' — 'files' ga oʻzgartirildi.");
            $page->update(['layout' => 'files']);
        }

        $directory = public_path('upload/files');
        File::ensureDirectoryExists($directory, 0755, true);

        $created = 0;

        foreach (self::SAMPLES as $index => [$title, $date, $withExtra]) {
            $slug = 'namuna-' . ($index + 1);

            if (FormMenu::where('dinamik_menu_id', $page->id)->where('slug', $slug)->exists()) {
                continue;
            }

            $fileName = 'namuna-' . Str::random(10) . '.pdf';
            file_put_contents($directory . '/' . $fileName, $this->pdf($title));

            FormMenu::create([
                'dinamik_menu_id' => $page->id,
                'slug'            => $slug,
                'title'           => $this->tr($title),
                'text'            => $withExtra ? $this->tr('Institut kengashi tomonidan tasdiqlangan hujjat.') : null,
                'image'           => $withExtra ? 'gspi-logo.png' : null,
                'file'            => $fileName,
                'date'            => $date,
                'order'           => $index + 1,
                'active'          => 1,
                'type'            => 'files',
            ]);

            $created++;
            $this->line('  ' . str_pad($date ?? 'sanasiz', 12) . $title);
        }

        $this->info("{$created} ta namunaviy fayl qoʻshildi.");
        $this->line('Oʻchirish uchun: php artisan demo:page-files ' . $this->argument('slug') . ' --reset');

        return self::SUCCESS;
    }

    private function reset(DinamikMenu $page): int
    {
        $items = FormMenu::where('dinamik_menu_id', $page->id)
            ->where('slug', 'like', 'namuna-%')
            ->get();

        foreach ($items as $item) {
            if ($item->file) {
                @unlink(public_path('upload/files/' . $item->file));
            }

            $item->forceDelete();
        }

        $this->info($items->count() . ' ta namunaviy fayl oʻchirildi.');

        return self::SUCCESS;
    }

    /** Eng sodda, ochiladigan PDF. */
    private function pdf(string $title): string
    {
        $text = str_replace(['(', ')'], '', $title);

        $content = "BT /F1 18 Tf 60 720 Td ({$text}) Tj ET";
        $length = strlen($content);

        return "%PDF-1.4\n"
            . "1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n"
            . "2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n"
            . "3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]"
            . "/Resources<</Font<</F1 4 0 R>>>>/Contents 5 0 R>>endobj\n"
            . "4 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\n"
            . "5 0 obj<</Length {$length}>>stream\n{$content}\nendstream endobj\n"
            . "trailer<</Root 1 0 R>>\n%%EOF\n";
    }

    private function tr(string $uz): array
    {
        return ['uz' => $uz, 'ru' => $uz, 'en' => $uz];
    }
}
