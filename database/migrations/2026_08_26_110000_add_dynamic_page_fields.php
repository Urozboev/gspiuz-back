<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dinamik sahifalar uchun yetishmayotgan maydonlar.
 *
 * `menus → dinamik_menus → form_menus` tuzilmasi loyihada avvaldan bor edi,
 * ammo hech qachon ishlatilmagan: `dinamik_menus` da modelda e'lon qilingan
 * `text` ustuni umuman yo'q edi, ya'ni sahifa matnini saqlab bo'lmasdi.
 *
 * Shu bilan birga sahifaning ko'rinish turi (`layout`) qo'shiladi:
 *   single — bitta sahifa: sarlavha + HTML matn + fayllar
 *   cards  — kartochkalar to'plami, har biri alohida sahifada ochiladi
 *   files  — faqat yuklab olinadigan fayllar ro'yxati
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dinamik_menus', function (Blueprint $table) {
            // Sahifa tanasi — modelda bor edi, jadvalda yo'q edi.
            $table->json('text')->nullable()->after('short_title');
            $table->string('layout', 20)->default('single')->after('menu_id');
            $table->json('meta_title')->nullable()->after('text');
            $table->json('meta_description')->nullable()->after('meta_title');
            $table->boolean('active')->default(1)->after('meta_description');
        });

        Schema::table('form_menus', function (Blueprint $table) {
            // layout=cards da har bir blok alohida sahifa bo'ladi.
            $table->string('slug')->nullable()->after('dinamik_menu_id');
            $table->json('body')->nullable()->after('text');
            $table->string('icon')->nullable()->after('body');
            $table->string('link')->nullable()->after('icon');
            $table->string('image')->nullable()->after('link');
            $table->date('date')->nullable()->after('image');
            $table->boolean('active')->default(1)->after('date');

            // Manzil sahifa ichida unikal: /conferences/xalqaro-anjuman-2026
            $table->unique(['dinamik_menu_id', 'slug'], 'form_menus_page_slug_unique');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->boolean('active')->default(1)->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn('active');
        });

        Schema::table('form_menus', function (Blueprint $table) {
            $table->dropUnique('form_menus_page_slug_unique');
            $table->dropColumn(['slug', 'body', 'icon', 'link', 'image', 'date', 'active']);
        });

        Schema::table('dinamik_menus', function (Blueprint $table) {
            $table->dropColumn(['text', 'layout', 'meta_title', 'meta_description', 'active']);
        });
    }
};
