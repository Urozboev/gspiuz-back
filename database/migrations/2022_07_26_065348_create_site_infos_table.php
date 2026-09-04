<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_infos', function (Blueprint $table) {
            $table->id();
            $table->text('title')->nullable();
            $table->text('desc')->nullable();
            $table->string('logo')->nullable();
            $table->string('logo_dark')->nullable();
            $table->text('address')->nullable()->comment('adresses |');
            $table->text('phone_number')->nullable()->comment('phone numbers |, first is main');
            $table->string('email')->nullable();
            $table->text('work_time')->nullable();
            $table->text('map')->nullable()->comment('iframe');

            $table->text('audience_size')->nullable();
            $table->string('educational_programs')->nullable();
            $table->string('green_zone')->nullable();
            $table->string('library_collection')->nullable();
            $table->string('number_of_students')->nullable();
            $table->string('male_students')->nullable();
            $table->string('female_students')->nullable();

            $table->string('exchange')->nullable();
            $table->string('favicon')->nullable();
            $table->string('instagram')->nullable();
            $table->string('telegram')->nullable();
            $table->string('facebook')->nullable();
            $table->string('youtube')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('site_infos');
    }
}
