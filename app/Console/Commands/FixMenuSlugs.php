<?php

namespace App\Console\Commands;

use App\Models\Menu;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Menyu `slug` ini `path` ga moslashtiradi.
 *
 * Muammo: bazadagi bir necha menyuda `slug` va `path` bir-biriga mos
 * emas — masalan `slug=documents` boʻlgan qatorning yoʻli `/requisites`.
 * Sahifa qidiruvi (`PageController::findPage`) slug yoki path boʻyicha
 * qidirgani uchun bunday qatorlar bir-birining sahifasini qaytarib
 * yuborishi mumkin.
 *
 * Toʻgʻri manba — `path`, chunki menyu sarlavhasi ham, frontenddagi
 * havola ham oʻshanga mos. `MenusController::slugFor()` yangi menyular
 * uchun aynan shu qoidani qoʻllaydi; bu buyruq esa eski yozuvlarni
 * oʻsha qoidaga keltiradi.
 *
 *   php artisan menus:fix-slugs           — faqat roʻyxat
 *   php artisan menus:fix-slugs --apply   — tasdiqlashdan soʻng tuzatadi
 */
class FixMenuSlugs extends Command
{
    protected $signature = 'menus:fix-slugs {--apply : Topilgan nomuvofiqliklarni tuzatish}';

    protected $description = 'Menyu slug va path nomuvofiqligini topadi va tuzatadi';

    public function handle(): int
    {
        $rows = [];

        foreach (Menu::orderBy('id')->get() as $menu) {
            $expected = $this->slugFromPath($menu->path);

            if ($expected === null || $expected === $menu->slug) {
                continue;
            }

            $rows[] = [
                'id' => $menu->id,
                'title' => $this->title($menu),
                'path' => $menu->path,
                'from' => $menu->slug,
                'to' => $expected,
            ];
        }

        if ($rows === []) {
            $this->info('  Hammasi joyida — nomuvofiqlik topilmadi.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->warn('  Slug va path mos kelmayotgan menyular');
        $this->newLine();

        $this->table(
            ['id', 'Menyu', 'Yoʻl', 'Hozirgi slug', 'Boʻlishi kerak'],
            array_map(array_values(...), $rows)
        );

        $this->warnAboutDuplicates($rows);

        if (!$this->option('apply')) {
            $this->newLine();
            $this->line('  Tuzatish uchun: <fg=yellow>php artisan menus:fix-slugs --apply</>');
            $this->newLine();

            return self::SUCCESS;
        }

        if (!$this->confirm('Slug qiymatlari yuqoridagicha oʻzgartirilsinmi?', false)) {
            $this->line('  Bekor qilindi.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                DB::table('menus')->where('id', $row['id'])->update(['slug' => $row['to']]);
                $this->line("  {$row['from']} → {$row['to']}");
            }
        });

        $this->newLine();
        $this->info('  Tayyor. Sahifalar endi toʻgʻri yoʻlga bogʻlanadi.');
        $this->newLine();

        return self::SUCCESS;
    }

    /** `path` dan oxirgi boʻlakni ajratadi: `/a/b` → `b`. */
    private function slugFromPath(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '' || $path === '/' || preg_match('~^https?://~i', $path)) {
            return null;
        }

        $clean = trim((string) strtok(ltrim($path, '/'), '?'));

        if ($clean === '') {
            return null;
        }

        // Ichma-ich yoʻlga (`/news/video`) tegmaymiz: oxirgi boʻlakni
        // ajratib olsak (`video`) boshqa boʻlim bilan toʻqnashishi mumkin.
        if (str_contains($clean, '/')) {
            return null;
        }

        return $clean;
    }

    private function title(Menu $menu): string
    {
        $title = $menu->getAttributes()['title'] ?? '';
        $decoded = json_decode((string) $title, true);

        return mb_substr(is_array($decoded) ? reset($decoded) : (string) $title, 0, 30);
    }

    /**
     * Ikki menyu bitta yoʻlga qarab tursa, tuzatishdan soʻng ularning
     * slug'i ham bir xil boʻlib qoladi — buni oldindan aytib qoʻyamiz.
     */
    private function warnAboutDuplicates(array $rows): void
    {
        // Tuzatilgandan KEYIN qaysi slug qaysi menyuda boʻlishini
        // hisoblaymiz: oʻzgaradiganlar yangi qiymatni, qolganlar
        // hozirgisini oladi. Aks holda oʻzgarish arafasidagi qiymat
        // ham sanalib, yoʻq toʻqnashuv koʻrsatiladi.
        $final = DB::table('menus')->pluck('slug', 'id')->all();

        foreach ($rows as $row) {
            $final[$row['id']] = $row['to'];
        }

        $clashes = [];

        foreach (array_count_values(array_filter($final)) as $slug => $count) {
            if ($count > 1) {
                $clashes[] = $slug;
            }
        }

        if ($clashes === []) {
            return;
        }

        $this->newLine();
        $this->error('  Diqqat: quyidagi slug bir nechta menyuda takrorlanadi — ' . implode(', ', $clashes));
        $this->line('  Demak bir xil yoʻlga qarab turgan ortiqcha menyu bor.');
        $this->line('  Uni admin paneldan qoʻlda oʻchiring yoki yoʻlini oʻzgartiring.');
    }
}
