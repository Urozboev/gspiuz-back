<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateConfigGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_groups', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->integer('position')->nullable();
            $table->boolean('is_active')->default(0);
            $table->timestamps();
        });

        DB::table('config_groups')->insert([
            [
                'title' => 'Продукты',
                'is_active' => 1,
                'position' => 1
            ],
            [
                'title' => 'Посты',
                'is_active' => 1,
                'position' => 2
            ],
            [
                'title' => 'Документы',
                'is_active' => 1,
                'position' => 3
            ],
            [
                'title' => 'Компания',
                'is_active' => 1,
                'position' => 4
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('config_groups');
    }
}
