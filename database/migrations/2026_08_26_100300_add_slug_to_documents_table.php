<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hujjatlarga barqaror kalit — seed va import qayta ishga tushirilganda
 * dublikat yaratmasligi uchun.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('slug')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
