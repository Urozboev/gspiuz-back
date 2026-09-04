<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modal xabardagi tugma yozuvi.
 *
 * `action` — tugma koʻrsatilsinmi degan bayroq. Yozuvning oʻzi shu paytgacha
 * frontendda qatʼiy "Batafsil" edi. Endi admin uni oʻzi tanlaydi:
 * "Batafsil oʻqish", "Roʻyxatdan oʻtish" va hokazo — uch tilda.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reks', function (Blueprint $table) {
            $table->json('action_label')->nullable()->after('action');
        });
    }

    public function down(): void
    {
        Schema::table('reks', function (Blueprint $table) {
            $table->dropColumn('action_label');
        });
    }
};
