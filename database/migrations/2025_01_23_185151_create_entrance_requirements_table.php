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
        Schema::create('entrance_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('educational_program_id');
            $table->foreign('educational_program_id')->references('id')->on('educational_programs')->onDelete('cascade');
            $table->text('name')->nullable();
            $table->text('dec')->nullable();
            $table->string('photo')->nullable();
            $table->string('active')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entrance_requirements');
    }
};
