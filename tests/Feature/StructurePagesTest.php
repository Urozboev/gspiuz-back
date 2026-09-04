<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Tuzilma sahifalari: boʻlim, fakultet va kafedra detallari.
 *
 * Bu endpointlar bazadagi haqiqiy slug bilan chaqirilishi kerak. Ilgari
 * ular testlar bilan qoplanmagan edi va bitta tahrir uchalasini ham 500
 * xatolikka olib kelgan — buni faqat qoʻlda tekshirishda sezdim.
 */
class StructurePagesTest extends TestCase
{
    private function api(string $path, string $locale = 'uz')
    {
        $prefix = trim((string) config('api.prefix'), '/');

        return $this->withHeaders(['Accept-Language' => $locale])->getJson('/' . $prefix . $path);
    }

    /** Berilgan tuzilma turidagi boʻlim manzillari. */
    private function slugs(?int $structureType = null, int $limit = 3): array
    {
        return DB::table('departments')
            ->when($structureType, fn ($query) => $query->where('structure_type_id', $structureType))
            ->whereNotNull('slug')
            ->limit($limit)
            ->pluck('slug')
            ->all();
    }

    public function test_every_department_detail_opens(): void
    {
        $slugs = $this->slugs(null, 20);

        $this->assertNotEmpty($slugs, 'Boʻlimlar topilmadi');

        $broken = [];

        foreach ($slugs as $slug) {
            $status = $this->api('/department/' . $slug)->status();

            if ($status !== 200) {
                $broken[] = "department/{$slug} -> {$status}";
            }
        }

        $this->assertSame([], $broken, implode("\n", $broken));
    }

    public function test_faculty_and_kafedra_details_open(): void
    {
        $broken = [];

        foreach ($this->slugs(null, 20) as $slug) {
            foreach (['fakultet', 'kafedralar'] as $endpoint) {
                $status = $this->api('/' . $endpoint . '/' . $slug)->status();

                if ($status !== 200) {
                    $broken[] = "{$endpoint}/{$slug} -> {$status}";
                }
            }
        }

        $this->assertSame([], $broken, implode("\n", $broken));
    }

    public function test_structure_lists_open_in_every_language(): void
    {
        foreach (['uz', 'ru', 'en'] as $locale) {
            foreach (['/department', '/fakultet', '/kafedralar', '/leaderships', '/tutors', '/positions'] as $path) {
                $this->api($path, $locale)->assertOk();
            }
        }
    }

    /**
     * Boʻlim sahifasida xodimning aynan oʻsha boʻlimdagi tayinlovi
     * koʻrsatilishi kerak — bu eager-load cheklovi buzilsa yiqiladi.
     */
    public function test_department_page_carries_employee_meta(): void
    {
        $slug = DB::table('departments')
            ->join('employ_metas', 'employ_metas.department_id', '=', 'departments.id')
            ->whereNotNull('departments.slug')
            ->value('departments.slug');

        // Namunaviy maʼlumot tozalangan boʻlsa xodimli boʻlim boʻlmaydi.
        // Bunda tekshiradigan narsa yoʻq — testni oʻtkazib yuboramiz,
        // yiqitmaymiz: bu kod xatosi emas, baza boʻsh degani.
        if ($slug === null) {
            $this->markTestSkipped('Xodimi bor boʻlim yoʻq — baza boʻsh.');
        }

        $json = $this->api('/department/' . $slug)->assertOk()->json();

        $rows = array_filter(array_merge(
            [$json['department_boss'] ?? null],
            $json['simple_employee'] ?? []
        ));

        $this->assertNotEmpty($rows, "Boʻlimda xodim koʻrinmadi: {$slug}");

        foreach ($rows as $row) {
            $this->assertArrayHasKey('employ_meta', $row, 'Tayinlov maʼlumoti yoʻq');
        }
    }
}
