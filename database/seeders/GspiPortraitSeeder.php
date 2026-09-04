<?php

namespace Database\Seeders;

use App\Models\Employ;
use App\Models\Service;
use App\Models\Vacancy;
use Illuminate\Database\Seeder;

/**
 * Xodim portretlari va vakansiya / jurnal muqovalari.
 *
 * GspiMediaSeeder yangilik va albom rasmlarini yopgan edi; bu yerda xodim
 * kartochkalaridagi bo'sh rasm o'rinlari (4:5 portret) hamda /vacancies va
 * /journals muqovalari (16:9) to'ldiriladi.
 *
 * Haqiqiy fotosuratlar admin paneldan yuklanadi — bular o'rinbosar.
 *
 *   php artisan db:seed --class=GspiPortraitSeeder
 */
class GspiPortraitSeeder extends Seeder
{
    /** Portret: 4:5, kartochkalar uchun. Admin paneldagi kabi uch o'lcham. */
    private const PORTRAIT_SIZES = [
        ''    => [800, 1000],
        '600' => [600, 750],
        '200' => [200, 250],
    ];

    /** Muqova: 16:9, GspiMediaSeeder bilan bir xil. */
    private const COVER_SIZES = [
        ''    => [1600, 900],
        '600' => [600, 338],
        '200' => [200, 113],
    ];

    /** O'rinbosar rasmlar palitrasi (institut yashil ohangida). */
    private const PALETTE = [
        [[6, 78, 59], [16, 136, 96]],
        [[12, 74, 110], [14, 116, 144]],
        [[63, 63, 70], [113, 113, 122]],
        [[76, 29, 149], [109, 40, 217]],
        [[124, 45, 18], [180, 83, 9]],
    ];

    public function run(): void
    {
        if (!extension_loaded('gd')) {
            $this->command?->warn('GD kengaytmasi yo\'q — rasmlar generatsiya qilinmadi.');

            return;
        }

        $this->ensureDirectories();
        $this->seedEmployPhotos();
        $this->seedVacancyCovers();
        $this->seedJournalCovers();
    }

    private function ensureDirectories(): void
    {
        foreach (array_keys(self::PORTRAIT_SIZES) as $size) {
            $path = public_path('upload/images' . ($size ? '/' . $size : ''));

            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    /** Har bir xodimga initsiallari tushirilgan portret. */
    private function seedEmployPhotos(): void
    {
        $created = 0;

        foreach (Employ::orderBy('id')->get() as $index => $employ) {
            if ($employ->photo) {
                continue;
            }

            $name = $this->makeImage(
                'employ-' . $employ->id,
                $this->initials($employ),
                $index,
                self::PORTRAIT_SIZES
            );

            $employ->forceFill(['photo' => $name])->save();
            $created++;
        }

        $this->command?->info("Xodim portretlari: {$created} ta.");
    }

    /** Vakansiya muqovalari. */
    private function seedVacancyCovers(): void
    {
        $created = 0;

        foreach (Vacancy::orderBy('id')->get() as $index => $vacancy) {
            if ($vacancy->img) {
                continue;
            }

            $name = $this->makeImage(
                'vacancy-' . $vacancy->id,
                $this->shorten($this->text($vacancy->title)),
                $index + 1,
                self::COVER_SIZES
            );

            $vacancy->forceFill(['img' => $name])->save();
            $created++;
        }

        $this->command?->info("Vakansiya muqovalari: {$created} ta.");
    }

    /** Jurnallar — Service modeli orqali (/api/journals). */
    private function seedJournalCovers(): void
    {
        $created = 0;

        foreach (Service::orderBy('id')->get() as $index => $journal) {
            if ($journal->img) {
                continue;
            }

            $name = $this->makeImage(
                'journal-' . $journal->id,
                $this->shorten($this->text($journal->title)),
                $index + 3,
                self::COVER_SIZES
            );

            $journal->forceFill(['img' => $name])->save();
            $created++;
        }

        $this->command?->info("Jurnal muqovalari: {$created} ta.");
    }

    /**
     * Berilgan o'lchamlarda o'rinbosar rasm yaratadi va fayl nomini qaytaradi.
     * Gradient fon + markazda yozuv.
     */
    private function makeImage(string $key, string $caption, int $paletteIndex, array $sizes): string
    {
        $name = $key . '.jpg';
        [$from, $to] = self::PALETTE[$paletteIndex % count(self::PALETTE)];

        foreach ($sizes as $size => [$width, $height]) {
            $canvas = imagecreatetruecolor($width, $height);

            // Vertikal gradient.
            for ($y = 0; $y < $height; $y++) {
                $ratio = $y / max(1, $height - 1);
                imageline($canvas, 0, $y, $width, $y, imagecolorallocate(
                    $canvas,
                    (int) round($from[0] + ($to[0] - $from[0]) * $ratio),
                    (int) round($from[1] + ($to[1] - $from[1]) * $ratio),
                    (int) round($from[2] + ($to[2] - $from[2]) * $ratio)
                ));
            }

            // Yozuv faqat kattaroq o'lchamlarda o'qiladi.
            if ($width >= 400) {
                $this->drawCaption($canvas, $width, $height, $this->ascii($caption));
            }

            $path = public_path('upload/images' . ($size ? '/' . $size : '') . '/' . $name);
            imagejpeg($canvas, $path, $size === '200' ? 60 : 80);
            imagedestroy($canvas);
        }

        return $name;
    }

    /**
     * Yozuvni markazga chizadi. GD ning o'rnatilgan shrifti kichik, shuning
     * uchun matnni alohida qatlamga chizib, piksellarini kattalashtirib
     * ko'chiramiz — imagecopyresized ishlatilsa, qatlamning to'rtburchak foni
     * gradient ustida dog' bo'lib qolardi.
     */
    private function drawCaption($canvas, int $width, int $height, string $text): void
    {
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $white = imagecolorallocate($canvas, 255, 255, 255);

        // Yozuv rasm kengligining ~55% ini egallaydi.
        $scale = max(1, (int) floor(($width * 0.55) / max(1, $textWidth)));

        if ($scale === 1) {
            imagestring(
                $canvas,
                $font,
                (int) (($width - $textWidth) / 2),
                (int) (($height - $textHeight) / 2),
                $text,
                $white
            );

            return;
        }

        $layer = imagecreatetruecolor($textWidth, $textHeight);
        imagefill($layer, 0, 0, imagecolorallocate($layer, 0, 0, 0));
        imagestring($layer, $font, 0, 0, $text, imagecolorallocate($layer, 255, 255, 255));

        $offsetX = (int) (($width - $textWidth * $scale) / 2);
        $offsetY = (int) (($height - $textHeight * $scale) / 2);

        // Faqat harf piksellari ko'chiriladi, fon gradient bo'lib qoladi.
        for ($y = 0; $y < $textHeight; $y++) {
            for ($x = 0; $x < $textWidth; $x++) {
                if ((imagecolorat($layer, $x, $y) & 0xFF) < 128) {
                    continue;
                }

                imagefilledrectangle(
                    $canvas,
                    $offsetX + $x * $scale,
                    $offsetY + $y * $scale,
                    $offsetX + ($x + 1) * $scale - 1,
                    $offsetY + ($y + 1) * $scale - 1,
                    $white
                );
            }
        }

        imagedestroy($layer);
    }

    /** Familiya va ismning bosh harflari, masalan "SF". */
    private function initials(Employ $employ): string
    {
        $letters = '';

        // Ism va familiya ko'p tilli maydon bo'lishi mumkin.
        foreach ([$employ->last_name, $employ->first_name] as $part) {
            $clean = $this->ascii($this->text($part));

            if ($clean !== 'GSPI') {
                $letters .= strtoupper(substr($clean, 0, 1));
            }
        }

        return $letters !== '' ? $letters : 'GSPI';
    }

    /** Ko'p tilli maydondan uz matnini oladi. */
    private function text($value): string
    {
        if (is_array($value)) {
            return (string) ($value['uz'] ?? reset($value) ?: '');
        }

        return (string) $value;
    }

    /** GD ning o'rnatilgan shriftlari faqat ASCII ni chizadi. */
    private function ascii(string $value): string
    {
        $map = ['ʻ' => "'", '’' => "'", 'ў' => 'o', 'қ' => 'q', 'ғ' => 'g', 'ҳ' => 'h'];
        $value = strtr($value, $map);
        $value = preg_replace('/[^\x20-\x7E]/', '', $value) ?? '';

        return trim($value) !== '' ? trim($value) : 'GSPI';
    }

    /** Uzun sarlavhani rasm uchun qisqartiradi. */
    private function shorten(string $title): string
    {
        $words = preg_split('/\s+/', trim($title)) ?: [];

        return implode(' ', array_slice($words, 0, 4));
    }
}
