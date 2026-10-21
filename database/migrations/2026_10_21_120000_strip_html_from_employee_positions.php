<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Xodim lavozimidagi HTML teglarini olib tashlaydi.
 *
 * Eski saytdan koʻchirilgan lavozimlar `<p><strong>Kafedra mudiri</strong></p>`
 * koʻrinishida kelgan. Lavozim — bir qatorli yorliq: admin panelda u
 * oddiy matn maydonida turadi va foydalanuvchi teglarni xom holda
 * koʻrardi. Sayt ularni baribir olib tashlaydi, shuning uchun koʻrinishda
 * hech narsa oʻzgarmaydi.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('employs')->whereNotNull('position')->orderBy('id')->each(function ($row) {
            $position = json_decode((string) $row->position, true);

            if (!is_array($position)) {
                return;
            }

            $clean = array_map(function ($value) {
                $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

                return trim(preg_replace('~\s+~u', ' ', str_replace("\u{00A0}", ' ', $text)) ?? '');
            }, $position);

            if ($clean !== $position) {
                DB::table('employs')->where('id', $row->id)->update([
                    'position' => json_encode($clean, JSON_UNESCAPED_UNICODE),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Teglarni qaytarib boʻlmaydi — ular maʼno tashimas edi.
    }
};
