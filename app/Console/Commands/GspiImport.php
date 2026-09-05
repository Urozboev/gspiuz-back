<?php

namespace App\Console\Commands;

use App\Models\Employ;
use App\Models\EmployMeta;
use App\Models\Post;
use App\Models\PostsCategory;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Eski gspi.uz bazasidan kontentni koʻchiradi.
 *
 * Manba — `legacy` ulanishi (`.env` dagi `LEGACY_DB_DATABASE`). Eski
 * saytning dump'i mahalliy bazaga yuklanadi, buyruq oʻshandan oʻqiydi.
 *
 *   php artisan gspi:import all             — nima koʻchishini koʻrsatadi
 *   php artisan gspi:import all --apply     — haqiqatan koʻchiradi
 *   php artisan gspi:import news --limit=5  — faqat yangiliklar
 *
 * Buyruq **idempotent**: bir marta koʻchirilgan yozuv qayta qoʻshilmaydi.
 */
class GspiImport extends Command
{
    protected $signature = 'gspi:import
        {what=all : all | departments | employees | pages | news | announcements | requisites}
        {--limit=5 : Yangilik va eʼlonlardan nechtasi (eng yangisidan)}
        {--apply : Bazaga haqiqatan yozish}';

    protected $description = 'Eski gspi.uz bazasidan kontentni koʻchiradi';

    private const TYPE_LEADERSHIP = 1;
    private const TYPE_DEPARTMENT = 2;
    private const TYPE_FACULTY = 3;
    private const TYPE_KAFEDRA = 4;
    private const TYPE_CENTER = 5;

    /** `looksLikePage()` natijasi — har tuzilma uchun bir marta hisoblanadi. */
    private array $pageCache = [];

    public function handle(): int
    {
        $what = (string) $this->argument('what');

        $known = ['all', 'departments', 'employees', 'pages', 'news', 'announcements', 'requisites'];

        if (!in_array($what, $known, true)) {
            $this->error('  Mumkin boʻlganlari: ' . implode(', ', $known));

            return self::FAILURE;
        }

        if (!$this->legacyIsReachable()) {
            return self::FAILURE;
        }

        if (!$this->option('apply')) {
            $this->newLine();
            $this->warn('  Bu faqat roʻyxat — bazaga hech narsa yozilmaydi.');
        }

        $steps = $what === 'all'
            ? ['departments', 'employees', 'pages', 'news', 'announcements', 'requisites']
            : [$what];

        foreach ($steps as $step) {
            $this->newLine();

            match ($step) {
                'departments' => $this->importDepartments(),
                'employees' => $this->importEmployees(),
                'pages' => $this->importPages(),
                'news' => $this->importPosts('yangiliklars', 'yangiliklar', 'Yangiliklar'),
                'announcements' => $this->importPosts('elonlars', 'elonlar', 'Eʼlonlar'),
                'requisites' => $this->showRequisites(),
            };
        }

        if (!$this->option('apply')) {
            $this->newLine();
            $this->line('  Koʻchirish uchun <fg=yellow>--apply</> qoʻshing.');
        }

        $this->newLine();

        return self::SUCCESS;
    }

    private function legacy(string $table): Builder
    {
        return DB::connection('legacy')->table($table);
    }

    private function legacyIsReachable(): bool
    {
        try {
            $this->legacy('tuzilmas')->count();
        } catch (\Throwable $e) {
            $this->error('  Eski bazaga ulanib boʻlmadi.');
            $this->line('  `.env` dagi `LEGACY_DB_DATABASE` ni tekshiring.');
            $this->line('  <fg=gray>' . $e->getMessage() . '</>');

            return false;
        }

        return true;
    }

    /** HTML entity'larni ochadi va boʻsh joylarni tozalaydi. */
    private function clean(?string $value): string
    {
        $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('~\s+~u', ' ', $value) ?? '');
    }

    /** Uch tilli maydon; boʻsh tarjima oʻzbekchaga tushadi. */
    private function trio(object $row, string $prefix, bool $keepHtml = false): array
    {
        $take = function (string $lang) use ($row, $prefix, $keepHtml): string {
            $raw = (string) ($row->{$prefix . '_' . $lang} ?? '');

            return $keepHtml
                ? trim(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'))
                : $this->clean($raw);
        };

        $uz = $take('uz');

        return [
            'uz' => $uz,
            'ru' => $take('ru') ?: $uz,
            'en' => $take('en') ?: $uz,
        ];
    }

    private function uniqueSlug(string $base, string $table): string
    {
        $slug = Str::slug($base) ?: 'yozuv';
        $candidate = $slug;
        $i = 2;

        while (DB::table($table)->where('slug', $candidate)->exists()) {
            $candidate = $slug . '-' . $i++;
        }

        return $candidate;
    }

    // ------------------------------------------------------------------
    // Boʻlimlar
    // ------------------------------------------------------------------

    /**
     * Eski `tuzilmas` dan boʻlimlarni koʻchiradi.
     *
     * Faqat haqiqiy xodimi bor tuzilmalar olinadi: qolganlari
     * ("Rekvizitlar", "Resurslar", "Yashil Institut") aslida boʻlim
     * emas, sahifa mavzusi — eski saytda ular ham shu jadvalda yotadi.
     */
    private function importDepartments(): void
    {
        $this->line('<fg=cyan>  Boʻlimlar</>');

        $ids = $this->legacy('kafedras')->distinct()->pluck('tuzilma_id')->all();

        $rows = $this->legacy('tuzilmas')->whereIn('id', $ids)->orderBy('id')->get();

        $new = [];
        $skipped = 0;

        foreach ($rows as $row) {
            $name = $this->trio($row, 'name');

            if ($name['uz'] === '' || $this->looksLikePage((int) $row->id)) {
                continue;
            }

            $slug = Str::slug($row->slug ?: $name['uz']);

            if (DB::table('departments')->where('slug', $slug)->exists()) {
                $skipped++;

                continue;
            }

            $new[] = [
                'name' => $name,
                'slug' => $slug,
                'type' => $this->departmentType($name['uz']),
            ];
        }

        $this->summary($new, $skipped, fn ($r) => [
            mb_substr($r['name']['uz'], 0, 48),
            $this->typeLabel($r['type']),
        ]);

        if (!$this->option('apply') || $new === []) {
            return;
        }

        foreach ($new as $row) {
            DB::table('departments')->insert([
                'name' => json_encode($row['name'], JSON_UNESCAPED_UNICODE),
                'slug' => $row['slug'],
                'structure_type_id' => $row['type'],
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info(sprintf('  %d ta boʻlim qoʻshildi.', count($new)));
    }

    /**
     * Tuzilma aslida sahifa mavzusimi?
     *
     * Eski bazada "Rekvizitlar" kabi sahifalar ham `kafedras` jadvalida
     * yotadi: yozuvning "ismi" tuzilma nomining oʻzi boʻladi. Kamida
     * bitta yozuv boshqacha atalgan boʻlsa — bu haqiqiy boʻlim.
     */
    private function looksLikePage(int $tuzilmaId): bool
    {
        if (array_key_exists($tuzilmaId, $this->pageCache)) {
            return $this->pageCache[$tuzilmaId];
        }

        $structure = $this->legacy('tuzilmas')->where('id', $tuzilmaId)->first();
        $people = $this->legacy('kafedras')->where('tuzilma_id', $tuzilmaId)->get(['name_uz']);

        if (!$structure || $people->isEmpty()) {
            return $this->pageCache[$tuzilmaId] = true;
        }

        $structureName = Str::slug($this->clean($structure->name_uz));

        foreach ($people as $person) {
            if (Str::slug($this->clean($person->name_uz)) !== $structureName) {
                return $this->pageCache[$tuzilmaId] = false;
            }
        }

        return $this->pageCache[$tuzilmaId] = true;
    }

    private function departmentType(string $name): int
    {
        $lower = mb_strtolower($name);

        return match (true) {
            str_contains($lower, 'rektor') => self::TYPE_LEADERSHIP,
            str_contains($lower, 'fakultet') => self::TYPE_FACULTY,
            str_contains($lower, 'kafedra') => self::TYPE_KAFEDRA,
            str_contains($lower, 'markaz') => self::TYPE_CENTER,
            default => self::TYPE_DEPARTMENT,
        };
    }

    private function typeLabel(int $type): string
    {
        return match ($type) {
            self::TYPE_LEADERSHIP => 'Rahbariyat',
            self::TYPE_FACULTY => 'Fakultet',
            self::TYPE_KAFEDRA => 'Kafedra',
            self::TYPE_CENTER => 'Markaz',
            default => 'Boʻlim',
        };
    }

    // ------------------------------------------------------------------
    // Xodimlar
    // ------------------------------------------------------------------

    private function importEmployees(): void
    {
        $this->line('<fg=cyan>  Xodimlar</>');

        $rows = $this->legacy('kafedras')->orderBy('id')->get();

        $new = [];
        $skipped = 0;
        $pages = 0;

        foreach ($rows as $row) {
            $name = $this->clean($row->name_uz);

            if ($name === '') {
                continue;
            }

            if ($this->looksLikePage((int) $row->tuzilma_id)) {
                $pages++;

                continue;
            }

            $slug = Str::slug($name);

            if ($slug !== '' && Employ::where('slug', $slug)->exists()) {
                $skipped++;

                continue;
            }

            // "Qalandarov Aziz Abdukayumovich" → familiya, ism, otasining ismi
            $parts = preg_split('~\s+~u', $name, 3) ?: [];

            $new[] = [
                'display' => $name,
                'last_name' => $this->everyLang($parts[0] ?? ''),
                'first_name' => $this->everyLang($parts[1] ?? ''),
                'surname' => $this->everyLang($parts[2] ?? ''),
                'position' => $this->trio($row, 'title', true),
                'work_time' => $this->trio($row, 'soha'),
                'dec' => $this->trio($row, 'body', true),
                'email' => $this->clean($row->email) ?: null,
                'phone' => $this->clean($row->tel) ?: null,
                'slug' => $slug !== '' ? $slug : $this->uniqueSlug($name, 'employs'),
                'department_slug' => $this->departmentSlugFor((int) $row->tuzilma_id),
                'position_id' => $this->positionFor($this->clean(strip_tags((string) $row->title_uz))),
                'has_image' => $this->clean($row->image) !== '',
            ];
        }

        if ($pages > 0) {
            $this->line(sprintf(
                '  <fg=gray>%d ta yozuv oʻtkazib yuborildi — ular xodim emas, sahifa matni.</>',
                $pages
            ));
        }

        $this->summary($new, $skipped, fn ($r) => [
            mb_substr($r['display'], 0, 36),
            mb_substr(strip_tags($r['position']['uz']), 0, 42),
        ]);

        if (!$this->option('apply') || $new === []) {
            return;
        }

        $missingDepartment = 0;

        foreach ($new as $row) {
            $employ = Employ::create([
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'surname' => $row['surname'],
                'position' => $row['position'],
                'work_time' => $row['work_time'],
                'dec' => $row['dec'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'slug' => $row['slug'],
            ]);

            $departmentId = $row['department_slug']
                ? DB::table('departments')->where('slug', $row['department_slug'])->value('id')
                : null;

            if (!$departmentId) {
                $missingDepartment++;
            }

            EmployMeta::create([
                'employ_id' => $employ->id,
                'department_id' => $departmentId,
                'position_id' => $row['position_id'],
                'employ_staff_id' => 1,
                'employ_form_id' => 1,
                'employ_type_id' => 2,
                'slug' => $this->uniqueSlug($row['slug'] . '-tayinlov', 'employ_metas'),
                'active' => 1,
                'order' => 1,
            ]);
        }

        $this->info(sprintf('  %d ta xodim qoʻshildi.', count($new)));

        if ($missingDepartment > 0) {
            $this->warn(sprintf('  %d tasi boʻlimga bogʻlanmadi — admin paneldan tanlang.', $missingDepartment));
        }

        $this->newLine();
        $this->warn('  Kontaktlarni tekshirib chiqing.');
        $this->line('  Eski saytdagi telefon va emaildan kamida bittasi xato ekani');
        $this->line('  aniqlangan. Rasmlar koʻchirilmadi — ular eski serverda qolgan.');
    }

    /**
     * Eski bazadagi erkin matnli lavozimdan `positions` jadvalidagi
     * raqamni aniqlaydi.
     *
     * Bu raqam sahifada koʻrsatilmaydi — koʻrsatiladigan matn
     * `employs.position` da saqlanadi. Raqam faqat guruhlash uchun:
     * boʻlim sahifasida kim rahbar, kim xodim ekanini shu ajratadi.
     */
    private function positionFor(string $title): int
    {
        $t = mb_strtolower($title);

        // Tartib muhim: "prorektor" ichida "rektor" bor, "dekan
        // oʻrinbosari" esa dekan emas.
        return match (true) {
            str_contains($t, 'prorektor') => 2,
            str_contains($t, 'rektor') => 1,
            str_contains($t, 'orinbosari') || str_contains($t, 'oʻrinbosari')
                || str_contains($t, 'o‘rinbosari') || str_contains($t, "o'rinbosari") => 8,
            str_contains($t, 'dekan') => 3,
            str_contains($t, 'kafedra mudiri') => 5,
            str_contains($t, 'mudiri') || str_contains($t, 'boshlig') || str_contains($t, 'raisi')
                || str_contains($t, 'direktor') || str_contains($t, 'boshqaruvchi') => 4,
            str_contains($t, 'tyutor') => 6,
            str_contains($t, 'professor') => 12,
            str_contains($t, 'dotsent') => 13,
            str_contains($t, 'katta o') => 7,
            default => 8,
        };
    }

    private function everyLang(string $value): array
    {
        return ['uz' => $value, 'ru' => $value, 'en' => $value];
    }

    private function departmentSlugFor(int $tuzilmaId): ?string
    {
        $structure = $this->legacy('tuzilmas')->where('id', $tuzilmaId)->first();

        if (!$structure) {
            return null;
        }

        return Str::slug($structure->slug ?: $this->clean($structure->name_uz)) ?: null;
    }

    // ------------------------------------------------------------------
    // Sahifa matnlari
    // ------------------------------------------------------------------

    /**
     * Eski saytda bir qancha sahifalar `kafedras` jadvalida, xodim
     * koʻrinishida saqlangan: yozuvning "ismi" — sahifa nomi, `body_uz`
     * esa sahifa matni. Ular shu yerda oʻz sahifasiga koʻchiriladi.
     *
     * Chapda eski tuzilma manzili, oʻngda bizdagi sahifa manzili.
     */
    private const PAGE_MAP = [
        'ekofaol-talabalar' => 'eco-students',
        'yashil-institut' => 'green-institute',
        'axborot-soatlari' => 'information-hours',
        'talabalar-turar-joyi' => 'dormitory',
        'resurslar' => 'e-resources',
        'rekvizitlar' => 'requisites',
        'iqtidorli-talabalar' => 'talented-students',
    ];

    private function importPages(): void
    {
        $this->line('<fg=cyan>  Sahifa matnlari</>');

        $new = [];
        $missing = [];

        foreach (self::PAGE_MAP as $legacySlug => $ourSlug) {
            $row = $this->legacy('kafedras')
                ->join('tuzilmas', 'tuzilmas.id', '=', 'kafedras.tuzilma_id')
                ->where('tuzilmas.slug', $legacySlug)
                ->select('kafedras.*')
                ->first();

            if (!$row || mb_strlen(strip_tags((string) $row->body_uz)) < 40) {
                continue;
            }

            $page = DB::table('dinamik_menus as d')
                ->join('menus as m', 'm.id', '=', 'd.menu_id')
                ->where('m.slug', $ourSlug)
                ->select('d.id', 'd.text')
                ->first();

            if (!$page) {
                $missing[] = $ourSlug;

                continue;
            }

            $existing = json_decode((string) $page->text, true);
            $hadText = mb_strlen(strip_tags((string) ($existing['uz'] ?? ''))) > 20;

            $new[] = [
                'page_id' => $page->id,
                'slug' => $ourSlug,
                'text' => $this->trio($row, 'body', true),
                'replaces' => $hadText,
            ];
        }

        if ($missing !== []) {
            $this->line('  <fg=gray>Sahifa topilmadi: ' . implode(', ', $missing) . '</>');
        }

        if ($new === []) {
            $this->line('  <fg=gray>Koʻchiriladigan matn yoʻq.</>');

            return;
        }

        $this->table(
            ['Sahifa', 'Hajmi', 'Holati'],
            array_map(fn ($r) => [
                $r['slug'],
                mb_strlen(strip_tags($r['text']['uz'])) . ' belgi',
                $r['replaces'] ? 'mavjud matn almashadi' : 'boʻsh sahifa',
            ], $new)
        );

        if (!$this->option('apply')) {
            return;
        }

        foreach ($new as $row) {
            DB::table('dinamik_menus')->where('id', $row['page_id'])->update([
                'text' => json_encode($row['text'], JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
        }

        $this->info(sprintf('  %d ta sahifa toʻldirildi.', count($new)));
    }

    // ------------------------------------------------------------------
    // Yangiliklar va eʼlonlar
    // ------------------------------------------------------------------

    private function importPosts(string $table, string $categorySlug, string $label): void
    {
        $this->line("<fg=cyan>  {$label}</>");

        $limit = max(1, (int) $this->option('limit'));

        // Talab qilinganidan koʻproq olamiz: bir qismi bazada boʻlishi
        // yoki sarlavhasiz boʻlishi mumkin.
        $rows = $this->legacy($table)->orderByDesc('id')->limit($limit * 4)->get();

        $new = [];
        $skipped = 0;

        foreach ($rows as $row) {
            if (count($new) >= $limit) {
                break;
            }

            $title = $this->trio($row, 'title');

            if ($title['uz'] === '') {
                continue;
            }

            $slug = Str::slug($this->clean($row->slug ?? '')) ?: Str::slug(mb_substr($title['uz'], 0, 80));

            if ($slug !== '' && Post::where('slug', $slug)->exists()) {
                $skipped++;

                continue;
            }

            $new[] = [
                'title' => $title,
                'desc' => $this->trio($row, 'body', true),
                'slug' => $slug !== '' ? $slug : $this->uniqueSlug($title['uz'], 'posts'),
                'date' => substr((string) ($row->created_at ?? now()->toDateString()), 0, 10),
                'views' => (int) ($row->view ?? 0),
            ];
        }

        $this->summary($new, $skipped, fn ($r) => [mb_substr($r['title']['uz'], 0, 54), $r['date']]);

        if (!$this->option('apply') || $new === []) {
            return;
        }

        $category = PostsCategory::firstOrCreate(
            ['slug' => $categorySlug],
            ['title' => ['uz' => $label, 'ru' => $label, 'en' => $label]]
        );

        foreach ($new as $row) {
            $post = Post::create([
                'title' => $row['title'],
                'subtitle' => ['uz' => '', 'ru' => '', 'en' => ''],
                'desc' => $row['desc'],
                'slug' => $row['slug'],
                'date' => $row['date'],
                'views_count' => $row['views'],
            ]);

            $post->postsCategories()->syncWithoutDetaching([$category->id]);
        }

        $this->info(sprintf('  %d ta yozuv qoʻshildi.', count($new)));
    }

    // ------------------------------------------------------------------
    // Rekvizitlar
    // ------------------------------------------------------------------

    /**
     * Rekvizitlar eski bazada sahifa matni sifatida saqlangan, alohida
     * ustunlarda emas. Shuning uchun ularni avtomatik yozmaymiz —
     * koʻrsatamiz, foydalanuvchi tasdiqlab admin panelga kiritadi.
     */
    private function showRequisites(): void
    {
        $this->line('<fg=cyan>  Rekvizitlar</>');

        $body = $this->legacy('kafedras')
            ->join('tuzilmas', 'tuzilmas.id', '=', 'kafedras.tuzilma_id')
            ->where('tuzilmas.name_uz', 'like', '%ekvizit%')
            ->value('kafedras.body_uz');

        if (!$body) {
            $this->line('  <fg=gray>Eski bazada topilmadi.</>');

            return;
        }

        $text = $this->clean(strip_tags(preg_replace('~<(br|/p|/tr|/td)[^>]*>~i', ' | ', $body) ?? ''));

        $patterns = [
            'Hisob raqami' => '~Hisob raqami:?\s*\|?\s*([\d\s]{20,30})~ui',
            'Toʻlov-kontrakt hisobi' => '~kontrakt uchun hisob raqam:?\s*\|?\s*([\d\s]{20,30})~ui',
            'MFO' => '~MFO:?\s*\|?\s*(\d{5})~ui',
            'INN (STIR)' => '~INN:?\s*\|?\s*([\d\s]{9,15})~ui',
            'OKONX (OKED)' => '~OKONX:?\s*\|?\s*(\d{5})~ui',
            'Manzil' => '~Manzil:?\s*\|?\s*(\d{6}[^|]{10,70})~ui',
        ];

        $found = [];

        foreach ($patterns as $label => $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $found[] = [$label, trim(preg_replace('~\s+~', ' ', $m[1]) ?? '')];
            }
        }

        if ($found === []) {
            $this->line('  <fg=gray>Matndan ajratib boʻlmadi.</>');

            return;
        }

        $this->table(['Maydon', 'Qiymat'], $found);
        $this->warn('  Bular avtomatik yozilmaydi.');
        $this->line('  Buxgalteriyadan tasdiqlatib, admin paneldan kiriting:');
        $this->line('  <fg=gray>Sayt maʼlumotlari → Rekvizitlar</>');
    }

    // ------------------------------------------------------------------

    /** Koʻchiriladigan yozuvlarni yozishdan oldin koʻrsatadi. */
    private function summary(array $new, int $skipped, callable $columns): void
    {
        if ($skipped > 0) {
            $this->line(sprintf('  <fg=gray>%d ta yozuv bazada bor — oʻtkazib yuborildi.</>', $skipped));
        }

        if ($new === []) {
            $this->line('  <fg=gray>Yangi yozuv yoʻq.</>');

            return;
        }

        $this->table(['Nomi', 'Qoʻshimcha'], array_map($columns, array_slice($new, 0, 12)));

        if (count($new) > 12) {
            $this->line(sprintf('  <fg=gray>… va yana %d ta.</>', count($new) - 12));
        }
    }
}
