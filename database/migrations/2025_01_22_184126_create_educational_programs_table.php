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
        Schema::create('educational_programs', function (Blueprint $table) {
            $table->id();
            $table->text('name')->nullable();
            $table->text('icon')->nullable();
            $table->string('education_years')->nullable();
            $table->string('yt_link')->nullable();
            $table->text('first_name')->nullable();
            $table->text('second_name')->nullable();
            $table->text('third_name')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('educational_programs')->onDelete('cascade');
            $table->string('slug');
            $table->string('code')->nullable();
            $table->string('file')->nullable();
            $table->text('lang')->nullable();
            $table->text('map')->nullable();
            $table->string('date')->nullable();
            $table->string('sirtqi_date')->nullable();
            $table->text('form_education')->nullable();
            $table->string('active')->nullable();
            $table->string('daytime')->nullable();
            $table->string('part_time')->nullable();
            $table->string('photo')->nullable();
            $table->text('first_description')->nullable();
            $table->text('second_description')->nullable();
            $table->text('third_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educational_programs');
    }
};
