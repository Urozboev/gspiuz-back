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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->unsignedBigInteger('structure_type_id');
//            $table->unsignedBigInteger('type_id');
            $table->string('slug');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('active')->default(1);
            $table->string('code')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Soft delete qo'shish


            $table->foreign('structure_type_id')->references('id')->on('structure_types')->onDelete('cascade');
//            $table->foreign('type_id')->references('id')->on('employ_types')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
