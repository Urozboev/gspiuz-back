<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bazadagi namunaviy (seeder bilan qoʻyilgan) maʼlumotni koʻrsatadi va
 * kerak boʻlsa tozalaydi.
 *
 * Nima uchun kerak: sayt toʻldirilayotganda seederlar orqali oʻrinbosar
 * kontent qoʻyilgan — xodim ismlari, telefonlar, bank rekvizitlari.
 * Ular haqiqiy emas. Rasmiy saytga chiqishdan oldin ular albatta
 * haqiqiy maʼlumot bilan almashtirilishi yoki oʻchirilishi shart.
 *
 *   php artisan demo:audit            — faqat roʻyxat (hech narsa oʻzgarmaydi)
 *   php artisan demo:audit --clean    — tasdiqlashdan soʻng oʻchiradi
 */
class DemoAudit extends Command
{
    protected $signature = 'demo:audit
        {--clean : Namunaviy yozuvlarni oʻchirish}
        {--only= : Faqat shu turlar (vergul bilan): employs, posts, works, events…}';

    protected $description = 'Namunaviy maʼlumotni koʻrsatadi va tozalaydi';

    /**
     * Xavf darajasi boʻyicha guruhlangan jadvallar.
     *
     * `critical` — notoʻgʻri boʻlsa real zarar keltiradigan maʼlumot.
     */
    private const GROUPS = [
        'critical' => [
            'employs' => 'Xodimlar (ism, telefon, email, qabul kunlari)',
        ],
        'content' => [
            'posts' => 'Yangiliklar',
            'documents' => 'Hujjatlar',
            'members' => 'Jurnallar / aʼzolar',
            'services' => 'Xizmatlar / savol-javob',
            'works' => 'Fotoalbomlar',
            'events' => 'Tadbirlar',
            'reks' => 'Bannerlar va modal xabarlar',
            'educational_programs' => 'Taʼlim dasturlari',
        ],
        'structure' => [
            'departments' => 'Boʻlim, fakultet, kafedralar',
            'partners' => 'Hamkorlar',
        ],
    ];

    /**
     * Bogʻliq jadvallar: asosiy yozuv oʻchsa, bular ham ketishi kerak,
     * aks holda bazada egasiz qatorlar qolib ketadi.
     */
    private const DEPENDENTS = [
        'employs' => ['employ_metas'],
        'posts' => ['post_images', 'posts_category_post'],
        'works' => ['work_images'],
    ];

    /** Rekvizitlar — alohida, chunki bular ustun, jadval emas. */
    private const REQUISITES = [
        'bank_account' => 'Hisob raqami',
        'treasury_account' => 'Gʻaznachilik hisobi',
        'mfo' => 'MFO',
        'inn' => 'STIR (INN)',
        'oked' => 'OKED',
        'bank_name' => 'Bank nomi',
    ];

    public function handle(): int
    {
        $this->newLine();
        $this->warn('  Bazadagi namunaviy maʼlumot');
        $this->newLine();

        $rows = [];

        foreach (self::GROUPS as $level => $tables) {
            foreach ($tables as $table => $label) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                $count = DB::table($table)->count();

                if ($count > 0) {
                    $rows[] = [$this->mark($level), $label, $count];
                }
            }
        }

        $this->table(['Xavf', 'Nima', 'Soni'], $rows);

        $this->showRequisites();

        if (!$this->option('clean')) {
            $this->newLine();
            $this->line('  Oʻchirish uchun: <fg=yellow>php artisan demo:audit --clean</>');
            $this->newLine();

            return self::SUCCESS;
        }

        return $this->clean();
    }

    private function mark(string $level): string
    {
        return match ($level) {
            'critical' => '<fg=red>XAVFLI</>',
            'content' => '<fg=yellow>kontent</>',
            default => 'tuzilma',
        };
    }

    /** Bank rekvizitlari — eng xavfli qism, alohida koʻrsatiladi. */
    private function showRequisites(): void
    {
        if (!Schema::hasTable('site_infos')) {
            return;
        }

        $info = DB::table('site_infos')->first();

        if (!$info) {
            return;
        }

        $filled = [];

        foreach (self::REQUISITES as $column => $label) {
            $value = $info->{$column} ?? null;

            if ($value !== null && $value !== '') {
                $filled[] = [$label, mb_substr((string) $value, 0, 48)];
            }
        }

        if ($filled === []) {
            return;
        }

        $this->newLine();
        $this->error('  Bank rekvizitlari toʻldirilgan — bular pul oʻtkazishda ishlatiladi:');
        $this->table(['Maydon', 'Qiymat'], $filled);
        $this->line('  Haqiqiyligini buxgalteriyadan tasdiqlatmaguningizcha saytga chiqarmang.');
    }

    /**
     * Oʻchiriladigan jadvallar roʻyxati.
     *
     * `--only` berilsa faqat oʻshalar; aks holda xavfli va kontent
     * guruhlari toʻliq. Tuzilma (boʻlim, hamkor) hech qachon kirmaydi.
     */
    private function tablesToClean(): array
    {
        $all = array_merge(
            array_keys(self::GROUPS['critical']),
            array_keys(self::GROUPS['content'])
        );

        $only = trim((string) $this->option('only'));

        if ($only === '') {
            return $all;
        }

        $wanted = array_filter(array_map('trim', explode(',', $only)));
        $picked = array_values(array_intersect($all, $wanted));
        $unknown = array_diff($wanted, $all);

        if ($unknown !== []) {
            $this->error('  Notanish tur: ' . implode(', ', $unknown));
            $this->line('  Mumkin boʻlganlari: ' . implode(', ', $all));

            return [];
        }

        return $picked;
    }

    private function clean(): int
    {
        $tables = $this->tablesToClean();

        if ($tables === []) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->line('  Oʻchiriladi: <fg=yellow>' . implode(', ', $tables) . '</>');

        // `--only` berilganda rekvizitlarga tegilmaydi — foydalanuvchi
        // aniq nimani soʻragan boʻlsa, faqat oʻsha bajariladi.
        $withRequisites = trim((string) $this->option('only')) === '';

        if ($withRequisites) {
            $this->line('  Bank rekvizitlari ham tozalanadi.');
        }

        $this->newLine();

        if (!$this->confirm('Davom etilsinmi?', false)) {
            $this->line('  Bekor qilindi — hech narsa oʻzgarmadi.');

            return self::SUCCESS;
        }

        // Tuzilma (boʻlim, hamkor) qoldiriladi: menyular va sahifalar ularga
        // bogʻlangan, oʻchirilsa sayt navigatsiyasi buziladi.
        DB::transaction(function () use ($tables, $withRequisites) {
            Schema::disableForeignKeyConstraints();

            foreach ($tables as $table) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                foreach (self::DEPENDENTS[$table] ?? [] as $child) {
                    if (Schema::hasTable($child)) {
                        $removed = DB::table($child)->delete();
                        $this->line(sprintf('  %-24s %d ta oʻchirildi', $child, $removed));
                    }
                }

                $deleted = DB::table($table)->delete();
                $this->line(sprintf('  %-24s %d ta oʻchirildi', $table, $deleted));
            }

            Schema::enableForeignKeyConstraints();

            if (!$withRequisites) {
                return;
            }

            if (Schema::hasTable('site_infos')) {
                $blank = [];

                foreach (array_keys(self::REQUISITES) as $column) {
                    if (Schema::hasColumn('site_infos', $column)) {
                        $blank[$column] = null;
                    }
                }

                if ($blank !== []) {
                    DB::table('site_infos')->update($blank);
                    $this->line('  bank rekvizitlari      tozalandi');
                }
            }
        });

        $this->newLine();
        $this->info('  Tayyor. Boʻlimlar va menyular saqlab qolindi.');
        $this->line('  Sayt boʻsh roʻyxatlarda ham ishlaydi — sahifalar buzilmaydi.');
        $this->newLine();

        return self::SUCCESS;
    }
}
