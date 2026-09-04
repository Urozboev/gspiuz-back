<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Modellarning `fillable` roʻyxati haqiqiy jadval ustunlariga mos kelishi.
 *
 * `site_infos.yt_url` xatosi aynan shundan kelib chiqqan edi: model, admin
 * formasi va API ustunni kutardi, jadvalda esa u yoʻq edi. Natijada "Sayt
 * maʼlumotlari" formasini saqlash har safar 500 xatosi bilan tugardi va
 * logotip, manzil, rekvizitlarni umuman saqlab boʻlmasdi.
 *
 * Bunday nomuvofiqlik faqat forma yuborilganda bilinadi — shuning uchun
 * uni test bosqichida ushlaymiz.
 */
class ModelSchemaTest extends TestCase
{
    public function test_every_fillable_field_exists_as_a_column(): void
    {
        $problems = [];
        $checked = 0;

        foreach (glob(app_path('Models/*.php')) as $file) {
            $class = 'App\\Models\\' . basename($file, '.php');

            if (!class_exists($class)) {
                continue;
            }

            $model = new $class();

            if (!$model instanceof Model) {
                continue;
            }

            $table = $model->getTable();

            if (!Schema::hasTable($table)) {
                continue;
            }

            $columns = Schema::getColumnListing($table);

            if ($columns === []) {
                continue;
            }

            $checked++;

            $missing = array_diff($model->getFillable(), $columns);

            if ($missing !== []) {
                $problems[] = sprintf(
                    '%s (%s): %s',
                    class_basename($class),
                    $table,
                    implode(', ', $missing)
                );
            }
        }

        $this->assertGreaterThan(30, $checked, 'Modellar topilmadi');

        $this->assertSame(
            [],
            $problems,
            "Modelda bor, jadvalda yoʻq ustunlar:\n" . implode("\n", $problems)
        );
    }

    /**
     * `casts` da eʼlon qilingan maydon ham jadvalda boʻlishi kerak —
     * aks holda qiymat jimgina yoʻqoladi.
     */
    public function test_every_cast_field_exists_as_a_column(): void
    {
        $problems = [];

        foreach (glob(app_path('Models/*.php')) as $file) {
            $class = 'App\\Models\\' . basename($file, '.php');

            if (!class_exists($class)) {
                continue;
            }

            $model = new $class();

            if (!$model instanceof Model || !Schema::hasTable($model->getTable())) {
                continue;
            }

            $columns = Schema::getColumnListing($model->getTable());

            if ($columns === []) {
                continue;
            }

            foreach (array_keys($model->getCasts()) as $field) {
                // Laravel o'zi qo'shadigan va hisoblanadigan maydonlar.
                if ($field === $model->getKeyName() || Str::contains($field, '.')) {
                    continue;
                }

                if (!in_array($field, $columns, true)) {
                    $problems[] = class_basename($class) . ' (' . $model->getTable() . '): ' . $field;
                }
            }
        }

        $this->assertSame(
            [],
            $problems,
            "Casts da bor, jadvalda yoʻq maydonlar:\n" . implode("\n", $problems)
        );
    }
}
