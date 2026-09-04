<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('dec')->nullable();
            $table->text('position')->nullable();
            $table->string('img')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('instagram_link')->nullable();
            $table->string('telegram_link')->nullable();
            $table->string('linkedin_link')->nullable();
            $table->string('facebook_link')->nullable();
            $table->string('gender')->nullable();
            $table->string('work_time')->nullable();
            $table->string('slug');
            $table->string('yers')->nullable();
            $table->integer('view_count')->default(0);
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
        Schema::dropIfExists('members');
    }
}
