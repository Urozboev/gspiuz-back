<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdditionalFunctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additional_functions', function (Blueprint $table) {
            $table->id();
            $table->string('telegram_bot_token')->nullable();
            $table->string('telegram_group_id')->nullable();
            $table->text('livechat')->nullable();
            $table->text('yandex_index')->nullable();
            $table->text('google_index')->nullable();
            $table->text('yandex_metrika')->nullable();
            $table->text('google_analytics')->nullable();
            $table->string('sitemap')->nullable();
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
        Schema::dropIfExists('additional_functions');
    }
}
