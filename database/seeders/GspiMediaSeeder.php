<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostImage;
use App\Models\Work;
use App\Models\WorkImage;
use Illuminate\Database\Seeder;

/**
 * Namunaviy rasmlar: yangilik kartochkalari va galereya albomlari.
 *
 * BARCHA RASMLAR O'RINBOSAR — GD bilan generatsiya qilinadigan gradient
 * fonlar, haqiqiy fotosurat emas. Albom nomlari ham o'ylab topilgan.
 * Haqiqiy fotosuratlar admin paneldan yuklanadi.
 *
 * Ishlab chiqarish serveriga chiqarishdan oldin: php artisan demo:audit
 *
 *   php artisan db:seed --class=GspiMediaSeeder
 */
class GspiMediaSeeder extends Seeder
{
    /** Admin paneldagi kabi uch o'lcham: asl, 600 va 200. */
    private const SIZES = [
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
        $this->seedPostImages();
        $this->seedAlbums();
    }

    private function ensureDirectories(): void
    {
        foreach (array_keys(self::SIZES) as $size) {
            $path = public_path('upload/images' . ($size ? '/' . $size : ''));

            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    /** Har bir yangilikka bittadan muqova rasmi. */
    private function seedPostImages(): void
    {
        foreach (Post::orderBy('id')->get() as $index => $post) {
            if (PostImage::where('post_id', $post->id)->exists()) {
                continue;
            }

            $title = is_array($post->title) ? ($post->title['uz'] ?? '') : (string) $post->title;
            $name = $this->makeImage('post-' . $post->id, $this->shorten($title), $index);

            PostImage::create(['post_id' => $post->id, 'img' => $name]);
        }
    }

    /** Ikkita namunaviy fotoalbom. */
    private function seedAlbums(): void
    {
        $albums = [
            [
                'slug'   => 'bitiruv-kechasi',
                'title'  => $this->tr('Bitiruv kechasi', 'Выпускной вечер', 'Graduation ceremony'),
                'desc'   => $this->tr(
                    "Bitiruvchilarga diplomlar topshirilgan tantanali marosim lavhalari.",
                    'Кадры торжественной церемонии вручения дипломов выпускникам.',
                    'Scenes from the ceremony where graduates received their diplomas.'
                ),
                'photos' => ['Bitiruv marosimi', 'Diplom topshirish', 'Bitiruvchilar', 'Tantanali qism'],
            ],
            [
                'slug'   => 'talabalar-hayoti',
                'title'  => $this->tr('Talabalar hayoti', 'Студенческая жизнь', 'Student life'),
                'desc'   => $this->tr(
                    "Institut kampusidagi kundalik hayot, to'garaklar va sport tadbirlari.",
                    'Повседневная жизнь кампуса, кружки и спортивные мероприятия.',
                    'Everyday campus life, clubs and sports events.'
                ),
                'photos' => ['Kutubxona', 'Sport zali', 'Ilmiy to\'garak', 'Kampus', 'Laboratoriya'],
            ],
        ];

        foreach ($albums as $index => $album) {
            $existing = Work::all()->first(function ($work) use ($album) {
                return data_get($work->title, 'uz') === $album['title']['uz'];
            });

            if ($existing) {
                continue;
            }

            $cover = $this->makeImage($album['slug'] . '-cover', $album['title']['uz'], $index);

            $work = Work::create([
                'title'    => $album['title'],
                'desc'     => $album['desc'],
                'main_img' => $cover,
            ]);

            foreach ($album['photos'] as $photoIndex => $caption) {
                $name = $this->makeImage(
                    $album['slug'] . '-' . ($photoIndex + 1),
                    $caption,
                    $index + $photoIndex
                );

                WorkImage::create(['work_id' => $work->id, 'img' => $name]);
            }
        }
    }

    /**
     * Uch o'lchamda o'rinbosar rasm yaratadi va fayl nomini qaytaradi.
     * Gradient fon + markazda qisqa yozuv.
     */
    private function makeImage(string $key, string $caption, int $paletteIndex): string
    {
        $name = $key . '.jpg';
        [$from, $to] = self::PALETTE[$paletteIndex % count(self::PALETTE)];

        foreach (self::SIZES as $size => [$width, $height]) {
            $canvas = imagecreatetruecolor($width, $height);

            // Vertikal gradient.
            for ($y = 0; $y < $height; $y++) {
                $ratio = $y / max(1, $height - 1);
                $color = imagecolorallocate(
                    $canvas,
                    (int) round($from[0] + ($to[0] - $from[0]) * $ratio),
                    (int) round($from[1] + ($to[1] - $from[1]) * $ratio),
                    (int) round($from[2] + ($to[2] - $from[2]) * $ratio)
                );
                imageline($canvas, 0, $y, $width, $y, $color);
            }

            // Yozuv faqat kattaroq o'lchamlarda o'qiladi.
            if ($width >= 600) {
                $white = imagecolorallocate($canvas, 255, 255, 255);
                $font = $width >= 1600 ? 5 : 4;
                $text = $this->ascii($caption);
                $textWidth = imagefontwidth($font) * strlen($text);
                $scale = $width >= 1600 ? 3 : 1;

                if ($scale > 1) {
                    // Katta o'lchamda yozuvni alohida chizib, kattalashtirib joylaymiz.
                    $layer = imagecreatetruecolor($textWidth + 2, imagefontheight($font) + 2);
                    $bg = imagecolorallocate($layer, $from[0], $from[1], $from[2]);
                    imagefill($layer, 0, 0, $bg);
                    imagestring($layer, $font, 1, 1, $text, imagecolorallocate($layer, 255, 255, 255));
                    imagecopyresized(
                        $canvas,
                        $layer,
                        (int) (($width - $textWidth * $scale) / 2),
                        (int) (($height - imagefontheight($font) * $scale) / 2),
                        0,
                        0,
                        $textWidth * $scale,
                        imagefontheight($font) * $scale,
                        imagesx($layer),
                        imagesy($layer)
                    );
                    imagedestroy($layer);
                } else {
                    imagestring(
                        $canvas,
                        $font,
                        (int) (($width - $textWidth) / 2),
                        (int) (($height - imagefontheight($font)) / 2),
                        $text,
                        $white
                    );
                }
            }

            $path = public_path('upload/images' . ($size ? '/' . $size : '') . '/' . $name);
            imagejpeg($canvas, $path, $size === '200' ? 60 : 80);
            imagedestroy($canvas);
        }

        return $name;
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

    private function tr(string $uz, ?string $ru = null, ?string $en = null): array
    {
        return ['uz' => $uz, 'ru' => $ru ?? $uz, 'en' => $en ?? $uz];
    }
}
