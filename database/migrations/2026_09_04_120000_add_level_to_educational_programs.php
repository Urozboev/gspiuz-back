<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Taʼlim dasturiga daraja (bakalavriat / magistratura) qoʻshadi.
 *
 * Nima uchun: menyuda "Bakalavriat" va "Magistratura" bandlari bor,
 * lekin ikkalasi ham bir xil filtrlanmagan roʻyxatni ochardi — ya'ni
 * "Magistratura" bosilganda bakalavriat yoʻnalishlari koʻrinardi.
 *
 * Mavjud yozuvlar `code` yoki nomdagi klassifikator raqamidan
 * toʻldiriladi: 60 — bakalavriat, 70 — magistratura. Aniqlab
 * boʻlmasa `null` qoladi va admin paneldan tanlanadi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('educational_programs', function (Blueprint $table) {
            $table->string('level', 20)->nullable()->after('code')->index();
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::table('educational_programs', function (Blueprint $table) {
            $table->dropIndex(['level']);
            $table->dropColumn('level');
        });
    }

    /** Klassifikator kodining birinchi ikki raqamidan darajani aniqlaydi. */
    private function backfill(): void
    {
        foreach (DB::table('educational_programs')->get(['id', 'code', 'name', 'slug']) as $row) {
            $level = $this->levelFrom($row->code)
                ?? $this->levelFrom($row->name)
                ?? $this->levelFrom($row->slug);

            if ($level !== null) {
                DB::table('educational_programs')->where('id', $row->id)->update(['level' => $level]);
            }
        }
    }

    private function levelFrom(?string $value): ?string
    {
        if (!preg_match('~\b(\d{2})\d{6}\b~', (string) $value, $m)) {
            return null;
        }

        return match ($m[1]) {
            '60' => 'bachelor',
            '70' => 'master',
            default => null,
        };
    }
};
