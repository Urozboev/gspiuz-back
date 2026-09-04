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
        Schema::create('entrance_requirement_e_rskill', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entrance_requirement_id');
            $table->unsignedBigInteger('skill_id');
            $table->timestamps();
            $table->foreign('entrance_requirement_id')->references('id')->on('entrance_requirements')->onDelete('cascade');
            $table->foreign('skill_id')->references('id')->on('e_rskills')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entrance_requirement_e_rskill');
    }
};
