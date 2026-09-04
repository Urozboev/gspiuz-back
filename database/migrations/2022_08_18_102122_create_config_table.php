<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('group_id')->nullable();
            $table->string('title');
            $table->string('route');
            $table->boolean('is_active')->default(0);
            $table->integer('position')->nullable();
            $table->timestamps();
        });

        DB::table('config')->insert([
            [
                'title' => 'Запросы',
                'route' => 'applications',
                'is_active' => 1,
                'group_id' => null
            ],
            [
                'title' => 'Продукты',
                'route' => 'products',
                'is_active' => 1,
                'group_id' => 1
            ],
            [
                'title' => 'Категории продуктов',
                'route' => 'products_categories',
                'is_active' => 1,
                'group_id' => 1
            ],
            [
                'title' => 'Бренды продуктов',
                'route' => 'brands',
                'is_active' => 1,
                'group_id' => 1
            ],
            [
                'title' => 'Посты',
                'route' => 'posts',
                'is_active' => 1,
                'group_id' => 2
            ],
            [
                'title' => 'Категории постов',
                'route' => 'posts_categories',
                'is_active' => 1,
                'group_id' => 2
            ],
            [
                'title' => 'Документы',
                'route' => 'documents',
                'is_active' => 1,
                'group_id' => 3
            ],
            [
                'title' => 'Категории документов',
                'route' => 'document_categories',
                'is_active' => 1,
                'group_id' => 3
            ],
            [
                'title' => 'Отзыви',
                'route' => 'feedbacks',
                'is_active' => 1,
                'group_id' => 4
            ],
            [
                'title' => 'Команда',
                'route' => 'members',
                'is_active' => 1,
                'group_id' => 4
            ],
            [
                'title' => 'Партнеры',
                'route' => 'partners',
                'is_active' => 1,
                'group_id' => 4
            ],
            [
                'title' => 'Наши работы',
                'route' => 'works',
                'is_active' => 1,
                'group_id' => 4
            ],
            [
                'title' => 'Вакансии',
                'route' => 'vacancies',
                'is_active' => 1,
                'group_id' => 4
            ],
            [
                'title' => 'FAQ',
                'route' => 'questions',
                'is_active' => 1,
                'group_id' => 4
            ],
            [
                'title' => 'Услуги',
                'route' => 'services',
                'is_active' => 1,
                'group_id' => null
            ],
            [
                'title' => 'Сертификаты',
                'route' => 'certificates',
                'is_active' => 1,
                'group_id' => null
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
        Schema::dropIfExists('config');
    }
}
