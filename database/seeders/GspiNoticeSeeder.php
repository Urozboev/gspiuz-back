<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Event;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\Rek;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Bosh sahifadagi uchta blok: hero bannerlari, video yangiliklar va
 * saytga kirilganda ochiladigan modal xabar.
 *
 * Bularsiz frontend zaxira rasm va bo'sh ro'yxat ko'rsatadi.
 * Haqiqiy kontent admin paneldan kiritiladi — bu o'rinbosar.
 *
 *   php artisan db:seed --class=GspiNoticeSeeder
 */
class GspiNoticeSeeder extends Seeder
{
    /** O'rinbosar rasmlar palitrasi (GspiMediaSeeder bilan bir xil ohang). */
    private const PALETTE = [
        [[6, 78, 59], [16, 136, 96]],
        [[12, 74, 110], [14, 116, 144]],
        [[76, 29, 149], [109, 40, 217]],
    ];

    public function run(): void
    {
        $this->seedBanners();
        $this->seedVideoNews();
        $this->seedPopup();
        $this->seedEvents();
    }

    /**
     * Kalendar uchun namunaviy tadbirlar.
     *
     * Biri o'tgan, biri yaqin kunlarda, biri ko'p kunlik — frontend
     * kalendarning uchala holatini ham ko'radi.
     */
    private function seedEvents(): void
    {
        $events = [
            [
                'title' => $this->tr(
                    'Ustoz-shogird anʼanasi: metodik seminar',
                    'Традиция «наставник — ученик»: методический семинар',
                    'Mentor and mentee: a teaching methods seminar'
                ),
                'desc' => $this->tr(
                    'Yosh oʻqituvchilar uchun tajriba almashish seminari.',
                    'Семинар по обмену опытом для молодых преподавателей.',
                    'An experience-sharing seminar for early-career lecturers.'
                ),
                'location' => $this->tr('Katta majlislar zali', 'Большой зал заседаний', 'Main assembly hall'),
                'date'     => now()->addDays(6)->toDateString(),
                'time'     => '14:00',
                'type'     => 'seminar',
            ],
            [
                'title' => $this->tr(
                    'Xalqaro ilmiy-amaliy anjuman',
                    'Международная научно-практическая конференция',
                    'International research conference'
                ),
                'desc' => $this->tr(
                    'Uch kunlik anjuman: maʼruzalar, seksiyalar va koʻrgazma.',
                    'Трёхдневная конференция: доклады, секции и выставка.',
                    'A three-day conference with talks, sections and an exhibition.'
                ),
                'location' => $this->tr('Institut bosh binosi', 'Главный корпус института', 'Main building'),
                'date'     => now()->addDays(24)->toDateString(),
                'end_date' => now()->addDays(26)->toDateString(),
                'time'     => '09:00',
                'type'     => 'konferensiya',
            ],
            [
                'title' => $this->tr(
                    'Bilimlar kuni tantanasi',
                    'Торжество в честь Дня знаний',
                    'Knowledge Day ceremony'
                ),
                'desc' => $this->tr(
                    'Yangi oʻquv yilining ochilish marosimi.',
                    'Церемония открытия нового учебного года.',
                    'The opening ceremony of the new academic year.'
                ),
                'location' => $this->tr('Institut maydoni', 'Площадь института', 'Institute square'),
                'date'     => now()->startOfYear()->addMonths(8)->startOfMonth()->toDateString(),
                'time'     => '10:00',
                'type'     => 'bayram',
            ],
        ];

        $created = 0;

        foreach ($events as $index => $event) {
            $slug = Str::slug($event['title']['uz']);

            if (Event::withTrashed()->where('slug', $slug)->exists()) {
                continue;
            }

            Event::create($event + [
                'slug'   => $slug,
                'img'    => $this->makeBanner('event-' . ($index + 1), $index),
                'active' => 1,
            ]);

            $created++;
        }

        $this->command?->info("Tadbirlar: {$created} ta.");
    }

    /** Bosh sahifa hero bannerlari. */
    private function seedBanners(): void
    {
        $banners = [
            [
                'title' => $this->tr(
                    'Guliston davlat pedagogika instituti',
                    'Гулистанский государственный педагогический институт',
                    'Gulistan State Pedagogical Institute'
                ),
                'desc' => $this->tr(
                    'Sirdaryo viloyatida pedagog kadrlar tayyorlaydigan yetakchi oliy taʼlim muassasasi.',
                    'Ведущий вуз Сырдарьинской области по подготовке педагогических кадров.',
                    'The leading teacher-training institution in the Sirdaryo region.'
                ),
            ],
            [
                'title' => $this->tr(
                    'Zamonaviy oʻquv muhiti',
                    'Современная учебная среда',
                    'A modern learning environment'
                ),
                'desc' => $this->tr(
                    'Laboratoriyalar, kutubxona fondi va raqamli taʼlim resurslari talabalar ixtiyorida.',
                    'Лаборатории, библиотечный фонд и цифровые образовательные ресурсы.',
                    'Laboratories, library collections and digital learning resources.'
                ),
            ],
            [
                'title' => $this->tr(
                    'Qabul 2026 boshlandi',
                    'Приём 2026 открыт',
                    'Admissions 2026 are open'
                ),
                'desc' => $this->tr(
                    'Bakalavriat va magistratura yoʻnalishlariga hujjat qabuli davom etmoqda.',
                    'Продолжается приём документов на бакалавриат и магистратуру.',
                    'Applications for bachelor\'s and master\'s programmes are being accepted.'
                ),
            ],
        ];

        $created = 0;

        foreach ($banners as $index => $banner) {
            $exists = Brand::all()->first(
                fn ($row) => data_get($row->title, 'uz') === $banner['title']['uz']
            );

            if ($exists) {
                continue;
            }

            Brand::create([
                'title' => $banner['title'],
                'desc'  => $banner['desc'],
                'img'   => $this->makeBanner('banner-' . ($index + 1), $index),
            ]);

            $created++;
        }

        $this->command?->info("Bannerlar: {$created} ta.");
    }

    /** Video yangiliklar — YouTube havolasi bilan. */
    private function seedVideoNews(): void
    {
        $videos = [
            [
                'title' => $this->tr(
                    'Institut bilan tanishuv',
                    'Знакомство с институтом',
                    'Meet the institute'
                ),
                'desc' => $this->tr(
                    'Kampus, oʻquv binolari va talabalar hayoti haqida qisqa video.',
                    'Короткое видео о кампусе, учебных корпусах и студенческой жизни.',
                    'A short film about the campus, buildings and student life.'
                ),
                'video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ],
            [
                'title' => $this->tr(
                    'Bitiruv marosimi lavhalari',
                    'Кадры выпускной церемонии',
                    'Graduation ceremony highlights'
                ),
                'desc' => $this->tr(
                    'Diplomlar topshirilgan tantanali marosimdan videoreportaj.',
                    'Видеорепортаж с торжественной церемонии вручения дипломов.',
                    'A report from the diploma award ceremony.'
                ),
                'video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ],
        ];

        $created = 0;

        foreach ($videos as $index => $video) {
            $slug = Str::slug($video['title']['uz']);

            if (Post::where('slug', $slug)->exists()) {
                continue;
            }

            $post = Post::create([
                'title'      => $video['title'],
                'desc'       => $video['desc'],
                'slug'       => $slug,
                'video_link' => $video['video'],
                'date'       => now()->subDays(($index + 1) * 12)->toDateString(),
            ]);

            PostImage::create([
                'post_id' => $post->id,
                'img'     => $this->makeBanner('video-' . ($index + 1), $index + 1),
            ]);

            $created++;
        }

        $this->command?->info("Video yangiliklar: {$created} ta.");
    }

    /** Namunaviy modal xabar — muddati bilan. */
    private function seedPopup(): void
    {
        $title = $this->tr(
            'Bilimlar kuni muborak!',
            'С Днём знаний!',
            'Happy Knowledge Day!'
        );

        $exists = Rek::all()->first(fn ($row) => data_get($row->title, 'uz') === $title['uz']);

        if ($exists) {
            $this->command?->info('Modal xabar: allaqachon mavjud.');

            return;
        }

        Rek::create([
            'title' => $title,
            'desc'  => $this->tr(
                '<p>Barcha talaba va oʻqituvchilarni yangi oʻquv yili bilan tabriklaymiz!</p>',
                '<p>Поздравляем студентов и преподавателей с новым учебным годом!</p>',
                '<p>Congratulations to all students and staff on the new academic year!</p>'
            ),
            // Bayram kuni o'zi paydo bo'lib, keyin o'zi yo'qoladi.
            'starts_at' => now()->startOfYear()->addMonths(8)->startOfMonth()->toDateString(),
            'ends_at'   => now()->startOfYear()->addMonths(8)->startOfMonth()->addDays(4)->toDateString(),
            'active'    => 1,
            'order'     => 1,
            'action'    => 0,
            'logo'      => $this->makeBanner('popup-bilimlar-kuni', 2),
        ]);

        $this->command?->info('Modal xabar: 1 ta.');
    }

    /**
     * Gradient o'rinbosar rasm, uch o'lchamda.
     * Fayl nomini qaytaradi.
     */
    private function makeBanner(string $key, int $paletteIndex): string
    {
        $name = $key . '.jpg';

        if (!extension_loaded('gd')) {
            return $name;
        }

        [$from, $to] = self::PALETTE[$paletteIndex % count(self::PALETTE)];

        foreach (['' => [1600, 900], '600' => [600, 338], '200' => [200, 113]] as $size => [$width, $height]) {
            $directory = public_path('upload/images' . ($size ? '/' . $size : ''));

            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $canvas = imagecreatetruecolor($width, $height);

            for ($y = 0; $y < $height; $y++) {
                $ratio = $y / max(1, $height - 1);
                imageline($canvas, 0, $y, $width, $y, imagecolorallocate(
                    $canvas,
                    (int) round($from[0] + ($to[0] - $from[0]) * $ratio),
                    (int) round($from[1] + ($to[1] - $from[1]) * $ratio),
                    (int) round($from[2] + ($to[2] - $from[2]) * $ratio)
                ));
            }

            imagejpeg($canvas, $directory . '/' . $name, $size === '200' ? 60 : 80);
            imagedestroy($canvas);
        }

        return $name;
    }

    private function tr(string $uz, string $ru, string $en): array
    {
        return ['uz' => $uz, 'ru' => $ru, 'en' => $en];
    }
}
