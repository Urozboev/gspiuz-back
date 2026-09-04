<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Admin paneldagi tahrirlash ekranlari.
 *
 * `AdminPagesSmokeTest` faqat parametrsiz sahifalarni ochadi. Xatolar esa
 * ko'pincha aynan tahrirlash ekranlarida yashiringan bo'ladi: u yerda
 * mavjud yozuv maydonlari chizilади va noto'g'ri tipdagi qiymat darrov
 * ko'rinadi.
 *
 * Har bir `{model}/edit` marshruti uchun bazadagi birinchi yozuv olinadi.
 */
class AdminEditPagesSmokeTest extends TestCase
{
    public function test_every_edit_screen_opens(): void
    {
        $admin = User::where('role', 'admin')->orderBy('id')->firstOrFail();

        $routes = collect(Route::getRoutes())
            ->filter(fn ($route) => in_array('GET', $route->methods(), true))
            ->filter(fn ($route) => str_starts_with($route->uri(), 'admin'))
            ->filter(fn ($route) => str_ends_with($route->uri(), '/edit'))
            ->values();

        $this->assertGreaterThan(10, $routes->count(), 'Tahrirlash marshrutlari topilmadi');

        $broken = [];
        $checked = 0;

        foreach ($routes as $route) {
            $uri = $route->uri();

            // admin/posts/{post}/edit -> posts
            if (!preg_match('~^admin/([^/]+)/\{[^}]+\}/edit$~', $uri, $m)) {
                continue;
            }

            $table = $this->tableFor($m[1]);

            if ($table === null || !$this->hasTable($table)) {
                continue;
            }

            $id = DB::table($table)->orderBy('id')->value('id');

            if ($id === null) {
                continue;
            }

            $response = $this->actingAs($admin)->get('/admin/' . $m[1] . '/' . $id . '/edit');
            $checked++;

            if (!in_array($response->status(), [200, 302], true)) {
                $broken[] = $m[1] . '/' . $id . '/edit -> ' . $response->status();
            }
        }

        $this->assertGreaterThan(10, $checked, 'Yetarlicha ekran tekshirilmadi');
        $this->assertSame([], $broken, "Ochilmagan tahrirlash ekranlari:\n" . implode("\n", $broken));
    }

    /** Marshrut nomidan jadval nomini topadi. */
    private function tableFor(string $segment): ?string
    {
        $known = [
            'dynamic-menus'         => 'dinamik_menus',
            'educational-programs'  => 'educational_programs',
            'entrance-requirements' => 'entrance_requirements',
            'posts_categories'      => 'posts_categories',
            'products_categories'   => 'products_categories',
            'document_categories'   => 'document_categories',
            'advantage_categories'  => 'advantage_categories',
            'employ_staff'          => 'employ_staff',
            'stracture_types'       => 'stracture_types',
            'popups'                => 'reks',
            'banners'               => 'brands',
            'students'              => 'members',
            'services'              => 'services',
            'works'                 => 'works',
            'langs'                 => 'langs',
            'users'                 => 'users',
            'departaments'          => 'departments',
        ];

        if (isset($known[$segment])) {
            return $known[$segment];
        }

        return Str::snake(Str::plural($segment));
    }

    private function hasTable(string $table): bool
    {
        try {
            DB::table($table)->limit(1)->get();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
