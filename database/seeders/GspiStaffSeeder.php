<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employ;
use App\Models\EmployFor;
use App\Models\EmployMeta;
use App\Models\EmployStaff;
use App\Models\EmployType;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Xodimlar tuzilmasi: lavozimlar, bo'limlar va namunaviy xodimlar.
 *
 * Bularsiz /leaderships, /department va /tutors bo'sh qaytadi.
 * Ishga tushirish:  php artisan db:seed --class=GspiStaffSeeder
 *
 * DIQQAT — BARCHA XODIMLAR O'RINBOSAR (haqiqiy emas).
 * Ismlar, familiyalar, telefon raqamlar va email manzillar to'liq
 * o'ylab topilgan; institutda bunday xodimlar bor-yo'qligi tekshirilmagan.
 * Lavozim va bo'lim nomlari oliygohlarda odatda uchraydigan umumiy
 * nomlar, GSPI da aynan shunday atalishi tasdiqlanmagan.
 *
 * Ishlab chiqarish serveriga chiqarishdan oldin: php artisan demo:audit
 */
class GspiStaffSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLookups();
        $this->seedPositions();
        $this->seedDepartments();
        $this->seedEmployees();
    }

    /**
     * O'rinbosar tug'ilgan sana.
     *
     * Slug'dan barqaror hosil qilinadi, ya'ni qayta seed qilinganda
     * o'zgarmaydi. Bittasi ataylab bugungi kunga to'g'rilanadi — bosh
     * sahifadagi tabrik blokini ko'z bilan tekshirish uchun.
     */
    private function birthdayFor(string $slug): string
    {
        if ($slug === 'rektor') {
            return now()->subYears(52)->format('Y-m-d');
        }

        $hash = crc32($slug);
        $year = 1965 + ($hash % 30);
        $month = 1 + ($hash % 12);
        $day = 1 + ($hash % 28);

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    /** Shtat, ish shakli va band bo'lish turlari. */
    private function seedLookups(): void
    {
        // EmployStaff::$casts da 'name' => 'array' — oddiy satr yozilsa,
        // admin paneldagi ro'yxat $name['uz'] ga murojaat qilib yiqiladi.
        $staff = [
            1 => $this->tr('Shtat', 'Штат', 'Full-time'),
            2 => $this->tr('Shtatdan tashqari', 'Внештатный', 'Part-time'),
        ];
        foreach ($staff as $id => $name) {
            EmployStaff::updateOrCreate(['id' => $id], ['name' => $name, 'order' => $id]);
        }

        $forms = [
            1 => $this->tr('Doimiy', 'Постоянный', 'Permanent'),
            2 => $this->tr('Muddatli', 'Срочный', 'Fixed-term'),
        ];
        foreach ($forms as $id => $name) {
            EmployFor::updateOrCreate(['id' => $id], ['name' => $name, 'order' => $id]);
        }

        $types = [
            1 => ['slug' => 'asosiy',      'name' => $this->tr('Asosiy ish joyi', 'Основное место работы', 'Primary employment')],
            2 => ['slug' => 'orindoshlik', 'name' => $this->tr("O'rindoshlik", 'Совместительство', 'Concurrent employment')],
        ];
        foreach ($types as $id => $type) {
            EmployType::updateOrCreate(['id' => $id], $type + ['order' => $id]);
        }
    }

    /**
     * Lavozimlar.
     * ID 4 — "Bo'lim boshlig'i": /department endpointi shu ID'ni standart filtr
     * sifatida ishlatadi, shuning uchun ID'lar aniq belgilangan.
     */
    private function seedPositions(): void
    {
        $positions = [
            1  => $this->tr('Rektor', 'Ректор', 'Rector'),
            2  => $this->tr('Prorektor', 'Проректор', 'Vice-Rector'),
            3  => $this->tr('Dekan', 'Декан', 'Dean'),
            4  => $this->tr("Bo'lim boshlig'i", 'Начальник отдела', 'Head of department'),
            5  => $this->tr('Kafedra mudiri', 'Заведующий кафедрой', 'Head of chair'),
            6  => $this->tr('Tyutor', 'Тьютор', 'Tutor'),
            7  => $this->tr("Katta o'qituvchi", 'Старший преподаватель', 'Senior lecturer'),
            8  => $this->tr("O'qituvchi", 'Преподаватель', 'Lecturer'),
            12 => $this->tr('Professor', 'Профессор', 'Professor'),
            13 => $this->tr('Dotsent', 'Доцент', 'Associate professor'),
        ];

        foreach ($positions as $id => $name) {
            Position::withTrashed()->updateOrCreate(['id' => $id], [
                'name'       => $name,
                'order'      => $id,
                'active'     => 1,
                'deleted_at' => null,
            ]);
        }
    }

    /**
     * Rahbariyat va ma'muriy bo'limlar.
     * structure_type_id: 1 — Rahbariyat, 2 — Boshqarma va bo'limlar.
     */
    private function seedDepartments(): void
    {
        $departments = [
            ['slug' => 'rahbariyat',            'type' => 1, 'name' => $this->tr('Institut rahbariyati', 'Руководство института', 'Institute management')],
            ['slug' => 'oquv-uslubiy-bolim',    'type' => 2, 'name' => $this->tr("O'quv-uslubiy bo'lim", 'Учебно-методический отдел', 'Academic affairs office')],
            ['slug' => 'yoshlar-bilan-ishlash', 'type' => 2, 'name' => $this->tr("Yoshlar bilan ishlash bo'limi", 'Отдел по работе с молодёжью', 'Youth affairs office')],
            ['slug' => 'axborot-xizmati',       'type' => 2, 'name' => $this->tr('Axborot xizmati', 'Информационная служба', 'Information service')],
        ];

        foreach ($departments as $department) {
            Department::withTrashed()->updateOrCreate(
                ['slug' => $department['slug']],
                [
                    'name'              => $department['name'],
                    'structure_type_id' => $department['type'],
                    'active'            => 1,
                    'deleted_at'        => null,
                ]
            );
        }
    }

    /** Namunaviy xodimlar. O'RINBOSAR — ism, telefon va email haqiqiy emas. */
    private function seedEmployees(): void
    {
        $employees = [
            [
                'slug'       => 'rektor',
                'department' => 'rahbariyat',
                'position'   => 1,
                'professor'  => 1,
                'leader'     => 1,
                'first_name' => $this->tr('Bahodir', 'Баходир', 'Bahodir'),
                'last_name'  => $this->tr('Rahmonov', 'Рахмонов', 'Rahmonov'),
                'surname'    => $this->tr('Anvarovich', 'Анварович', 'Anvarovich'),
                'post'       => $this->tr('Rektor', 'Ректор', 'Rector'),
                'work_time'  => $this->tr('Dushanba, 15:00 - 17:00', 'Понедельник, 15:00 - 17:00', 'Monday, 15:00 - 17:00'),
                'phone'      => '+998 67 225 40 60',
                'email'      => 'rektor@gspi.uz',
            ],
            [
                'slug'       => 'oquv-ishlari-prorektori',
                'department' => 'rahbariyat',
                'position'   => 2,
                'professor'  => 1,
                'leader'     => 1,
                'first_name' => $this->tr('Nodira', 'Нодира', 'Nodira'),
                'last_name'  => $this->tr('Yusupova', 'Юсупова', 'Yusupova'),
                'surname'    => $this->tr('Olimovna', 'Олимовна', 'Olimovna'),
                'post'       => $this->tr("O'quv ishlari bo'yicha prorektor", 'Проректор по учебной работе', 'Vice-Rector for Academic Affairs'),
                'work_time'  => $this->tr('Seshanba, 15:00 - 17:00', 'Вторник, 15:00 - 17:00', 'Tuesday, 15:00 - 17:00'),
                'phone'      => '+998 67 225 40 61',
                'email'      => 'prorektor@gspi.uz',
            ],
            [
                'slug'       => 'yoshlar-prorektori',
                'department' => 'rahbariyat',
                'position'   => 2,
                'leader'     => 1,
                'first_name' => $this->tr('Sardor', 'Сардор', 'Sardor'),
                'last_name'  => $this->tr('Ergashev', 'Эргашев', 'Ergashev'),
                'surname'    => $this->tr('Baxtiyorovich', 'Бахтиёрович', 'Bakhtiyorovich'),
                'post'       => $this->tr("Yoshlar masalalari bo'yicha prorektor", 'Проректор по делам молодёжи', 'Vice-Rector for Youth Affairs'),
                'work_time'  => $this->tr('Chorshanba, 15:00 - 17:00', 'Среда, 15:00 - 17:00', 'Wednesday, 15:00 - 17:00'),
                'phone'      => '+998 67 225 40 62',
                'email'      => 'yoshlar@gspi.uz',
            ],
            [
                'slug'       => 'oquv-uslubiy-bolim-boshligi',
                'department' => 'oquv-uslubiy-bolim',
                'position'   => 4,
                'first_name' => $this->tr('Dilnoza', 'Дилноза', 'Dilnoza'),
                'last_name'  => $this->tr('Qodirova', 'Кодирова', 'Qodirova'),
                'surname'    => $this->tr('Shuhratovna', 'Шухратовна', 'Shuhratovna'),
                'post'       => $this->tr("O'quv-uslubiy bo'lim boshlig'i", 'Начальник учебно-методического отдела', 'Head of Academic Affairs'),
                'phone'      => '+998 67 225 40 70',
                'email'      => 'oquv@gspi.uz',
            ],
            [
                'slug'       => 'axborot-xizmati-boshligi',
                'department' => 'axborot-xizmati',
                'position'   => 4,
                'first_name' => $this->tr('Jasur', 'Жасур', 'Jasur'),
                'last_name'  => $this->tr('Tursunov', 'Турсунов', 'Tursunov'),
                'surname'    => $this->tr('Ilhomovich', 'Ильхомович', 'Ilhomovich'),
                'post'       => $this->tr("Axborot xizmati boshlig'i", 'Начальник информационной службы', 'Head of Information Service'),
                'phone'      => '+998 67 225 40 71',
                'email'      => 'axborot@gspi.uz',
            ],
            [
                'slug'       => 'tyutor-pedagogika',
                'department' => 'pedagogika-fakulteti',
                'position'   => 6,
                'first_name' => $this->tr('Gulnora', 'Гулнора', 'Gulnora'),
                'last_name'  => $this->tr('Aliyeva', 'Алиева', 'Aliyeva'),
                'surname'    => $this->tr('Rustamovna', 'Рустамовна', 'Rustamovna'),
                'post'       => $this->tr('Tyutor', 'Тьютор', 'Tutor'),
                'work_time'  => $this->tr('Har kuni, 09:00 - 17:00', 'Ежедневно, 09:00 - 17:00', 'Daily, 09:00 - 17:00'),
                'phone'      => '+998 67 225 40 80',
                'email'      => 'tyutor1@gspi.uz',
            ],
            [
                'slug'       => 'tyutor-aniq-fanlar',
                'department' => 'aniq-va-tabiiy-fanlar-fakulteti',
                'position'   => 6,
                'first_name' => $this->tr('Otabek', 'Отабек', 'Otabek'),
                'last_name'  => $this->tr('Nazarov', 'Назаров', 'Nazarov'),
                'surname'    => $this->tr('Zafarovich', 'Зафарович', 'Zafarovich'),
                'post'       => $this->tr('Tyutor', 'Тьютор', 'Tutor'),
                'work_time'  => $this->tr('Har kuni, 09:00 - 17:00', 'Ежедневно, 09:00 - 17:00', 'Daily, 09:00 - 17:00'),
                'phone'      => '+998 67 225 40 81',
                'email'      => 'tyutor2@gspi.uz',
            ],
            [
                'slug'       => 'tyutor-ijtimoiy-gumanitar',
                'department' => 'ijtimoiy-gumanitar-fanlar-fakulteti',
                'position'   => 6,
                'first_name' => $this->tr('Malika', 'Малика', 'Malika'),
                'last_name'  => $this->tr('Xolmatova', 'Холматова', 'Xolmatova'),
                'surname'    => $this->tr('Baxtiyorovna', 'Бахтиёровна', 'Bakhtiyorovna'),
                'post'       => $this->tr('Tyutor', 'Тьютор', 'Tutor'),
                'work_time'  => $this->tr('Har kuni, 09:00 - 17:00', 'Ежедневно, 09:00 - 17:00', 'Daily, 09:00 - 17:00'),
                'phone'      => '+998 67 225 40 82',
                'email'      => 'tyutor3@gspi.uz',
            ],
            [
                'slug'       => 'dekan-pedagogika',
                'department' => 'pedagogika-fakulteti',
                'position'   => 3,
                'leader'     => 1,
                'first_name' => $this->tr('Feruza', 'Феруза', 'Feruza'),
                'last_name'  => $this->tr('Sattorova', 'Сатторова', 'Sattorova'),
                'surname'    => $this->tr('Alisherovna', 'Алишеровна', 'Alisherovna'),
                'post'       => $this->tr('Pedagogika fakulteti dekani', 'Декан педагогического факультета', 'Dean of the Faculty of Pedagogy'),
                'phone'      => '+998 67 225 41 10',
                'email'      => 'dekan.pedagogika@gspi.uz',
            ],
            [
                'slug'       => 'dekan-aniq-fanlar',
                'department' => 'aniq-va-tabiiy-fanlar-fakulteti',
                'position'   => 3,
                'leader'     => 1,
                'first_name' => $this->tr('Ulug\'bek', 'Улугбек', 'Ulugbek'),
                'last_name'  => $this->tr('Xasanov', 'Хасанов', 'Xasanov'),
                'surname'    => $this->tr('Tolibovich', 'Толибович', 'Tolibovich'),
                'post'       => $this->tr('Aniq va tabiiy fanlar fakulteti dekani', 'Декан факультета точных и естественных наук', 'Dean of the Faculty of Exact and Natural Sciences'),
                'phone'      => '+998 67 225 41 11',
                'email'      => 'dekan.aniq@gspi.uz',
            ],
            [
                'slug'       => 'dekan-ijtimoiy-gumanitar',
                'department' => 'ijtimoiy-gumanitar-fanlar-fakulteti',
                'position'   => 3,
                'leader'     => 1,
                'first_name' => $this->tr('Xurshid', 'Хуршид', 'Xurshid'),
                'last_name'  => $this->tr('Umarov', 'Умаров', 'Umarov'),
                'surname'    => $this->tr('Sobirovich', 'Собирович', 'Sobirovich'),
                'post'       => $this->tr('Ijtimoiy-gumanitar fanlar fakulteti dekani', 'Декан факультета социально-гуманитарных наук', 'Dean of the Faculty of Social Sciences and Humanities'),
                'phone'      => '+998 67 225 41 12',
                'email'      => 'dekan.ijtimoiy@gspi.uz',
            ],
            [
                'slug'       => 'mudir-pedagogika-psixologiya',
                'department' => 'pedagogika-va-psixologiya-kafedrasi',
                'position'   => 5,
                'leader'     => 1,
                'first_name' => $this->tr('Nigora', 'Нигора', 'Nigora'),
                'last_name'  => $this->tr('Ibrohimova', 'Иброхимова', 'Ibrohimova'),
                'surname'    => $this->tr('Qahramonovna', 'Кахрамоновна', 'Qahramonovna'),
                'post'       => $this->tr('Kafedra mudiri', 'Заведующая кафедрой', 'Head of chair'),
                'phone'      => '+998 67 225 41 20',
                'email'      => 'kafedra.pedagogika@gspi.uz',
            ],
            [
                'slug'       => 'mudir-aniq-fanlar',
                'department' => 'aniq-fanlar-kafedrasi',
                'position'   => 5,
                'leader'     => 1,
                'first_name' => $this->tr('Sherzod', 'Шерзод', 'Sherzod'),
                'last_name'  => $this->tr('Mamatqulov', 'Маматкулов', 'Mamatqulov'),
                'surname'    => $this->tr('G\'ayratovich', 'Гайратович', 'Gayratovich'),
                'post'       => $this->tr('Kafedra mudiri', 'Заведующий кафедрой', 'Head of chair'),
                'phone'      => '+998 67 225 41 21',
                'email'      => 'kafedra.aniq@gspi.uz',
            ],
            [
                'slug'       => 'professor-tarix',
                'department' => 'tarix-va-sanatshunoslik-kafedrasi',
                'position'   => 12,
                'professor'  => 1,
                'first_name' => $this->tr('Anvar', 'Анвар', 'Anvar'),
                'last_name'  => $this->tr('Qosimov', 'Косимов', 'Qosimov'),
                'surname'    => $this->tr('Rashidovich', 'Рашидович', 'Rashidovich'),
                'post'       => $this->tr('Professor, tarix fanlari doktori', 'Профессор, доктор исторических наук', 'Professor, Doctor of Historical Sciences'),
                'phone'      => '+998 67 225 41 30',
                'email'      => 'a.qosimov@gspi.uz',
            ],
            [
                'slug'       => 'dotsent-xorijiy-tillar',
                'department' => 'xorijiy-tillar-kafedrasi',
                'position'   => 13,
                'professor'  => 1,
                'first_name' => $this->tr('Kamola', 'Камола', 'Kamola'),
                'last_name'  => $this->tr('Ismoilova', 'Исмоилова', 'Ismoilova'),
                'surname'    => $this->tr('Bahodirovna', 'Баходировна', 'Bahodirovna'),
                'post'       => $this->tr('Dotsent, filologiya fanlari nomzodi', 'Доцент, кандидат филологических наук', 'Associate professor, PhD in Philology'),
                'phone'      => '+998 67 225 41 31',
                'email'      => 'k.ismoilova@gspi.uz',
            ],
        ];

        foreach ($employees as $data) {
            $department = Department::where('slug', $data['department'])->first();

            if (!$department) {
                continue;
            }

            $employ = Employ::withTrashed()->updateOrCreate(['slug' => $data['slug']], [
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'surname'    => $data['surname'],
                'position'   => $data['post'],
                'work_time'  => $data['work_time'] ?? null,
                'phone'      => $data['phone'] ?? null,
                'email'      => $data['email'] ?? null,
                'leader'     => $data['leader'] ?? 0,
                'professor'  => $data['professor'] ?? 0,
                // Tug'ilgan sana bosh sahifadagi tabrik bloki uchun kerak
                // (GET /birthdays). Haqiqiy sanalar admin paneldan kiritiladi.
                'birthday'   => $this->birthdayFor($data['slug']),
                'deleted_at' => null,
            ]);

            EmployMeta::withTrashed()->updateOrCreate(['slug' => $data['slug']], [
                'employ_id'       => $employ->id,
                'department_id'   => $department->id,
                'position_id'     => $data['position'],
                'employ_staff_id' => 1,
                'employ_form_id'  => 1,
                'employ_type_id'  => 1,
                'active'          => 1,
                'deleted_at'      => null,
            ]);
        }
    }

    /** Uch tilli maydon uchun yordamchi. */
    private function tr(string $uz, ?string $ru = null, ?string $en = null): array
    {
        return ['uz' => $uz, 'ru' => $ru ?? $uz, 'en' => $en ?? $uz];
    }
}
