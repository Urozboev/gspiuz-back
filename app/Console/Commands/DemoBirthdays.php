<?php

namespace App\Console\Commands;

use App\Models\Employ;
use Illuminate\Console\Command;

/**
 * Tug'ilgan kunlar blokini sinash uchun vaqtinchalik ma'lumot.
 *
 * Bir kunda bir nechta xodimning tug'ilgan kuni bo'lishi mumkin, va bosh
 * sahifadagi blok shuni to'g'ri ko'rsatishi kerak. Odatiy holatda bunday
 * kunni kutib o'tirmaslik uchun bu buyruq bir nechta xodimning tug'ilgan
 * sanasini bugungi kunga ko'chiradi — turli yosh bilan.
 *
 * Bu ishlab chiqish uchun; haqiqiy sanalar admin paneldan kiritiladi.
 *
 *   php artisan demo:birthdays          — bugungi kunga ko'chiradi
 *   php artisan demo:birthdays --reset  — asl holatiga qaytaradi
 */
class DemoBirthdays extends Command
{
    protected $signature = 'demo:birthdays
                            {--reset : Sanalarni asl holatiga qaytaradi}
                            {--count=8 : Nechta xodim}';

    protected $description = 'Tugʻilgan kunlar blokini sinash uchun sanalarni bugungi kunga koʻchiradi';

    public function handle(): int
    {
        return $this->option('reset') ? $this->reset() : $this->apply();
    }

    private function apply(): int
    {
        $count = max(1, min((int) $this->option('count'), 15));

        $employees = Employ::orderBy('id')->take($count)->get();

        if ($employees->isEmpty()) {
            $this->warn('Xodimlar topilmadi. Avval GspiStaffSeeder ni ishga tushiring.');

            return self::FAILURE;
        }

        $today = now();

        foreach ($employees as $index => $employ) {
            // Har biriga boshqa yosh — blok yoshni to'g'ri hisoblashini ko'rish
            // uchun. Ro'yxat rahbariyatdan boshlanadi, shuning uchun yosh
            // kamayib boradi: rektor eng katta, tyutorlar yoshroq.
            $age = 62 - $index * 4;

            $employ->forceFill([
                'birthday' => $today->copy()->subYears($age)->format('Y-m-d'),
            ])->save();

            $this->line(sprintf('  %-34s %d yosh', $employ->slug, $age));
        }

        // Bittasi rasmsiz — frontend shu holatni ham chizishi kerak.
        $withoutPhoto = $employees->last();

        if ($withoutPhoto->photo) {
            $withoutPhoto->forceFill(['photo' => null])->save();
            $this->line('  ' . $withoutPhoto->slug . ' — rasmi olib tashlandi');
        }

        $this->info($employees->count() . ' ta xodimning tugʻilgan kuni bugunga koʻchirildi.');
        $this->line('Qaytarish uchun: php artisan demo:birthdays --reset');

        return self::SUCCESS;
    }

    /**
     * Asl holat — GspiStaffSeeder dagi bilan bir xil qoida:
     * sana slug'dan barqaror hosil qilinadi.
     */
    private function reset(): int
    {
        $restored = 0;

        foreach (Employ::orderBy('id')->get() as $employ) {
            $hash = crc32($employ->slug);

            $employ->forceFill([
                'birthday' => sprintf(
                    '%04d-%02d-%02d',
                    1965 + ($hash % 30),
                    1 + ($hash % 12),
                    1 + ($hash % 28)
                ),
            ])->save();

            $restored++;
        }

        $this->info($restored . ' ta xodimning sanasi asl holatiga qaytarildi.');
        $this->line('Rasmlarni tiklash uchun: php artisan db:seed --class=GspiPortraitSeeder');

        return self::SUCCESS;
    }
}
