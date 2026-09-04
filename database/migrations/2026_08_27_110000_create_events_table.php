<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tadbirlar kalendari.
 *
 * Saytdagi /events sahifasi va bosh sahifadagi "yaqin tadbirlar" bloki
 * shu jadvaldan oziqlanadi. Yangiliklardan farqi — tadbirning aniq
 * sanasi, vaqti va joyi bo'ladi, kalendarda ko'rsatiladi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->json('title');
            $table->json('desc')->nullable();
            $table->string('slug')->unique();

            // Boshlanish sanasi majburiy; ko'p kunlik tadbirda tugash sanasi ham.
            $table->date('date');
            $table->date('end_date')->nullable();
            $table->string('time', 32)->nullable();

            $table->json('location')->nullable();
            $table->string('type', 64)->nullable();
            $table->string('url')->nullable();
            $table->string('img')->nullable();

            $table->boolean('active')->default(1);
            $table->unsignedInteger('views_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
