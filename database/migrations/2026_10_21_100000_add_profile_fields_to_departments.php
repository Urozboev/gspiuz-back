<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Boʻlim, fakultet va kafedra haqida maʼlumot.
 *
 * Ilgari boʻlim faqat nom edi: sahifasida xodimlardan boshqa hech narsa
 * koʻrsatib boʻlmasdi. Endi yangiliklardagi kabi erkin matn (rasm,
 * jadval, roʻyxat bilan), muqova rasmi va aloqa maʼlumotlari bor.
 *
 * Hammasi ixtiyoriy: toʻldirilmasa sahifa avvalgidek faqat xodimlarni
 * koʻrsatadi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->json('description')->nullable()->after('name');
            $table->string('image')->nullable()->after('description');
            $table->string('phone', 100)->nullable()->after('image');
            $table->string('email', 150)->nullable()->after('phone');
            $table->json('address')->nullable()->after('email');
            $table->json('work_time')->nullable()->after('address');
            $table->unsignedInteger('order')->default(0)->after('work_time');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn(['description', 'image', 'phone', 'email', 'address', 'work_time', 'order']);
        });
    }
};
