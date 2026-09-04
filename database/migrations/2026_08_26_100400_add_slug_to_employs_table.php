<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Employ modeli 'slug' ni fillable'da e'lon qilgan, lekin ustun jadvalda yo'q edi.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('employs', function (Blueprint $table) {
            $table->string('slug')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('employs', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
