<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username');
            $table->string('password');
            $table->enum('role', ['admin', 'moderator']);
            $table->timestamps();
        });

        // Boshlang'ich admin hisobi. Ilgari bu yerda 'admin' / '123123'
        // yozib qo'yilgan edi — ya'ni har bir yangi o'rnatma ochiq matnda
        // repozitoriyda turgan parol bilan ishga tushardi.
        //
        // Endi qiymatlar .env dan olinadi; parol berilmasa tasodifiy
        // yaratiladi va konsolga chiqariladi, shunda uni faqat o'rnatuvchi
        // ko'radi va darhol almashtira oladi.
        $username = env('ADMIN_USERNAME', 'admin');
        $password = env('ADMIN_PASSWORD');

        if (!$password) {
            $password = Str::random(16);

            echo PHP_EOL . '  Admin hisobi yaratildi:' . PHP_EOL
                . '    login: ' . $username . PHP_EOL
                . '    parol: ' . $password . PHP_EOL
                . '  Bu parolni saqlab qo\'ying — u boshqa ko\'rsatilmaydi.' . PHP_EOL . PHP_EOL;
        }

        DB::table('users')->insert([
            'name' => 'Admin',
            'username' => $username,
            'password' => Hash::make($password),
            'role' => 'admin'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
