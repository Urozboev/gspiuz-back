<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hujjatlarni turkumga bog'lash (/documents?category=slug) va
 * turkumlarga slug qo'shish — Document modelidagi document_category()
 * munosabati shu ustunni kutadi, lekin u jadvalda yo'q edi.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedBigInteger('document_category_id')->nullable()->index();
        });

        Schema::table('document_categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('document_category_id');
        });

        Schema::table('document_categories', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
