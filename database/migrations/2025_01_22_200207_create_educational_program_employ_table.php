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
        Schema::create('educational_program_employ', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('educational_program_id');
            $table->unsignedBigInteger('employ_id');
            $table->timestamps();

            $table->foreign('educational_program_id')->references('id')->on('educational_programs')->onDelete('cascade');
            $table->foreign('employ_id')->references('id')->on('employs')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educational_program_employ');
    }
};
