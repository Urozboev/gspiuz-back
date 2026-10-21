<?php

namespace App\Console\Commands;

use App\Models\FormMenu;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Sahifa saqlanganda xato bilan oʻchib ketgan elementlarni qaytaradi.
 *
 * Xato: sahifa admin paneldan saqlanganda `DynamicMenuController::update()`
 * uning kartochka, fayl va bloklarini oʻchirardi (tuzatilgan, qarang:
 * PageEditKeepsContentTest). Elementlar `SoftDeletes` bilan oʻchirilgani
 * uchun bazada qolgan — faqat `deleted_at` belgilangan.
 *
 * Qaysilar xato bilan oʻchganini qanday bilamiz: xato ularni sahifa
 * yangilangan **aynan oʻsha soniyada** oʻchirgan. Foydalanuvchi
 * elementlar ekranidan ataylab oʻchirgan yozuvlarda bunday moslik
 * boʻlmaydi, shuning uchun ularga tegilmaydi.
 *
 *   php artisan pages:restore-lost          — faqat roʻyxat
 *   php artisan pages:restore-lost --apply  — tasdiqlashdan soʻng qaytaradi
 */
class RestoreLostPageItems extends Command
{
    protected $signature = 'pages:restore-lost
        {--apply : Topilganlarni qaytarish}
        {--window=5 : Sahifa yangilanishi bilan oʻchirish orasidagi ruxsat etilgan farq (soniya)}';

    protected $description = 'Sahifa saqlanganda xato bilan oʻchgan elementlarni qaytaradi';

    public function handle(): int
    {
        $window = max(0, (int) $this->option('window'));

        $rows = DB::table('form_menus as f')
            ->join('dinamik_menus as d', 'd.id', '=', 'f.dinamik_menu_id')
            ->leftJoin('menus as m', 'm.id', '=', 'd.menu_id')
            ->whereNotNull('f.deleted_at')
            ->whereRaw('ABS(TIMESTAMPDIFF(SECOND, f.deleted_at, d.updated_at)) <= ?', [$window])
            ->orderBy('f.dinamik_menu_id')
            ->orderBy('f.id')
            ->get(['f.id', 'f.title', 'f.group', 'f.type', 'f.deleted_at', 'm.slug as page_slug']);

        if ($rows->isEmpty()) {
            $this->info('  Xato bilan oʻchgan element topilmadi.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->warn(sprintf('  Sahifa saqlanganda oʻchib ketgan %d ta element:', $rows->count()));
        $this->newLine();

        $this->table(
            ['Sahifa', 'Element', 'Blok', 'Oʻchirilgan'],
            $rows->map(fn ($row) => [
                $row->page_slug ?? '—',
                mb_substr($this->title($row->title), 0, 40),
                $row->group ?? $row->type ?? '—',
                $row->deleted_at,
            ])->all()
        );

        $total = DB::table('form_menus')->whereNotNull('deleted_at')->count();

        if ($total > $rows->count()) {
            $this->line(sprintf(
                '  <fg=gray>Yana %d ta oʻchirilgan element bor — ular foydalanuvchi tomonidan alohida oʻchirilgan, tegilmaydi.</>',
                $total - $rows->count()
            ));
        }

        if (!$this->option('apply')) {
            $this->newLine();
            $this->line('  Qaytarish uchun: <fg=yellow>php artisan pages:restore-lost --apply</>');
            $this->newLine();

            return self::SUCCESS;
        }

        if (!$this->confirm('Shu elementlar qaytarilsinmi?', false)) {
            $this->line('  Bekor qilindi.');

            return self::SUCCESS;
        }

        $restored = FormMenu::onlyTrashed()->whereIn('id', $rows->pluck('id'))->restore();

        $this->info(sprintf('  %d ta element qaytarildi.', $restored));

        return self::SUCCESS;
    }

    private function title(?string $raw): string
    {
        $decoded = json_decode((string) $raw, true);

        if (is_array($decoded)) {
            return (string) ($decoded['uz'] ?? reset($decoded) ?: '—');
        }

        return (string) ($raw ?: '—');
    }
}
