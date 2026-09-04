<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Murojaatlar (rektorga / tyutorga / komplayens xizmatiga).
 *
 * Oddiy "Bog'lanish" formasidan (applications) farqi — bu yerda
 * ariza raqami, holati va rasmiy javob matni saqlanadi.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('appeals', function (Blueprint $table) {
            $table->id();
            $table->string('ticket', 32)->unique();          // Ariza raqami: MRJ-2026-000123
            $table->string('type', 32)->index();             // rector | tutor | compliance
            $table->string('status', 32)->default('new')->index(); // new | in_review | answered | rejected

            $table->string('name');
            $table->string('phone_number', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->text('message');
            $table->string('file')->nullable();              // Ilova qilingan hujjat

            $table->text('answer')->nullable();              // Rasmiy javob
            $table->timestamp('answered_at')->nullable();
            $table->unsignedBigInteger('answered_by')->nullable();

            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('appeals');
    }
};
