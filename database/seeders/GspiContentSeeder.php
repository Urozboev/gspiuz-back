<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Partner;
use App\Models\Post;
use App\Models\PostsCategory;
use App\Models\Member;
use App\Models\Question;
use App\Models\Service;
use App\Models\SiteInfo;
use App\Models\Vacancy;
use Illuminate\Database\Seeder;

/**
 * Frontend sahifalari bo'sh qolmasligi uchun kontent.
 *
 * DIQQAT — BU YERDAGI MA'LUMOTLARNING KO'PI O'RINBOSAR (haqiqiy emas).
 * Quyidagilar hech qanday rasmiy manbaga tayanmaydi, ishonarli ko'rinsin
 * deb yozilgan: jurnallar, iqtidorli talabalar, vakansiyalar va FAQ
 * javoblari. (Bank rekvizitlari bu yerdan butunlay olib tashlandi.)
 * Haqiqiy manbadan olingani faqat ikkitasi: hamkorlar ro'yxati
 * (edu.uz, dtm.uz, ziyonet.uz) va lex.uz dagi qonun havolasi.
 *
 * Ishlab chiqarish serveriga chiqarishdan oldin: php artisan demo:audit
 *
 *   php artisan db:seed --class=GspiContentSeeder
 */
class GspiContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPostCategories();
        $this->seedRequisites();
        $this->seedDocuments();
        $this->seedFaq();
        $this->seedPartners();
        $this->seedVacancies();
        $this->seedJournals();
        $this->seedStudents();
    }

    /** Yangilik turkumlari — /news?category=... uchun. */
    private function seedPostCategories(): void
    {
        $categories = [
            ['slug' => 'yangiliklar', 'title' => $this->tr('Yangiliklar', 'Новости', 'News')],
            ['slug' => 'elonlar',     'title' => $this->tr("E'lonlar", 'Объявления', 'Announcements')],
            ['slug' => 'tadbirlar',   'title' => $this->tr('Tadbirlar', 'Мероприятия', 'Events')],
            ['slug' => 'konferensiyalar', 'title' => $this->tr('Konferensiyalar', 'Конференции', 'Conferences')],
        ];

        foreach ($categories as $category) {
            PostsCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }

        // Turkumsiz postlarni "Yangiliklar"ga biriktiramiz,
        // e'lon xarakteridagilarini esa "E'lonlar"ga.
        $news = PostsCategory::where('slug', 'yangiliklar')->first();
        $announcements = PostsCategory::where('slug', 'elonlar')->first();

        foreach (Post::with('postsCategories')->get() as $post) {
            if ($post->postsCategories->isNotEmpty()) {
                continue;
            }

            $title = is_array($post->title) ? ($post->title['uz'] ?? '') : (string) $post->title;
            $isAnnouncement = (bool) preg_match('/jadval|qabul|royxat|elon/iu', $title);

            $post->postsCategories()->syncWithoutDetaching([
                ($isAnnouncement ? $announcements : $news)->id,
            ]);
        }
    }

    /**
     * Bank rekvizitlari — /requisites sahifasi uchun.
     *
     * O'RINBOSAR — RAQAMLAR HAQIQIY EMAS. Hisob raqami, g'aznachilik
     * raqami, MFO, STIR va OKED o'ylab topilgan. Bu raqamlar bo'yicha
     * pul o'tkazilishi mumkin bo'lgani uchun eng xavflisi — prodga
     * chiqishdan oldin albatta haqiqiysi bilan almashtirilsin.
     */
    private function seedRequisites(): void
    {
        $siteInfo = SiteInfo::latest()->first();

        if (!$siteInfo) {
            return;
        }

        // Faqat yuridik nom — bu haqiqiy ma'lumot.
        //
        // Bank rekvizitlari (hisob raqami, g'aznachilik hisobi, MFO, STIR,
        // OKED, bank nomi) ATAYLAB seed qilinmaydi. Ilgari bu yerda o'ylab
        // topilgan raqamlar turardi; ular bo'yicha kontrakt to'lovi
        // o'tkazilishi mumkin bo'lgani uchun olib tashlandi.
        // Haqiqiy raqamlar buxgalteriyadan olinib, admin panelning
        // "Sayt ma'lumotlari" bo'limidan kiritiladi.
        $siteInfo->fill([
            'legal_name' => $this->tr(
                'Guliston davlat pedagogika instituti',
                'Гулистанский государственный педагогический институт',
                'Gulistan State Pedagogical Institute'
            ),
        ])->save();
    }

    /** Hujjat turkumlari va namunaviy hujjatlar. */
    private function seedDocuments(): void
    {
        $categories = [
            'normativ-hujjatlar' => $this->tr('Normativ hujjatlar', 'Нормативные документы', 'Regulatory documents'),
            'ochiq-malumotlar'   => $this->tr("Ochiq ma'lumotlar", 'Открытые данные', 'Open data'),
            'nizomlar'           => $this->tr('Nizom va qoidalar', 'Уставы и положения', 'Charters and regulations'),
            'buyruqlar'          => $this->tr('Buyruqlar', 'Приказы', 'Orders'),
        ];

        foreach ($categories as $slug => $title) {
            DocumentCategory::updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'slug'  => $slug,
            ]);
        }

        $documents = [
            ['slug' => 'talim-togrisidagi-qonun', 'category' => 'normativ-hujjatlar', 'date' => '2024-09-02', 'link' => 'https://lex.uz/docs/-5013009', 'title' => $this->tr(
                'O\'zbekiston Respublikasining "Ta\'lim to\'g\'risida"gi Qonuni',
                'Закон Республики Узбекистан «Об образовании»',
                'Law of the Republic of Uzbekistan "On Education"'
            )],
            ['slug' => 'ichki-tartib-qoidalari', 'category' => 'nizomlar', 'date' => '2024-01-15', 'title' => $this->tr(
                'Institut ichki tartib-qoidalari',
                'Правила внутреннего распорядка института',
                'Internal regulations of the institute'
            )],
            ['slug' => 'talabalar-soni', 'category' => 'ochiq-malumotlar', 'date' => '2025-01-10', 'title' => $this->tr(
                "Talabalar soni to'g'risida ma'lumot",
                'Сведения о численности студентов',
                'Information on the number of students'
            )],
            ['slug' => 'kasbiy-ijodiy-imtihonlar-buyrugi', 'category' => 'buyruqlar', 'date' => '2025-02-03', 'title' => $this->tr(
                "Kasbiy-ijodiy imtihonlarni tashkil etish to'g'risida buyruq",
                'Приказ об организации профессионально-творческих экзаменов',
                'Order on organising professional-creative examinations'
            )],
        ];

        foreach ($documents as $document) {
            $category = DocumentCategory::where('slug', $document['category'])->first();

            // Kalit sifatida slug — JSON sarlavha bo'yicha qidiruv baza
            // formatlashiga bog'liq bo'lib, dublikat yaratib yuborardi.
            Document::updateOrCreate(
                ['slug' => $document['slug']],
                [
                    'title'                => $document['title'],
                    'date'                 => $document['date'],
                    'link'                 => $document['link'] ?? null,
                    'document_category_id' => $category?->id,
                ]
            );
        }
    }

    /** Ko'p beriladigan savollar. O'RINBOSAR — javoblar tasdiqlanmagan. */
    private function seedFaq(): void
    {
        $faq = [
            [
                'question' => $this->tr(
                    'Institutga hujjat topshirish muddati qachon?',
                    'Когда принимаются документы в институт?',
                    'When are applications accepted?'
                ),
                'answer' => $this->tr(
                    'Hujjatlar har yili iyun-iyul oylarida Davlat test markazi tizimi orqali qabul qilinadi.',
                    'Документы принимаются ежегодно в июне-июле через систему Государственного центра тестирования.',
                    'Applications are accepted every June-July through the State Testing Centre system.'
                ),
            ],
            [
                'question' => $this->tr(
                    "Talabalar turar joyi bilan ta'minlanadimi?",
                    'Обеспечиваются ли студенты общежитием?',
                    'Is student accommodation provided?'
                ),
                'answer' => $this->tr(
                    "Ha, institut talabalar turar joyi bilan ta'minlaydi. Joylar imtiyozli toifalar hisobga olingan holda taqsimlanadi.",
                    'Да, институт предоставляет общежитие. Места распределяются с учётом льготных категорий.',
                    'Yes, the institute provides a dormitory. Places are allocated taking privileged categories into account.'
                ),
            ],
            [
                'question' => $this->tr(
                    "Kontrakt to'lovini bo'lib to'lash mumkinmi?",
                    'Можно ли оплатить контракт частями?',
                    'Can the tuition fee be paid in instalments?'
                ),
                'answer' => $this->tr(
                    "Ha, kontrakt to'lovi shartnomada belgilangan muddatlarda bosqichma-bosqich amalga oshiriladi.",
                    'Да, оплата контракта производится поэтапно в сроки, указанные в договоре.',
                    'Yes, the fee is paid in stages within the deadlines set out in the contract.'
                ),
            ],
        ];

        foreach ($faq as $item) {
            $this->upsertByUz(Question::class, 'question', $item['question']['uz'], $item);
        }
    }

    /** Hamkorlar. Tashkilotlar va havolalari haqiqiy. */
    private function seedPartners(): void
    {
        $partners = [
            [
                'link'  => 'https://edu.uz',
                'title' => $this->tr(
                    'Oliy ta\'lim, fan va innovatsiyalar vazirligi',
                    'Министерство высшего образования, науки и инноваций',
                    'Ministry of Higher Education, Science and Innovation'
                ),
            ],
            [
                'link'  => 'https://dtm.uz',
                'title' => $this->tr('Davlat test markazi', 'Государственный центр тестирования', 'State Testing Centre'),
            ],
            [
                'link'  => 'https://ziyonet.uz',
                'title' => $this->tr(
                    'Ziyonet axborot-ta\'lim tarmog\'i',
                    'Информационно-образовательная сеть Ziyonet',
                    'Ziyonet educational network'
                ),
            ],
        ];

        foreach ($partners as $partner) {
            // partner = 1 — "Hamkorlar" bo'limi (partner = 0 esa foydali havolalar).
            Partner::updateOrCreate(['link' => $partner['link']], $partner + ['img' => '', 'partner' => 1]);
        }
    }

    /** Bo'sh ish o'rinlari. O'RINBOSAR — bunday vakansiyalar e'lon qilinmagan. */
    private function seedVacancies(): void
    {
        $vacancies = [
            [
                'title' => $this->tr(
                    "Pedagogika kafedrasi o'qituvchisi",
                    'Преподаватель кафедры педагогики',
                    'Lecturer at the Department of Pedagogy'
                ),
                'desc' => $this->tr(
                    "Oliy ma'lumot va pedagogik yo'nalish bo'yicha kamida 2 yil ish tajribasi talab etiladi.",
                    'Требуется высшее образование и опыт работы по педагогическому направлению не менее 2 лет.',
                    'Higher education and at least 2 years of teaching experience are required.'
                ),
                'week'  => $this->tr('Dushanba - Shanba', 'Понедельник - Суббота', 'Monday - Saturday'),
                'price' => $this->tr('Kelishilgan holda', 'По договорённости', 'Negotiable'),
                'date'  => '2026-08-01',
            ],
            [
                'title' => $this->tr(
                    'Axborot xizmati mutaxassisi',
                    'Специалист информационной службы',
                    'Information service specialist'
                ),
                'desc' => $this->tr(
                    "Sayt va ijtimoiy tarmoqlarni yuritish hamda kontent tayyorlash bo'yicha tajriba.",
                    'Опыт ведения сайта и социальных сетей, подготовки контента.',
                    'Experience running a website and social media, and preparing content.'
                ),
                'week'  => $this->tr('Dushanba - Juma', 'Понедельник - Пятница', 'Monday - Friday'),
                'price' => $this->tr('Kelishilgan holda', 'По договорённости', 'Negotiable'),
                'date'  => '2026-08-10',
            ],
        ];

        foreach ($vacancies as $vacancy) {
            $this->upsertByUz(Vacancy::class, 'title', $vacancy['title']['uz'], $vacancy);
        }
    }

    /** Ilmiy jurnallar. O'RINBOSAR — jurnal nomlari o'ylab topilgan. */
    private function seedJournals(): void
    {
        $journals = [
            [
                'slug'  => 'pedagogik-mahorat',
                'title' => $this->tr('Pedagogik mahorat', 'Педагогическое мастерство', 'Pedagogical Skill'),
                'desc'  => $this->tr(
                    "Pedagogika va ta'lim metodikasi bo'yicha ilmiy-nazariy jurnal. Yiliga to'rt marta chop etiladi.",
                    'Научно-теоретический журнал по педагогике и методике образования. Выходит четыре раза в год.',
                    'A scholarly journal on pedagogy and teaching methods, published four times a year.'
                ),
            ],
            [
                'slug'  => 'ilmiy-axborotnoma',
                'title' => $this->tr('Ilmiy axborotnoma', 'Научный вестник', 'Scientific Bulletin'),
                'desc'  => $this->tr(
                    "Institut professor-o'qituvchilarining tadqiqot natijalari e'lon qilinadigan to'plam.",
                    'Сборник, в котором публикуются результаты исследований профессорско-преподавательского состава института.',
                    'A collection publishing research results by the institute academic staff.'
                ),
            ],
            [
                'slug'  => 'yosh-tadqiqotchi',
                'title' => $this->tr('Yosh tadqiqotchi', 'Молодой исследователь', 'Young Researcher'),
                'desc'  => $this->tr(
                    'Talabalar va yosh olimlarning ilmiy maqolalari uchun nashr.',
                    'Издание для научных статей студентов и молодых учёных.',
                    'A publication for research papers by students and early-career scholars.'
                ),
            ],
        ];

        foreach ($journals as $journal) {
            Service::updateOrCreate(['slug' => $journal['slug']], $journal);
        }
    }

    /** Iqtidorli talabalar. O'RINBOSAR — ismlar va yutuqlar o'ylab topilgan. */
    private function seedStudents(): void
    {
        $students = [
            [
                'slug'     => 'shahnoza-rasulova',
                'name'     => $this->tr('Shahnoza Rasulova', 'Шахноза Расулова', 'Shahnoza Rasulova'),
                'position' => $this->tr(
                    'Pedagogika fakulteti, 3-kurs',
                    'Педагогический факультет, 3 курс',
                    'Faculty of Pedagogy, 3rd year'
                ),
                'dec' => $this->tr(
                    "Respublika fan olimpiadasi g'olibi, \"Yosh tadqiqotchi\" jurnalida uchta maqola muallifi.",
                    'Победитель республиканской предметной олимпиады, автор трёх статей в журнале «Молодой исследователь».',
                    'Winner of the national subject olympiad and author of three papers in the Young Researcher journal.'
                ),
                'yers'   => '2023',
                'gender' => 'female',
            ],
            [
                'slug'     => 'islom-yodgorov',
                'name'     => $this->tr('Islom Yodgorov', 'Ислом Ёдгоров', 'Islom Yodgorov'),
                'position' => $this->tr(
                    'Aniq va tabiiy fanlar fakulteti, 4-kurs',
                    'Факультет точных и естественных наук, 4 курс',
                    'Faculty of Exact and Natural Sciences, 4th year'
                ),
                'dec' => $this->tr(
                    'Matematika bo\'yicha xalqaro olimpiada sovrindori, "Zulfiya" davlat stipendiyasi sohibi.',
                    'Призёр международной олимпиады по математике, обладатель государственной стипендии «Зульфия».',
                    'Medallist at an international mathematics olympiad and holder of a state scholarship.'
                ),
                'yers'   => '2022',
                'gender' => 'male',
            ],
            [
                'slug'     => 'zilola-tosheva',
                'name'     => $this->tr('Zilola Tosheva', 'Зилола Тошева', 'Zilola Tosheva'),
                'position' => $this->tr(
                    'Ijtimoiy-gumanitar fanlar fakulteti, 2-kurs',
                    'Факультет социально-гуманитарных наук, 2 курс',
                    'Faculty of Social Sciences and Humanities, 2nd year'
                ),
                'dec' => $this->tr(
                    "Talabalar ilmiy jamiyati rahbari, viloyat ijodiy tanlovlari g'olibi.",
                    'Руководитель студенческого научного общества, победитель областных творческих конкурсов.',
                    'Head of the student research society and winner of regional creative competitions.'
                ),
                'yers'   => '2024',
                'gender' => 'female',
            ],
        ];

        foreach ($students as $student) {
            Member::updateOrCreate(['slug' => $student['slug']], $student);
        }
    }

    /**
     * Ko'p tilli maydonning o'zbekcha qiymati bo'yicha topib yangilaydi,
     * topilmasa yaratadi. JSON ustunlar bo'yicha SQL qidiruvi baza
     * formatlashiga bog'liq bo'lgani uchun taqqoslash PHP tarafda bajariladi.
     */
    private function upsertByUz(string $model, string $field, string $uzValue, array $attributes): void
    {
        $existing = $model::all()->first(function ($row) use ($field, $uzValue) {
            $value = $row->{$field};

            return is_array($value) ? ($value['uz'] ?? null) === $uzValue : $value === $uzValue;
        });

        if ($existing) {
            $existing->fill($attributes)->save();

            return;
        }

        $model::create($attributes);
    }

    /** Uch tilli maydon uchun yordamchi. */
    private function tr(string $uz, ?string $ru = null, ?string $en = null): array
    {
        return ['uz' => $uz, 'ru' => $ru ?? $uz, 'en' => $en ?? $uz];
    }
}
