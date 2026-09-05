<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Call markaz raqami uchun alohida maydon.
 *
 * Saytda uchta joyda telefon koʻrsatiladi: tepadagi sarlavha, yuguruvchi
 * satrdagi "Call markaz" yozuvi va pastki qism. Ilgari uchalasi ham
 * bitta `phone_number` dan olinardi, holbuki call markaz raqami odatda
 * qabulxona raqamidan boshqa boʻladi.
 *
 * Ixtiyoriy: boʻsh qoldirilsa frontend `phone_number` ni ishlatadi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->string('call_center', 100)->nullable()->after('phone_number');
        });
    }

    public function down(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->dropColumn('call_center');
        });
    }
};
