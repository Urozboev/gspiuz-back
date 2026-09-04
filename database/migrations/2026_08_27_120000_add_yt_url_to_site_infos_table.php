<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bosh sahifadagi video havolasi.
 *
 * `yt_url` modelning `fillable` ro'yxatida, admin formasida, API javobida
 * va kontrollerda ishlatilgan, ammo jadvalda bunday ustun yo'q edi.
 * Natijada "Sayt ma'lumotlari" formasini saqlash har safar
 * "Unknown column 'yt_url'" xatosi bilan yiqilardi — ya'ni logotip,
 * manzil, rekvizitlar va boshqa sozlamalarni umuman saqlab bo'lmasdi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->string('yt_url')->nullable()->after('youtube');
        });
    }

    public function down(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->dropColumn('yt_url');
        });
    }
};
