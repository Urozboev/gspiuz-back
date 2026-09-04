<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Saytga kirilganda ochiladigan modal xabarlar (bayram tabriklari,
 * muhim e'lonlar) uchun maydonlar.
 *
 * `reks` jadvali avval bitta reklama bloki uchun ishlatilgan. Endi u
 * bir nechta xabarni saqlaydi va har biri o'z muddatiga ega bo'ladi:
 * tabrik belgilangan kunda o'zi paydo bo'lib, o'zi yo'qoladi — buning
 * uchun hech kim admin panelga kirib o'chirishi shart emas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reks', function (Blueprint $table) {
            $table->json('desc')->nullable()->after('title');
            $table->date('starts_at')->nullable()->after('action');
            $table->date('ends_at')->nullable()->after('starts_at');
            $table->boolean('active')->default(1)->after('ends_at');
            $table->unsignedInteger('order')->default(0)->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('reks', function (Blueprint $table) {
            $table->dropColumn(['desc', 'starts_at', 'ends_at', 'active', 'order']);
        });
    }
};
