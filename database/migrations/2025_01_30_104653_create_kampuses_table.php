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
        Schema::create('kampuses', function (Blueprint $table) {
            $table->id();
            $table->text('name')->nullable();
            $table->string('educational_programs')->nullable();
            $table->string('audience_size')->nullable();
            $table->string('green_zone')->nullable();
            $table->text('first_name')->nullable();
            $table->string('map')->nullable();
            $table->text('second_name')->nullable();
            $table->text('third_name')->nullable();
            $table->string('slug');
            $table->string('active')->nullable();
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
        Schema::dropIfExists('kampuses');
    }
};
