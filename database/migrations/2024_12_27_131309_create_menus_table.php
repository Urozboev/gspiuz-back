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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->string('order');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('path')->nullable();
          $table->foreign('parent_id')->references('id')->on('menus')->onDelete('cascade');
            $table->string('slug');
            $table->timestamps();
            $table->softDeletes(); // Soft delete qo'shish

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
