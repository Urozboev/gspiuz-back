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
        Schema::create('file_menus', function (Blueprint $table) {
            $table->id();
            $table->text('name')->nullable();
            $table->text('body')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->text('m_type')->nullable();
            $table->text('text')->nullable();
            $table->unsignedBigInteger('form_menu_id')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Soft delete qo'shish

            $table->foreign('form_menu_id')->references('id')->on('form_menus')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_menus');
    }
};
