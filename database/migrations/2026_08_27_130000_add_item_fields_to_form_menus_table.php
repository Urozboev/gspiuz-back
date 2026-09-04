<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sahifa yozuvlari uchun qo'shimcha maydonlar.
 *
 * `form_menus` uchala sahifa ko'rinishida ham yozuv jadvali bo'lib xizmat
 * qiladi:
 *   single — ikonli bo'limlar
 *   cards  — kartochkalar (bosilganda alohida sahifa ochiladi)
 *   files  — yuklab olinadigan fayllar
 *
 * `files` ko'rinishi uchun faylning o'zi kerak edi; kartochkalar uchun
 * qo'shimcha sarlavha va video havolasi.
 *
 * Fayl hajmi va turi saqlanmaydi — ular o'qishda diskdan olinadi, shunda
 * fayl almashtirilsa ma'lumot eskirmaydi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_menus', function (Blueprint $table) {
            $table->string('file')->nullable()->after('image');
            $table->string('video')->nullable()->after('file');
            $table->json('subtitle')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('form_menus', function (Blueprint $table) {
            $table->dropColumn(['file', 'video', 'subtitle']);
        });
    }
};
