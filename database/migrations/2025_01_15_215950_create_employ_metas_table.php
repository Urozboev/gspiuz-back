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
        Schema::create('employ_metas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employ_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('employ_staff_id');
            $table->unsignedBigInteger('employ_form_id');
            $table->string('contrakt_date')->nullable();
            $table->string('contrakt_number')->nullable();
            $table->string('slug');
            $table->unsignedBigInteger('employ_type_id');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();


            $table->foreign('employ_id')->references('id')->on('employs')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade');
            $table->foreign('employ_staff_id')->references('id')->on('employ_staff')->onDelete('cascade');
            $table->foreign('employ_type_id')->references('id')->on('employ_types')->onDelete('cascade');
            $table->foreign('employ_form_id')->references('id')->on('employ_fors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employ_metas');
    }
};
