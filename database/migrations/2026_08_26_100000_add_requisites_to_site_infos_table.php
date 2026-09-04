<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bank rekvizitlari — frontenddagi /requisites sahifasi uchun.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->json('legal_name')->nullable();   // To'liq yuridik nom
            $table->string('bank_account')->nullable(); // Hisob raqami
            $table->json('bank_name')->nullable();     // Bank nomi
            $table->string('mfo', 20)->nullable();     // MFO
            $table->string('inn', 20)->nullable();     // STIR (INN)
            $table->string('oked', 20)->nullable();    // OKED
            $table->string('treasury_account')->nullable(); // G'aznachilik hisob raqami
        });
    }

    public function down()
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->dropColumn([
                'legal_name', 'bank_account', 'bank_name',
                'mfo', 'inn', 'oked', 'treasury_account',
            ]);
        });
    }
};
