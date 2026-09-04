<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * `form_menus.order` ustuniga standart qiymat beradi.
 *
 * Ustun NOT NULL, ammo standart qiymatsiz edi — ya'ni tartib raqami
 * ochiq ko'rsatilmasa yozuv yaratilmasdi. Sahifaga fayl yoki kartochka
 * qo'shishda tartib ko'pincha ahamiyatsiz, shuning uchun 0.
 *
 * doctrine/dbal o'rnatilmagani uchun xom SQL ishlatiladi.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `form_menus` MODIFY `order` VARCHAR(255) NOT NULL DEFAULT '0'");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `form_menus` MODIFY `order` VARCHAR(255) NOT NULL');
    }
};
