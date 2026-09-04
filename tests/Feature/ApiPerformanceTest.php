<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * API tezligi: har bir endpoint uchun SQL soʻrovlar soni va vaqti.
 *
 * Asosiy xavf — N+1: roʻyxatdagi har bir yozuv uchun alohida soʻrov
 * yuborilishi. Bunda sahifa sekinlashadi va yozuvlar koʻpaygan sari
 * ahvol yomonlashadi, lekin ishlab chiqish paytida (5-10 yozuv bilan)
 * bu sezilmaydi.
 *
 * Test chegaralarni tekshiradi va oʻlchov jadvalini chiqaradi.
 */
class ApiPerformanceTest extends TestCase
{
    /** Bitta soʻrovga ruxsat etilgan eng koʻp SQL soʻrov. */
    private const MAX_QUERIES = 30;

    private function prefix(): string
    {
        return '/' . trim((string) config('api.prefix'), '/');
    }

    /** Endpointni chaqirib, soʻrovlar soni va vaqtini oʻlchaydi. */
    private function measure(string $path): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $started = microtime(true);
        $response = $this->withHeaders(['Accept-Language' => 'uz'])->getJson($this->prefix() . $path);
        $elapsed = (microtime(true) - $started) * 1000;

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        return [
            'status'  => $response->status(),
            'queries' => count($queries),
            'ms'      => round($elapsed),
            'log'     => $queries,
        ];
    }

    public function test_no_endpoint_runs_too_many_queries(): void
    {
        $paths = collect(Route::getRoutes())
            ->filter(fn ($route) => in_array('GET', $route->methods(), true))
            ->map(fn ($route) => $route->uri())
            ->filter(fn ($uri) => str_starts_with($uri, 'nt8xn7'))
            ->reject(fn ($uri) => str_contains($uri, '{'))
            ->map(fn ($uri) => '/' . ltrim(substr($uri, strpos($uri, '/')), '/'))
            ->unique()
            ->values();

        $this->assertGreaterThan(20, $paths->count(), 'Endpointlar topilmadi');

        $rows = [];
        $heavy = [];

        foreach ($paths as $path) {
            $result = $this->measure($path);

            if ($result['status'] !== 200) {
                continue;
            }

            $rows[] = sprintf('%-24s %3d soʻrov  %4d ms', $path, $result['queries'], $result['ms']);

            if ($result['queries'] > self::MAX_QUERIES) {
                $heavy[] = sprintf('%s — %d soʻrov', $path, $result['queries']);
            }
        }

        sort($rows);
        echo "\n" . implode("\n", $rows) . "\n";

        $this->assertSame(
            [],
            $heavy,
            "Juda koʻp SQL soʻrov yuboradigan endpointlar (chegara " . self::MAX_QUERIES . "):\n"
                . implode("\n", $heavy)
        );
    }

    /**
     * Roʻyxat endpointlari yozuvlar soniga bogʻliq boʻlmasligi kerak.
     *
     * `per_page` ikki barobar oshirilganda soʻrovlar soni ham ikki barobar
     * oshsa — bu N+1 ning aniq belgisi.
     */
    public function test_list_endpoints_do_not_scale_with_row_count(): void
    {
        $problems = [];

        foreach (['/news', '/gallery', '/journals', '/documents', '/vacancies', '/events'] as $path) {
            $small = $this->measure($path . '?per_page=3');
            $large = $this->measure($path . '?per_page=12');

            if ($small['status'] !== 200 || $large['status'] !== 200) {
                continue;
            }

            // Yozuvlar soni 4 barobar oshdi; soʻrovlar sezilarli oshmasligi kerak.
            $growth = $large['queries'] - $small['queries'];

            if ($growth > 3) {
                $problems[] = sprintf(
                    '%s — 3 yozuvda %d soʻrov, 12 yozuvda %d soʻrov (+%d)',
                    $path,
                    $small['queries'],
                    $large['queries'],
                    $growth
                );
            }
        }

        $this->assertSame([], $problems, "N+1 belgilari:\n" . implode("\n", $problems));
    }
}
