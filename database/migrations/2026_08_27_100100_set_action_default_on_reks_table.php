<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * `reks.action` ustunini bayroqqa aylantiradi.
 *
 * Ustun varchar(255) bo'lib, standart qiymatsiz NOT NULL edi. Eski admin
 * kontrolleri unga so'zma-so'z 'default_value' qatorini yozardi. Aslida bu
 * ha/yo'q bayrog'i — havola tugmasi ko'rsatilsinmi yoki yo'q.
 *
 * doctrine/dbal o'rnatilmagani uchun ustun xom SQL bilan o'zgartiriladi.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Avval matnli qiymatlarni raqamga keltiramiz.
        DB::table('reks')->whereNotIn('action', ['0', '1'])->update(['action' => '0']);

        DB::statement('ALTER TABLE `reks` MODIFY `action` TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `reks` MODIFY `action` VARCHAR(255) NOT NULL');
    }
};
