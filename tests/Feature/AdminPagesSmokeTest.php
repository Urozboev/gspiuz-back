<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Admin paneldagi parametrsiz sahifalar ochilishini tekshiradi.
 *
 * Bir necha o'nlab blade fayli bir vaqtda tahrirlanganda (masalan matnlar
 * o'zbekchaga o'girilganda) buzilgan sahifa shu yerda darrov ko'rinadi.
 */
class AdminPagesSmokeTest extends TestCase
{
    public function test_every_parameterless_admin_page_opens(): void
    {
        $admin = User::where('role', 'admin')->orderBy('id')->firstOrFail();

        $urls = collect(Route::getRoutes())
            ->filter(fn ($route) => in_array('GET', $route->methods(), true))
            ->map(fn ($route) => $route->uri())
            ->filter(fn ($uri) => str_starts_with($uri, 'admin'))
            // Parametrli marshrutlar uchun mos yozuv kerak — ular bu yerda emas.
            ->reject(fn ($uri) => str_contains($uri, '{'))
            ->unique()
            ->values();

        $this->assertGreaterThan(20, $urls->count(), 'Admin marshrutlari topilmadi');

        $broken = [];

        foreach ($urls as $uri) {
            $response = $this->actingAs($admin)->get('/' . $uri);

            // 200 — sahifa, 302 — boshqa sahifaga yo'naltirish (masalan chiqish).
            if (!in_array($response->status(), [200, 302], true)) {
                $broken[] = $uri . ' -> ' . $response->status();
            }
        }

        $this->assertSame([], $broken, "Ochilmagan sahifalar:\n" . implode("\n", $broken));
    }
}
