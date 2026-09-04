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
        Schema::create('dinamik_menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id');
            $table->text('title');
            $table->text('short_title')->nullable();
            $table->text('background')->nullable();
            $table->text('file')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Soft delete qo'shish

            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dinamik_menus');
    }
};
