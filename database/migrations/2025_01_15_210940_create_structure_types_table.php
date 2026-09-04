<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('structure_types', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->integer('order')->nullable();
            $table->boolean('active')->default(1);
            $table->timestamps();
            $table->softDeletes(); // Soft delete qo'shish

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('structure_types');
    }
};
