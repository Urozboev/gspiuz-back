<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * API manzilini prefiks bilan quradi.
     *
     * Prefiks maxfiy va `.env` dan oʻqiladi, shuning uchun testlarda ham
     * qattiq yozilmaydi — aks holda repozitoriyga tushib qolardi.
     *
     *   $this->apiUrl('/news')  →  /<prefiks>/news
     */
    protected function apiUrl(string $path): string
    {
        return '/' . trim((string) config('api.prefix'), '/') . '/' . ltrim($path, '/');
    }
}
