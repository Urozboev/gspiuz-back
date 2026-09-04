<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bloklarni guruhlash kaliti.
 *
 * Dastlab `position` ustunidan foydalanish mo'ljallangan edi, ammo u butun
 * son — tab kaliti esa matn ("bachelor", "master"). Shuning uchun alohida
 * ustun. Qabul sahifasidagi beshta tab shu maydon orqali ajratiladi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_menus', function (Blueprint $table) {
            $table->string('group')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('form_menus', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }
};
