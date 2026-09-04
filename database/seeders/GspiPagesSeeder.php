<?php

namespace Database\Seeders;

use App\Models\DinamikMenu;
use App\Models\FormMenu;
use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Menyu daraxti va dinamik sahifalar.
 *
 * Manba — frontend sessiyasi tayyorlagan eksport:
 *   storage/app/frontend-content.json
 *
 * Unda saytdagi menyu (7 yuqori band, 56 element) va sahifalarning
 * sarlavha / izoh matnlari uch tilda turadi. Shu paytgacha bu matnlar
 * frontend kodiga yozib qo'yilgan edi; endi ular bazaga ko'chiriladi va
 * admin paneldan tahrirlanadi.
 *
 * Idempotent: qayta ishga tushirilsa mavjud yozuvlarni yangilaydi,
 * nusxa yaratmaydi. Sahifa matni (`text`) esa faqat bo'sh bo'lsa
 * to'ldiriladi — admin paneldan kiritilgan matn ustidan yozilmaydi.
 *
 *   php artisan db:seed --class=GspiPagesSeeder
 */
class GspiPagesSeeder extends Seeder
{
    private const SOURCE = 'app/frontend-content.json';

    /**
     * Kartochkali ko'rinishga mos sahifalar.
     * Qolganlari `single` — sarlavha va HTML matn.
     */
    private const CARD_PAGES = ['conferences', 'announcements'];

    /** Faqat fayllar ro'yxati sifatida ochiladigan sahifalar. */
    private const FILE_PAGES = ['study-plans', 'syllabus', 'qualification-requirements'];

    /**
     * Blok manzillari takrorlanganda oldiga qo'shiladigan prefiks.
     * Guruh kaliti inglizcha, manzil esa saytdagi kabi o'zbekcha bo'lsin.
     */
    private const GROUP_SLUGS = [
        'commission' => 'qabul-komissiyasi',
        'bachelor'   => 'bakalavriat',
        'master'     => 'magistratura',
        'second'     => 'ikkinchi-oliy',
        'foreign'    => 'xorijiy',
    ];

    private array $data = [];

    public function run(): void
    {
        $path = storage_path(self::SOURCE);

        if (!is_file($path)) {
            $this->command?->warn('Eksport fayli topilmadi: storage/' . self::SOURCE);

            return;
        }

        $this->data = json_decode(file_get_contents($path), true) ?: [];

        if ($this->data === []) {
            $this->command?->warn('Eksport fayli o\'qilmadi.');

            return;
        }

        $this->seedMenuTree();
        $this->seedPages();
        $this->seedAdmissions();
        $this->seedConferenceCards();
        $this->seedAboutBody();
        $this->seedPageBodies();

        $this->command?->info('Menyu: ' . Menu::count() . ' band, sahifa: ' . DinamikMenu::count() . ' ta.');
    }

    /** Ikki darajali menyu daraxti. */
    private function seedMenuTree(): void
    {
        foreach ($this->data['menu'] ?? [] as $top) {
            $parent = $this->upsertMenu($top, null);

            foreach ($top['children'] ?? [] as $child) {
                $this->upsertMenu($child, $parent->id);
            }
        }
    }

    /**
     * Menyu bandi. Slug bo'yicha topiladi; slug'siz bandlar (dropdown
     * sarlavhalari) sarlavhasi bo'yicha, ota-onasi bilan birga.
     */
    private function upsertMenu(array $item, ?int $parentId): Menu
    {
        // `menus.slug` bo'sh bo'lmaydi. Dropdown sarlavhalarining o'z manzili
        // yo'q, shuning uchun ularga nomidan slug yasaladi — keyinchalik
        // admin o'sha guruhga sahifa biriktirishi mumkin.
        $slug = $item['slug'] ?: Str::slug(data_get($item, 'title.uz') ?? '');

        $attributes = [
            'title'     => $item['title'],
            'path'      => $item['path'],
            'order'     => $item['order'] ?? 0,
            'parent_id' => $parentId,
            'active'    => 1,
        ];

        // Bir nechta band bitta sahifaga ishora qilishi mumkin: "Bakalavriat",
        // "Magistratura" va "Ta'lim yo'nalishlari" — uchalasi ham
        // /educational-programs ga. Shuning uchun band slug yoki manzil bilan
        // emas, menyudagi o'rni (ota-onasi + tartib raqami) bilan aniqlanadi.
        $menu = Menu::where('parent_id', $parentId)
            ->where('order', (string) ($item['order'] ?? 0))
            ->first();

        if ($menu) {
            $menu->update($attributes);

            return $menu;
        }

        return Menu::create($attributes + ['slug' => $slug]);
    }

    /** Sahifa sarlavhalari va izohlari. */
    private function seedPages(): void
    {
        $handled = [];

        foreach ($this->data['pages'] ?? [] as $page) {
            $created = $this->upsertPage(
                $page['slug'],
                $page['title'],
                $page['subtitle'] ?? null,
                $this->layoutFor($page['slug'])
            );

            // Bloklar `pages[].blocks` da ham, alohida `pageBlocks` bo'limida
            // ham bo'lishi mumkin — bir xil yozuv ikki marta kelmasligi uchun
            // sarlavha bo'yicha birlashtiramiz.
            $blocks = $this->mergeBlocks(
                $page['blocks'] ?? [],
                $this->data['pageBlocks'][$page['slug']] ?? []
            );

            if ($blocks !== []) {
                $this->syncBlocks($created, $this->translate($blocks));
            }

            $handled[] = $page['slug'];
        }

        // `pageBlocks` da bor, lekin `pages[]` da yo'q sahifalar
        // (masalan /about va /murojaat) — sarlavhasi menyudan olinadi.
        foreach ($this->data['pageBlocks'] ?? [] as $slug => $blocks) {
            if (in_array($slug, $handled ?? [], true) || $blocks === []) {
                continue;
            }

            $menu = Menu::where('slug', $slug)->first();

            $page = $this->upsertPage(
                $slug,
                $menu?->title ?? ['uz' => $slug],
                null,
                $this->layoutFor($slug)
            );

            $this->syncBlocks($page, $this->translate($blocks));
        }

        // Bosh sahifa bloklari ham shu tartibda boshqariladi.
        if (isset($this->data['home'])) {
            $home = $this->data['home'];
            $page = $this->upsertPage(
                'home',
                $home['hero']['title'] ?? ['uz' => 'Bosh sahifa'],
                $home['hero']['subtitle'] ?? null,
                'single'
            );

            $this->syncBlocks($page, $home['blocks'] ?? []);
            $this->syncBlocks($page, $this->homeSystems());
            $this->syncBlocks($page, $this->homeLinks());
            $this->syncBlocks($page, $this->homeHeadings($home['sectionTitles'] ?? []));
            $this->syncBlocks($page, $this->homeQuickLinks());
        }
    }

    /**
     * Bosh sahifadagi "Bizning tizimlarimiz" bloki.
     *
     * Havolalar frontenddagi `EXTERNAL_LINKS` dan olingan — endi ular
     * kodda emas, admin paneldan boshqariladi.
     */
    private function homeSystems(): array
    {
        return [
            [
                'group' => 'systems',
                'order' => 11,
                'icon'  => 'Laptop',
                'link'  => 'https://hemis.gspi.uz',
                'title' => $this->tr('HEMIS', 'HEMIS', 'HEMIS'),
                'desc'  => $this->tr(
                    'Oliy taʼlim boshqaruvining axborot tizimi',
                    'Информационная система управления высшим образованием',
                    'Higher education management information system'
                ),
            ],
            [
                'group' => 'systems',
                'order' => 12,
                'icon'  => 'GraduationCap',
                'link'  => 'https://student.gspi.uz',
                'title' => $this->tr('Talaba portali', 'Портал студента', 'Student portal'),
                'desc'  => $this->tr(
                    'Talabalar uchun shaxsiy kabinet',
                    'Личный кабинет для студентов',
                    'Personal account for students'
                ),
            ],
            [
                'group' => 'systems',
                'order' => 13,
                'icon'  => 'BookOpen',
                'link'  => 'https://moodle.gspi.uz',
                'title' => $this->tr('Moodle', 'Moodle', 'Moodle'),
                'desc'  => $this->tr(
                    'Masofaviy taʼlim platformasi',
                    'Платформа дистанционного обучения',
                    'Distance learning platform'
                ),
            ],
            [
                'group' => 'systems',
                'order' => 14,
                'icon'  => 'Layers',
                'link'  => 'https://unilibrary.uz',
                'title' => $this->tr('Elektron kutubxona', 'Электронная библиотека', 'Digital library'),
                'desc'  => $this->tr(
                    'Darslik va ilmiy adabiyotlar toʻplami',
                    'Собрание учебников и научной литературы',
                    'A collection of textbooks and academic literature'
                ),
            ],
        ];
    }

    /**
     * Bosh sahifadagi bo'lim sarlavhalari.
     *
     * "Foydali havolalar", "Bizning tizimlarimiz" kabi sarlavhalar
     * frontend kodida yozib qo'yilgan edi. Endi ular ham admin paneldan
     * tahrirlanadi: har bir sarlavha `headings` guruhidagi blok bo'lib,
     * `slug` — bo'lim kaliti, `title` — sarlavha, `desc` — izoh.
     */
    private function homeHeadings(array $sectionTitles): array
    {
        $blocks = [];
        $order = 31;

        foreach ($sectionTitles as $key => $value) {
            // Izohlar alohida kalit bilan keladi: "systems" va "systemsSubtitle".
            if (str_ends_with($key, 'Subtitle')) {
                continue;
            }

            $blocks[] = [
                'group' => 'headings',
                'order' => $order++,
                'slug'  => Str::slug($key),
                'title' => $value,
                'desc'  => $sectionTitles[$key . 'Subtitle'] ?? null,
            ];
        }

        return $blocks;
    }

    /**
     * Bosh sahifadagi "Tezkor havolalar" bloki — hero ostidagi kartochka.
     *
     * Havolalar frontenddagi `QUICK_LINKS` va `EXTERNAL_LINKS` dan olingan.
     * `link` `http` bilan boshlansa sayt uni yangi oynada ochadi, aks holda
     * ichki havola sifatida — alohida bayroq kerak emas.
     */
    private function homeQuickLinks(): array
    {
        $links = [
            ['/murojaat', 'Rektorga murojaat', 'Обращение к ректору', 'Appeal to the rector'],
            ['https://hemis.gspi.uz', 'HEMIS', 'HEMIS', 'HEMIS'],
            ['https://student.gspi.uz', 'Talaba portali', 'Портал студента', 'Student portal'],
            ['/murojaat?type=tutor', 'Tyutorga murojaat', 'Обращение к тьютору', 'Appeal to the tutor'],
            ['https://unilibrary.uz', 'Elektron kutubxona', 'Электронная библиотека', 'Digital library'],
            ['/murojaat?type=compliance', 'Komplayensga murojaat', 'Обращение в комплаенс', 'Compliance appeal'],
        ];

        $blocks = [];

        foreach ($links as $index => [$href, $uz, $ru, $en]) {
            $blocks[] = [
                'group' => 'quicklinks',
                'order' => 41 + $index,
                'link'  => $href,
                'title' => $this->tr($uz, $ru, $en),
            ];
        }

        return $blocks;
    }
    /** Bosh sahifadagi "Foydali havolalar" bloki — tashqi davlat tizimlari. */
    private function homeLinks(): array
    {
        $links = [
            ['edu.uz', 'https://edu.uz',
                'Oliy taʼlim, fan va innovatsiyalar vazirligi',
                'Министерство высшего образования, науки и инноваций',
                'Ministry of Higher Education, Science and Innovation'],
            ['my.gov.uz', 'https://my.gov.uz',
                'Yagona interaktiv davlat xizmatlari portali',
                'Единый портал интерактивных государственных услуг',
                'Unified portal of interactive public services'],
            ['dtm.uz', 'https://dtm.uz',
                'Davlat test markazi',
                'Государственный центр тестирования',
                'State Testing Centre'],
            ['ziyonet.uz', 'https://ziyonet.uz',
                'Axborot-taʼlim tarmogʻi',
                'Информационно-образовательная сеть',
                'Information and education network'],
            ['lex.uz', 'https://lex.uz',
                'Qonun hujjatlari maʼlumotlar bazasi',
                'База данных законодательства',
                'National legislation database'],
        ];

        $blocks = [];

        foreach ($links as $index => [$name, $url, $uz, $ru, $en]) {
            $blocks[] = [
                'group' => 'links',
                'order' => 21 + $index,
                'link'  => $url,
                'title' => $this->tr($name, $name, $name),
                'desc'  => $this->tr($uz, $ru, $en),
            ];
        }

        return $blocks;
    }

    /** Qabul sahifasi — beshta tab, o'ttiz bir blok. */
    private function seedAdmissions(): void
    {
        $admissions = $this->data['admissions'] ?? null;

        if (!$admissions) {
            return;
        }

        $page = $this->upsertPage(
            $admissions['slug'],
            $admissions['title'],
            $admissions['subtitle'] ?? null,
            'single'
        );

        $this->syncBlocks($page, $admissions['blocks'] ?? []);
    }

    /**
     * Konferensiyalar sahifasi uchun namunaviy kartochkalar.
     *
     * `cards` ko'rinishini uchidan-uchiga tekshirish uchun: to'r → kartochka
     * bosiladi → alohida sahifa ochiladi. Haqiqiy anjumanlar admin paneldan
     * kiritiladi, bular o'rinbosar.
     */
    private function seedConferenceCards(): void
    {
        $menu = Menu::where('slug', 'conferences')->first();

        if (!$menu) {
            return;
        }

        $page = DinamikMenu::where('menu_id', $menu->id)->first();

        if (!$page) {
            return;
        }

        $cards = [
            [
                'title' => $this->tr(
                    'Pedagogik taʼlimda raqamli texnologiyalar',
                    'Цифровые технологии в педагогическом образовании',
                    'Digital technologies in teacher education'
                ),
                'desc' => $this->tr(
                    'Institutda oʻtkazilgan xalqaro ilmiy-amaliy anjuman materiallari.',
                    'Материалы международной научно-практической конференции.',
                    'Proceedings of the international research conference held at the institute.'
                ),
                'date' => '2026-04-18',
            ],
            [
                'title' => $this->tr(
                    'Ona tili va adabiyoti oʻqitish metodikasi',
                    'Методика преподавания родного языка и литературы',
                    'Methods of teaching language and literature'
                ),
                'desc' => $this->tr(
                    'Respublika miqyosidagi anjuman: maʼruzalar va tavsiyalar.',
                    'Республиканская конференция: доклады и рекомендации.',
                    'A national conference: papers and recommendations.'
                ),
                'date' => '2026-02-27',
            ],
            [
                'title' => $this->tr(
                    'Yosh tadqiqotchilar ilmiy anjumani',
                    'Научная конференция молодых исследователей',
                    'Young researchers conference'
                ),
                'desc' => $this->tr(
                    'Magistrant va yosh oʻqituvchilarning ilmiy ishlari taqdimoti.',
                    'Презентация научных работ магистрантов и молодых преподавателей.',
                    'Presentations by master\'s students and early-career lecturers.'
                ),
                'date' => '2025-11-14',
            ],
        ];

        foreach ($cards as $index => $card) {
            $slug = Str::slug(data_get($card['title'], 'uz'));

            $attributes = [
                'dinamik_menu_id' => $page->id,
                'title'           => $card['title'],
                'text'            => $card['desc'],
                'body'            => $this->cardBody($card),
                'date'            => $card['date'],
                'order'           => $index + 1,
                'active'          => 1,
            ];

            $existing = FormMenu::where('dinamik_menu_id', $page->id)
                ->where('slug', $slug)
                ->first();

            if ($existing) {
                $existing->update($attributes);

                continue;
            }

            FormMenu::create($attributes + ['slug' => $slug]);
        }
    }

    /** Kartochkaning alohida sahifasidagi matn. */
    private function cardBody(array $card): array
    {
        $body = [];

        foreach (['uz', 'ru', 'en'] as $lang) {
            $body[$lang] = '<p>' . $card['desc'][$lang] . '</p>'
                . '<p>Toʻliq maʼlumot admin paneldan kiritiladi.</p>';
        }

        return $body;
    }

    private function tr(string $uz, string $ru, string $en): array
    {
        return ['uz' => $uz, 'ru' => $ru, 'en' => $en];
    }

    private function layoutFor(string $slug): string
    {
        if (in_array($slug, self::CARD_PAGES, true)) {
            return 'cards';
        }

        return in_array($slug, self::FILE_PAGES, true) ? 'files' : 'single';
    }

    /**
     * Menyu bandini topadi (yo'q bo'lsa yashirin band yaratadi) va unga
     * dinamik sahifa biriktiradi.
     */
    private function upsertPage(string $slug, array $title, ?array $subtitle, string $layout): DinamikMenu
    {
        $menu = Menu::where('slug', $slug)->first();

        if (!$menu) {
            // Menyuda ko'rinmaydigan, lekin manzili bo'yicha ochiladigan sahifa.
            $menu = Menu::create([
                'title'  => $title,
                'slug'   => $slug,
                'path'   => '/' . $slug,
                'order'  => 999,
                'active' => 0,
            ]);
        }

        $page = DinamikMenu::where('menu_id', $menu->id)->first();

        $attributes = [
            'menu_id'     => $menu->id,
            'title'       => $title,
            'short_title' => $subtitle,
            'layout'      => $layout,
            'active'      => 1,
        ];

        if (!$page) {
            return DinamikMenu::create($attributes);
        }

        // Admin paneldan kiritilgan matn saqlanib qoladi.
        $page->update($attributes);

        return $page;
    }

    /**
     * "Institut haqida" sahifasining matni — rektor tabrigi.
     *
     * Shu paytgacha frontend tarjima faylida uch tilda yozib qo'yilgan edi.
     * Rektorning ismi ataylab qo'shilmaydi: u frontendda o'rinbosar sifatida
     * turgan, haqiqiysi `/leaderships` dan olinadi.
     *
     * Matn faqat sahifa bo'sh bo'lsa yoziladi — admin paneldan kiritilgan
     * matn ustidan yozilmaydi.
     */
    private function seedAboutBody(): void
    {
        $menu = Menu::where('slug', 'about')->first();

        if (!$menu) {
            return;
        }

        $page = DinamikMenu::where('menu_id', $menu->id)->first();

        if (!$page || !empty(array_filter((array) $page->text))) {
            return;
        }

        $greeting = [
            'uz' => [
                'Rektor tabrigi',
                'Aziz talabalar va boʻlgʻusi abituriyentlar! Guliston davlat pedagogika '
                . 'instituti veb-saytiga xush kelibsiz. Bizning asosiy maqsadimiz — zamonaviy '
                . 'talablarga javob beradigan, raqobatbardosh va yuqori intellektual salohiyatga '
                . 'ega yosh pedagog kadrlarni tarbiyalashdir. Sizlarga sifatli taʼlim berish '
                . 'yoʻlida barcha kuch-gʻayratimizni safarbar etamiz.',
            ],
            'ru' => [
                'Приветствие ректора',
                'Дорогие студенты и абитуриенты! Добро пожаловать на официальный сайт '
                . 'Гулистанского государственного педагогического института. Наша главная цель — '
                . 'воспитать высокоинтеллектуальных, конкурентоспособных молодых педагогов, '
                . 'отвечающих современным требованиям. Мы прилагаем все усилия для '
                . 'предоставления вам качественного образования.',
            ],
            'en' => [
                'Rector\'s address',
                'Dear students and future applicants! Welcome to the official website of '
                . 'Gulistan State Pedagogical Institute. Our primary goal is to train highly '
                . 'intellectual, competitive and modern pedagogical professionals. We commit our '
                . 'resources and energy to providing you with the highest standard of education.',
            ],
        ];

        $text = [];

        foreach ($greeting as $lang => [$heading, $body]) {
            $text[$lang] = '<h2>' . $heading . '</h2>' . "\n" . '<p>' . $body . '</p>';
        }

        $page->update(['text' => $text]);

        $this->command?->info('Institut haqida: rektor tabrigi qoʻshildi.');
    }

    /**
     * Sahifalarning boshlang'ich matni.
     *
     * Matn faqat sahifa bo'sh bo'lsa qo'yiladi — admin paneldan
     * kiritilgan matn hech qachon ustidan yozilmaydi.
     */
    private function seedPageBodies(): void
    {
        $bodies = require __DIR__ . '/data/page-bodies.php';
        $filled = 0;

        foreach ($bodies as $slug => $text) {
            $menu = Menu::where('slug', $slug)->first();

            if (!$menu) {
                continue;
            }

            $page = DinamikMenu::where('menu_id', $menu->id)->first();

            if (!$page || !empty(array_filter((array) $page->text))) {
                continue;
            }

            $page->update(['text' => $text]);
            $filled++;
        }

        if ($filled > 0) {
            $this->command?->info("Sahifa matni: {$filled} ta sahifaga qo'yildi.");
        }
    }

    /** Ikki ro'yxatni sarlavha bo'yicha birlashtiradi; keyingisi ustun. */
    private function mergeBlocks(array $first, array $second): array
    {
        $merged = [];

        foreach (array_merge($first, $second) as $block) {
            $title = is_array($block['title'] ?? null)
                ? ($block['title']['uz'] ?? null)
                : ($block['title'] ?? null);

            $key = $title ?? count($merged);
            $merged[$key] = $block;
        }

        return array_values($merged);
    }

    /**
     * Bloklarga rus va ingliz tilini qo'shadi.
     *
     * Frontend eksportida sahifa bloklari faqat o'zbekcha keladi.
     * Tarjimalar `database/seeders/data/page-block-translations.php` da,
     * kalit — blokning o'zbekcha sarlavhasi. Tarjimasi topilmagan blok
     * o'zbekcha holida qoladi.
     */
    private function translate(array $blocks): array
    {
        $dictionary = require __DIR__ . '/data/page-block-translations.php';

        foreach ($blocks as &$block) {
            $uzTitle = is_array($block['title'] ?? null)
                ? ($block['title']['uz'] ?? null)
                : ($block['title'] ?? null);

            if (!$uzTitle || !isset($dictionary[$uzTitle])) {
                continue;
            }

            $entry = $dictionary[$uzTitle];

            $block['title'] = $this->withTranslations($block['title'] ?? null, $uzTitle, $entry['title'] ?? []);

            $uzDesc = is_array($block['desc'] ?? null)
                ? ($block['desc']['uz'] ?? null)
                : ($block['desc'] ?? null);

            if ($uzDesc) {
                $block['desc'] = $this->withTranslations($block['desc'] ?? null, $uzDesc, $entry['desc'] ?? []);
            }
        }

        return $blocks;
    }

    /** Mavjud qiymatlarni saqlab, ru/en ni to'ldiradi. */
    private function withTranslations($value, string $uz, array $translations): array
    {
        $result = is_array($value) ? $value : [];
        $result['uz'] = $uz;
        $result['ru'] = $translations['ru'] ?? ($result['ru'] ?? $uz);
        $result['en'] = $translations['en'] ?? ($result['en'] ?? $uz);

        return $result;
    }

    /** Sahifa bloklari — tartib va tab kaliti bilan. */
    private function syncBlocks(DinamikMenu $page, array $blocks): void
    {
        // Sarlavhalar sahifa ichida takrorlanishi mumkin ("Bakalavriat" ham
        // bakalavriat tabida, ham magistratura tabida) — slug esa unikal
        // bo'lishi shart, chunki kartochka manzili shundan yasaladi.
        $used = [];

        foreach ($blocks as $index => $block) {
            $title = $block['title'] ?? null;

            if (!$title) {
                continue;
            }

            // Blokda manzil aniq berilgan bo'lsa (masalan bo'lim sarlavhalari),
            // uni saqlab qolamiz — aks holda sarlavhadan yasaymiz.
            $slug = $block['slug']
                ?? (Str::slug(data_get($title, 'uz') ?? '') ?: 'blok-' . ($index + 1));

            if (isset($used[$slug])) {
                $group = $block['group'] ?? null;
                $prefix = $group ? (self::GROUP_SLUGS[$group] ?? $group) : null;
                $candidate = $prefix ? $prefix . '-' . $slug : $slug . '-' . ($index + 1);

                $slug = isset($used[$candidate]) ? $slug . '-' . ($index + 1) : $candidate;
            }

            $used[$slug] = true;

            $attributes = [
                'dinamik_menu_id' => $page->id,
                'title'           => $title,
                'text'            => $block['desc'] ?? null,
                'group'           => $block['group'] ?? null,
                'icon'            => $block['icon'] ?? null,
                'link'            => $block['link'] ?? null,
                'order'           => $block['order'] ?? $index + 1,
                'active'          => 1,
            ];

            $existing = FormMenu::where('dinamik_menu_id', $page->id)
                ->where('slug', $slug)
                ->first();

            if ($existing) {
                $existing->update($attributes);

                continue;
            }

            FormMenu::create($attributes + ['slug' => $slug]);
        }
    }
}
