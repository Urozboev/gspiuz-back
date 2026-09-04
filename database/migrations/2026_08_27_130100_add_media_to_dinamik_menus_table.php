<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sahifaning video havolasi va rasm galereyasi.
 *
 * "Bitta sahifa" ko'rinishida matndan tashqari video va bir nechta rasm
 * qo'yish kerak bo'ladi. Matn ichiga ham qo'yish mumkin (CKEditor buni
 * qo'llab-quvvatlaydi), ammo alohida maydonlar saytda matndan keyin
 * tartibli chiziladi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dinamik_menus', function (Blueprint $table) {
            $table->string('video')->nullable()->after('background');
            $table->json('images')->nullable()->after('video');
        });
    }

    public function down(): void
    {
        Schema::table('dinamik_menus', function (Blueprint $table) {
            $table->dropColumn(['video', 'images']);
        });
    }
};
