<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Lang;
use App\Models\StructureType;
use App\Models\SiteInfo;
use App\Models\Department;
use App\Models\Post;
use App\Models\EducationalProgram;

/**
 * GSPI boshlang'ich ma'lumotlari.
 *
 * Bu yerdagi rekvizitlar, ijtimoiy tarmoqlar va kafedralar ro'yxati
 * institutning amaldagi rasmiy sayti (gspi.uz) dan olingan.
 *
 * Ishga tushirish:  php artisan db:seed --class=GspiSeeder
 */
class GspiSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLangs();
        $this->seedStructureTypes();
        $this->seedSiteInfo();
        $this->seedFaculties();
        $this->seedChairs();
        $this->seedPrograms();
        $this->seedPosts();
    }

    /** Sayt tillari. */
    private function seedLangs(): void
    {
        $langs = [
            ['title' => "O'zbekcha", 'code' => 'uz', 'is_main' => true],
            ['title' => 'Русский',   'code' => 'ru', 'is_main' => false],
            ['title' => 'English',   'code' => 'en', 'is_main' => false],
        ];

        foreach ($langs as $lang) {
            Lang::updateOrCreate(['code' => $lang['code']], $lang);
        }
    }

    /**
     * Tuzilma turlari.
     * ID 3 — fakultet, ID 4 — kafedra (LeadershipController shu ID'larga tayanadi).
     */
    private function seedStructureTypes(): void
    {
        $types = [
            1 => 'Rahbariyat',
            2 => "Boshqarma va bo'limlar",
            3 => 'Fakultetlar',
            4 => 'Kafedralar',
            5 => 'Markazlar',
        ];

        foreach ($types as $id => $name) {
            StructureType::updateOrCreate(
                ['id' => $id],
                ['name' => $this->tr($name), 'order' => $id, 'active' => 1]
            );
        }
    }

    /** Institut rekvizitlari. */
    private function seedSiteInfo(): void
    {
        SiteInfo::updateOrCreate(
            ['id' => 1],
            [
                'title' => $this->tr(
                    'Guliston davlat pedagogika instituti',
                    'Гулистанский государственный педагогический институт',
                    'Gulistan State Pedagogical Institute'
                ),
                'desc' => $this->tr(
                    "Guliston davlat pedagogika instituti — Sirdaryo viloyatidagi pedagogik ta'lim markazi. Institut zamonaviy metodika va amaliyotga yo'naltirilgan ta'lim asosida yuqori malakali pedagog kadrlar tayyorlaydi.",
                    'Гулистанский государственный педагогический институт — центр педагогического образования Сырдарьинской области.',
                    'Gulistan State Pedagogical Institute is the centre of pedagogical education in the Syrdarya region.'
                ),
                'address' => $this->tr(
                    "120101, Sirdaryo viloyati, Guliston shahri, Talabalar ko'chasi, 49-uy",
                    '120101, Сырдарьинская область, город Гулистан, улица Талабалар, дом 49',
                    '49 Talabalar Street, Gulistan, Syrdarya region, 120101, Uzbekistan'
                ),
                'phone_number' => '+998 55 651 92 76',
                'email'        => 'info@gspi.uz',
                // Institut bergan ko'rsatkichlar
                'number_of_students'   => '5243',
                'audience_size'        => '129',  // professor-o'qituvchilar
                'educational_programs' => '19',
                'work_time'    => "Dushanba – Shanba, 08:30–13:00 / 14:00–18:00",
                'telegram'     => 'https://t.me/GulDPIUz',
                'instagram'    => 'https://instagram.com/guliston_pedagogika_instituti',
                'facebook'     => 'https://facebook.com/gulistonpedagogikainstituti',
                'youtube'      => 'https://youtube.com/@gspi',
            ]
        );
    }

    /**
     * Fakultetlar (3 ta).
     *
     * DIQQAT: nomlar vaqtinchalik — institut tasdiqlagan rasmiy nomlar bilan
     * almashtirilishi kerak. Soni institut bergan ma'lumot bo'yicha 3 ta.
     */
    private function seedFaculties(): void
    {
        $faculties = [
            'Pedagogika fakulteti',
            'Aniq va tabiiy fanlar fakulteti',
            'Ijtimoiy-gumanitar fanlar fakulteti',
        ];

        foreach ($faculties as $name) {
            Department::updateOrCreate(
                ['slug' => $this->slug($name)],
                [
                    'name'              => $this->tr($name),
                    'structure_type_id' => 3,
                    'active'            => 1,
                ]
            );
        }
    }

    /**
     * Ta'lim yo'nalishlari — hozircha namuna sifatida bir nechtasi.
     * To'liq ro'yxat (19 ta) admin panel orqali kiritiladi.
     */
    private function seedPrograms(): void
    {
        $programs = [
            ['name' => "60110100 — Pedagogika", 'years' => 4],
            ['name' => "60110500 — Boshlang'ich ta'lim", 'years' => 4],
            ['name' => "60111400 — O'zbek tili va adabiyoti", 'years' => 4],
            ['name' => '60540100 — Matematika', 'years' => 4],
        ];

        foreach ($programs as $program) {
            EducationalProgram::updateOrCreate(
                ['slug' => $this->slug($program['name'])],
                [
                    'name'            => $this->tr($program['name']),
                    'education_years' => $program['years'],
                ]
            );
        }
    }

    /**
     * Kafedralar — gspi.uz dagi amaldagi ro'yxat.
     */
    private function seedChairs(): void
    {
        $chairs = [
            'Aniq fanlar kafedrasi',
            'Pedagogika va psixologiya kafedrasi',
            "O'zbek tili va tillarni o'qitish kafedrasi",
            "Milliy g'oya va falsafa kafedrasi",
            'Jismoniy madaniyat nazariyasi va metodikasi kafedrasi',
            'Tabiiy fanlar kafedrasi',
            "Tarix va san'atshunoslik kafedrasi",
            "Maktabgacha va boshlang'ich ta'lim metodikasi kafedrasi",
            'Xorijiy tillar kafedrasi',
        ];

        foreach ($chairs as $name) {
            $slug = $this->slug($name);

            Department::updateOrCreate(
                ['slug' => $slug],
                [
                    'name'              => $this->tr($name),
                    'structure_type_id' => 4,
                    'active'            => 1,
                ]
            );
        }
    }

    /** Namunaviy yangiliklar (sarlavhalar gspi.uz dan). */
    private function seedPosts(): void
    {
        $posts = [
            [
                'title' => "Guliston davlat pedagogika instituti talabalarining grant-kontrakt taqsimoti bo'yicha to'plagan akademik ballari",
                'date'  => '2026-08-03 17:11:00',
            ],
            [
                'title' => "Texnikumlarni muvaffaqiyatli tamomlagan bitiruvchilarni oliy ta'lim muassasalarining bakalavriat ta'lim yo'nalishlariga suhbat asosida o'qishga qabul qilish",
                'date'  => '2026-08-03 11:40:00',
            ],
            [
                'title' => "“Qog'ozsiz bir kun” ekoaksiyasi",
                'date'  => '2026-07-08 17:37:00',
            ],
            [
                'title' => "Guliston davlat pedagogika institutida o'tkaziladigan kasbiy (ijodiy) imtihonlar jadvali",
                'date'  => '2026-07-01 10:59:00',
            ],
            [
                'title' => "Guliston davlat pedagogika institutiga ikkinchi va undan keyingi oliy ta'limga qabul qilish dasturlari",
                'date'  => '2026-06-26 23:50:00',
            ],
            [
                'title' => "Bitiruvchi bosqich talabalarining yakuniy davlat attestatsiyasidan topshiradigan fanlar ro'yxati",
                'date'  => '2026-02-05 09:00:00',
            ],
            [
                'title' => "Maktabgacha va maktab ta'limi tizimining ilmiy muammolari banki",
                'date'  => '2026-01-09 09:00:00',
            ],
        ];

        foreach ($posts as $post) {
            Post::updateOrCreate(
                ['slug' => $this->slug($post['title'])],
                [
                    'title' => $this->tr($post['title']),
                    'desc'  => $this->tr($post['title']),
                    'date'  => $post['date'],
                ]
            );
        }
    }

    /**
     * Ko'p tilli maydon qiymati.
     * Tarjima berilmasa, o'zbekcha matn barcha tillar uchun ishlatiladi.
     */
    private function tr(string $uz, ?string $ru = null, ?string $en = null): array
    {
        return [
            'uz' => $uz,
            'ru' => $ru ?? $uz,
            'en' => $en ?? $uz,
        ];
    }

    /** Kirill/lotin apostroflarini hisobga olgan sodda slug. */
    private function slug(string $value): string
    {
        $map = ['‘' => '', '’' => '', "'" => '', '“' => '', '”' => '', '«' => '', '»' => ''];
        $value = strtr($value, $map);

        return trim(preg_replace('/-+/', '-', preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($value))), '-');
    }
}
