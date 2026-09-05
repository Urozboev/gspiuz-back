<?php

namespace App\Console\Commands;

use App\Models\Employ;
use App\Models\Post;
use App\Models\PostsCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Hozirgi gspi.uz saytining API'sidan kontentni koʻchiradi.
 *
 * Kirish maʼlumoti (Basic auth) **hech qayerga yozilmaydi**: buyruq uni
 * soʻraydi, faqat shu ishga ishlatadi. Uni `.env` ga qoʻymang va buyruq
 * qatoriga yozmang — terminal tarixida qolib ketadi.
 *
 *   php artisan gspi:import news              — nima koʻchishini koʻrsatadi
 *   php artisan gspi:import news --apply      — haqiqatan yozadi
 *
 * Buyruq **idempotent**: bir marta koʻchirilgan yozuv qayta yozilmaydi,
 * shuning uchun uzilib qolsa yana ishga tushiraverish mumkin.
 */
class GspiImport extends Command
{
    protected $signature = 'gspi:import
        {what : news | announcements | employees}
        {--limit=40 : Nechta yozuv koʻchirilsin (eng yangisidan)}
        {--apply : Bazaga haqiqatan yozish}';

    protected $description = 'gspi.uz API sidan kontentni koʻchiradi';

    private const BASE = 'https://api.gspi.uz/api';

    private string $auth = '';

    public function handle(): int
    {
        $what = (string) $this->argument('what');

        if (!in_array($what, ['news', 'announcements', 'employees'], true)) {
            $this->error('  news, announcements yoki employees boʻlishi kerak.');

            return self::FAILURE;
        }

        if (!$this->authenticate()) {
            return self::FAILURE;
        }

        // Manbada xodimlar `departments` deb atalgan.
        $rows = $this->fetch($what === 'employees' ? 'departments' : $what);

        if ($rows === null) {
            return self::FAILURE;
        }

        $this->info(sprintf('  Manbada %d ta yozuv topildi.', count($rows)));

        return match ($what) {
            'employees' => $this->importEmployees($rows),
            default => $this->importPosts($rows, $what),
        };
    }

    /** Kirish maʼlumotini soʻraydi va bitta soʻrov bilan tekshiradi. */
    private function authenticate(): bool
    {
        $this->newLine();
        $this->line('  gspi.uz API kirish kaliti (Basic auth qiymati).');
        $this->line('  <fg=gray>Kiritilgan matn ekranda koʻrinmaydi va hech qayerga saqlanmaydi.</>');

        $this->auth = trim((string) $this->secret('  Kalit'));

        if ($this->auth === '') {
            $this->error('  Kalit kiritilmadi.');

            return false;
        }

        // Foydalanuvchi "Basic xxx" koʻrinishida ham kiritishi mumkin.
        $this->auth = (string) preg_replace('~^Basic\s+~i', '', $this->auth);

        if ($this->request('menus') === null) {
            $this->error('  Kalit qabul qilinmadi yoki API javob bermadi.');

            return false;
        }

        $this->info('  Kalit qabul qilindi.');

        return true;
    }

    private function request(string $path): ?array
    {
        try {
            $response = Http::withHeaders(['Authorization' => 'Basic ' . $this->auth])
                ->timeout(60)
                ->acceptJson()
                ->get(self::BASE . '/' . $path);
        } catch (\Throwable $e) {
            $this->error('  Ulanmadi: ' . $e->getMessage());

            return null;
        }

        if (!$response->successful()) {
            $this->error(sprintf('  %s → HTTP %d', $path, $response->status()));

            return null;
        }

        return $response->json();
    }

    /** Javob `data` ichida ham, toʻgʻridan-toʻgʻri massiv ham boʻlishi mumkin. */
    private function fetch(string $path): ?array
    {
        $json = $this->request($path);

        if ($json === null) {
            return null;
        }

        $rows = $json['data'] ?? $json;

        return is_array($rows) ? array_values($rows) : null;
    }

    /** HTML entity'larni ochadi: `&#39;` → `'`. */
    private function clean(?string $value): string
    {
        return trim(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /** Uch tilli maydonni yigʻadi; boʻsh tarjima oʻzbekchaga tushadi. */
    private function trio(array $row, string $prefix): array
    {
        $uz = $this->clean($row[$prefix . '_uz'] ?? null);

        return [
            'uz' => $uz,
            'ru' => $this->clean($row[$prefix . '_ru'] ?? null) ?: $uz,
            'en' => $this->clean($row[$prefix . '_en'] ?? null) ?: $uz,
        ];
    }

    private function uniqueSlug(string $title, string $table): string
    {
        $base = Str::slug($title) ?: 'yozuv';
        $slug = $base;
        $i = 2;

        while (DB::table($table)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    private function importPosts(array $rows, string $what): int
    {
        // Eng yangisidan boshlaymiz.
        usort($rows, fn ($a, $b) => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));

        $rows = array_slice($rows, 0, max(1, (int) $this->option('limit')));

        $new = [];
        $skipped = 0;

        foreach ($rows as $row) {
            $title = $this->trio($row, 'title');

            if ($title['uz'] === '') {
                continue;
            }

            // Manbadagi slug boʻlsa oʻshani ishlatamiz — takror import
            // qilinganda ayni yozuv qayta yozilmasligi shunga bogʻliq.
            $slug = Str::slug($this->clean($row['slug'] ?? '')) ?: Str::slug($title['uz']);

            if ($slug !== '' && Post::where('slug', $slug)->exists()) {
                $skipped++;

                continue;
            }

            $new[] = [
                'title' => $title,
                'desc' => $this->trio($row, 'body'),
                'slug' => $slug !== '' ? $slug : $this->uniqueSlug($title['uz'], 'posts'),
                'date' => substr((string) ($row['created_at'] ?? now()->toDateString()), 0, 10),
                'views_count' => (int) ($row['view'] ?? 0),
            ];
        }

        $this->report($new, $skipped, fn ($r) => [mb_substr($r['title']['uz'], 0, 52), $r['date']]);

        if (!$this->option('apply') || $new === []) {
            return self::SUCCESS;
        }

        $category = $this->categoryFor($what);

        $bar = $this->output->createProgressBar(count($new));

        foreach ($new as $row) {
            $post = Post::create([
                'title' => $row['title'],
                'subtitle' => ['uz' => '', 'ru' => '', 'en' => ''],
                'desc' => $row['desc'],
                'slug' => $row['slug'],
                'date' => $row['date'],
                'views_count' => $row['views_count'],
            ]);

            if ($category) {
                $post->postsCategories()->syncWithoutDetaching([$category->id]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info(sprintf('  %d ta yozuv qoʻshildi.', count($new)));
        $this->line('  <fg=gray>Rasmlar koʻchirilmadi — manzil naqshi hali nomaʼlum.</>');

        return self::SUCCESS;
    }

    /**
     * Yozuv qaysi turkumga tushishi.
     *
     * Frontend `/news?category=<slug>` orqali filtrlaydi, shuning uchun
     * turkum biriktirilmasa eʼlonlar eʼlonlar sahifasida koʻrinmaydi.
     */
    private function categoryFor(string $what): ?PostsCategory
    {
        $known = [
            'news' => ['yangiliklar', ['uz' => 'Yangiliklar', 'ru' => 'Новости', 'en' => 'News']],
            'announcements' => ['elonlar', ['uz' => 'Eʼlonlar', 'ru' => 'Объявления', 'en' => 'Announcements']],
        ];

        if (!isset($known[$what])) {
            return null;
        }

        [$slug, $title] = $known[$what];

        return PostsCategory::firstOrCreate(['slug' => $slug], ['title' => $title]);
    }

    private function importEmployees(array $rows): int
    {
        $new = [];
        $skipped = 0;

        foreach ($rows as $row) {
            $name = $this->clean($row['name_uz'] ?? '');

            if ($name === '') {
                continue;
            }

            // "Qalandarov Aziz Abdukayumovich" → familiya, ism, otasining ismi
            $parts = preg_split('~\s+~', $name, 3) ?: [];

            $lastName = $parts[0] ?? '';
            $firstName = $parts[1] ?? '';
            $surname = $parts[2] ?? '';

            $slug = Str::slug($name);

            if ($slug !== '' && Employ::where('slug', $slug)->exists()) {
                $skipped++;

                continue;
            }

            $new[] = [
                'first_name' => $this->sameInAllLangs($firstName),
                'last_name' => $this->sameInAllLangs($lastName),
                'surname' => $this->sameInAllLangs($surname),
                'position' => $this->trio($row, 'title'),
                'work_time' => $this->trio($row, 'soha'),
                'dec' => $this->trio($row, 'body'),
                'email' => $this->clean($row['email'] ?? null) ?: null,
                'phone' => $this->clean($row['tel'] ?? null) ?: null,
                'slug' => $slug !== '' ? $slug : $this->uniqueSlug($name, 'employs'),
                'display' => $name,
            ];
        }

        $this->report($new, $skipped, fn ($r) => [
            mb_substr($r['display'], 0, 38),
            mb_substr(strip_tags($r['position']['uz']), 0, 40),
        ]);

        if (!$this->option('apply') || $new === []) {
            return self::SUCCESS;
        }

        $this->newLine();
        $this->warn('  Diqqat: xodimlar boʻlimga bogʻlanmaydi.');
        $this->line('  Manbada faqat `tuzilma_id` bor, boʻlim nomlari yoʻq. Bogʻlanish');
        $this->line('  admin paneldan qoʻlda kiritiladi yoki nomlar aniqlangach qoʻshiladi.');
        $this->newLine();

        if (!$this->confirm('Shunga qaramay koʻchirilsinmi?', false)) {
            $this->line('  Bekor qilindi.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar(count($new));

        foreach ($new as $row) {
            Employ::create([
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

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info(sprintf('  %d ta xodim qoʻshildi.', count($new)));
        $this->line('  <fg=gray>Rasmlar va boʻlim bogʻlanishi keyin qoʻshiladi.</>');
        $this->newLine();
        $this->warn('  Kontaktlarni tekshirib chiqing.');
        $this->line('  Manbadagi telefon va email har doim ham toʻgʻri emas — bittasi');
        $this->line('  allaqachon xato ekani aniqlangan. Admin paneldan koʻrib chiqing.');

        return self::SUCCESS;
    }

    /** Ism-familiya tarjima qilinmaydi — uch tilda ham bir xil. */
    private function sameInAllLangs(string $value): array
    {
        return ['uz' => $value, 'ru' => $value, 'en' => $value];
    }

    /** Koʻchiriladigan yozuvlarni yozishdan oldin koʻrsatadi. */
    private function report(array $new, int $skipped, callable $columns): void
    {
        $this->newLine();

        if ($skipped > 0) {
            $this->line(sprintf('  <fg=gray>%d ta yozuv allaqachon bazada bor — oʻtkazib yuborildi.</>', $skipped));
        }

        if ($new === []) {
            $this->info('  Yangi yozuv yoʻq.');

            return;
        }

        $this->table(['Nomi', 'Qoʻshimcha'], array_map($columns, array_slice($new, 0, 10)));

        if (count($new) > 10) {
            $this->line(sprintf('  … va yana %d ta.', count($new) - 10));
        }

        if (!$this->option('apply')) {
            $this->newLine();
            $this->line('  Bu faqat roʻyxat. Koʻchirish uchun <fg=yellow>--apply</> qoʻshing.');
        }
    }
}
