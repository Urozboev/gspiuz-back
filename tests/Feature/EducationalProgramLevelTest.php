<?php

namespace Tests\Feature;

use App\Models\EducationalProgram;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Taʼlim yoʻnalishlarini daraja boʻyicha ajratish.
 *
 * Menyuda "Bakalavriat" va "Magistratura" bandlari alohida, lekin
 * ilgari ikkalasi ham bir xil filtrlanmagan roʻyxatni ochardi —
 * ya'ni "Magistratura" bosilganda bakalavriat yoʻnalishlari
 * koʻrinardi. Filtr ishlashini shu test qoʻriqlaydi.
 */
class EducationalProgramLevelTest extends TestCase
{
    private function api(string $path)
    {
        $prefix = trim((string) config('api.prefix'), '/');

        return $this->withHeaders(['Accept-Language' => 'uz'])->getJson('/' . $prefix . $path);
    }

    /** @return array<int, array<string, mixed>> */
    private function rows(string $query = ''): array
    {
        $json = $this->api('/educational-programs' . $query)->assertOk()->json();

        return $json['data'] ?? $json;
    }

    public function test_level_is_present_in_the_response(): void
    {
        $rows = $this->rows();

        $this->assertNotEmpty($rows, 'Taʼlim yoʻnalishlari topilmadi');
        $this->assertArrayHasKey('level', $rows[0]);
    }

    public function test_filter_returns_only_the_requested_level(): void
    {
        $program = EducationalProgram::whereNull('parent_id')->firstOrFail();
        $original = $program->level;

        try {
            // Bittasini magistraturaga oʻtkazamiz va ikkala roʻyxatni tekshiramiz.
            DB::table('educational_programs')->where('id', $program->id)->update(['level' => 'master']);

            $masters = $this->rows('?level=master');
            $bachelors = $this->rows('?level=bachelor');

            $this->assertSame(
                [$program->id],
                array_column($masters, 'id'),
                'Magistratura roʻyxatida boshqa yoʻnalish bor'
            );

            $this->assertNotContains(
                $program->id,
                array_column($bachelors, 'id'),
                'Magistratura yoʻnalishi bakalavriat roʻyxatiga tushib qolgan'
            );
        } finally {
            DB::table('educational_programs')->where('id', $program->id)->update(['level' => $original]);
        }
    }

    public function test_without_the_filter_everything_comes_back(): void
    {
        $total = EducationalProgram::whereNull('parent_id')->count();

        $this->assertCount($total, $this->rows());
    }

    /** Notoʻgʻri qiymat 500 bermasligi kerak — boʻsh roʻyxat qaytadi. */
    public function test_unknown_level_returns_an_empty_list(): void
    {
        $this->assertSame([], $this->rows('?level=yoq-bunday-daraja'));
    }
}
