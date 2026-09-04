<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sayt boshi va pastidagi ikkita shior.
 *
 * `tagline` — institutning asosiy shiori ("Sirdaryo yoshlari taʼlim va
 * taraqqiyot yoʻlida!"), `slogan` — tashkil topgan yildan beri nima
 * qilinayotgani ("2022-yildan pedagogika xizmatidamiz").
 *
 * Shu paytgacha ular frontend kodida uch tilda yozib qoʻyilgan edi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->json('tagline')->nullable()->after('desc');
            $table->json('slogan')->nullable()->after('tagline');
        });
    }

    public function down(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->dropColumn(['tagline', 'slogan']);
        });
    }
};
