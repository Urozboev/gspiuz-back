<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Boʻlim sahifasi oʻzidagi HAMMA xodimni koʻrsatishi.
 *
 * Bu xato faqat haqiqiy maʼlumot kelgach chiqdi. Ilgari har boʻlimda
 * bittadan xodim boʻlgani uchun sezilmagan, holbuki:
 *
 *   – rahbar faqat `position_id = 4` boʻyicha qidirilardi, shuning uchun
 *     rektor va kafedra mudiri oʻz sahifasida rahbar sifatida chiqmasdi;
 *   – qolganlar `position_id != 4` sharti bilan olinardi, shuning uchun
 *     bir boʻlimda bir nechta boshliq boʻlsa (institut kengashi tarkibi)
 *     ulardan bittasidan boshqasi hech qayerda koʻrinmasdi.
 */
class DepartmentRosterTest extends TestCase
{
    private function api(string $path)
    {
        $prefix = trim((string) config('api.prefix'), '/');

        return $this->withHeaders(['Accept-Language' => 'uz'])->getJson('/' . $prefix . $path);
    }

    /** Sahifada koʻrinadigan xodimlar soni. */
    private function shownCount(string $slug): int
    {
        $json = $this->api('/department/' . $slug)->assertOk()->json();

        return (($json['department_boss'] ?? null) ? 1 : 0) + count($json['simple_employee'] ?? []);
    }

    public function test_every_employee_of_a_department_is_shown(): void
    {
        $departments = DB::table('departments')
            ->join('employ_metas', 'employ_metas.department_id', '=', 'departments.id')
            ->whereNull('employ_metas.deleted_at')
            ->whereNotNull('departments.slug')
            ->groupBy('departments.id', 'departments.slug')
            ->select('departments.slug', DB::raw('COUNT(*) as total'))
            ->get();

        if ($departments->isEmpty()) {
            $this->markTestSkipped('Xodimi bor boʻlim yoʻq — baza boʻsh.');
        }

        $wrong = [];

        foreach ($departments as $department) {
            $shown = $this->shownCount($department->slug);

            if ($shown !== (int) $department->total) {
                $wrong[] = sprintf(
                    '%s: bazada %d, sahifada %d',
                    $department->slug,
                    $department->total,
                    $shown
                );
            }
        }

        $this->assertSame([], $wrong, implode("\n", $wrong));
    }

    /** Rahbar roʻyxatda ikki marta chiqmasligi kerak. */
    public function test_the_head_is_not_repeated_among_the_others(): void
    {
        $slug = DB::table('departments')
            ->join('employ_metas', 'employ_metas.department_id', '=', 'departments.id')
            ->whereNotNull('departments.slug')
            ->groupBy('departments.id', 'departments.slug')
            ->havingRaw('COUNT(*) > 1')
            ->value('departments.slug');

        if ($slug === null) {
            $this->markTestSkipped('Bir nechta xodimli boʻlim yoʻq.');
        }

        $json = $this->api('/department/' . $slug)->assertOk()->json();

        $boss = $json['department_boss'] ?? null;

        $this->assertNotNull($boss, "Rahbar topilmadi: {$slug}");

        $others = array_column($json['simple_employee'] ?? [], 'id');

        $this->assertNotContains($boss['id'], $others, 'Rahbar qolganlar roʻyxatida ham bor');
    }

    /**
     * Rahbarlik lavozimlarining hammasi tan olinishi kerak, nafaqat
     * "boʻlim boshligʻi".
     */
    public function test_a_head_of_department_is_recognised_by_any_leadership_position(): void
    {
        $rows = DB::table('departments')
            ->join('employ_metas', 'employ_metas.department_id', '=', 'departments.id')
            ->whereIn('employ_metas.position_id', [1, 2, 3, 4, 5])
            ->whereNotNull('departments.slug')
            ->distinct()
            ->limit(10)
            ->pluck('departments.slug');

        if ($rows->isEmpty()) {
            $this->markTestSkipped('Rahbarlik lavozimidagi xodim yoʻq.');
        }

        $missing = [];

        foreach ($rows as $slug) {
            if (($this->api('/department/' . $slug)->assertOk()->json('department_boss')) === null) {
                $missing[] = $slug;
            }
        }

        $this->assertSame([], $missing, 'Rahbar koʻrsatilmadi: ' . implode(', ', $missing));
    }
}
