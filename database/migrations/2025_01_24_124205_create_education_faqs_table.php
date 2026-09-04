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
        Schema::create('education_faqs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('educational_program_id');
            $table->foreign('educational_program_id')->references('id')->on('educational_programs')->onDelete('cascade');
            $table->unsignedBigInteger('skill_id');
            $table->foreign('skill_id')->references('id')->on('e_rskills')->onDelete('cascade');
            $table->text('question')->nullable();
            $table->string('action')->nullable();
            $table->text('answer')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('education_faqs')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education_faqs');
    }
};
