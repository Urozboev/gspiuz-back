<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hujjat qabuli muddati.
 *
 * Saytdagi "Hujjat qabuliga N kun qoldi" hisoblagichi shu sanalardan
 * hisoblanadi. Ikkalasi ham bo'sh bo'lsa blok umuman ko'rinmaydi —
 * ya'ni qabul tugagach hech kim hech narsani o'chirishi shart emas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->date('admission_starts_at')->nullable()->after('slogan');
            $table->date('admission_ends_at')->nullable()->after('admission_starts_at');
            $table->string('admission_url')->nullable()->after('admission_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->dropColumn(['admission_starts_at', 'admission_ends_at', 'admission_url']);
        });
    }
};
