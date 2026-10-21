<?php

namespace App\Http\Controllers;

use App\Models\Lang;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $main_lang;

    function __construct()
    {
        $this->main_lang = Lang::where('is_main', 1)
            ->firstOrFail();
    }

    /**
     * Formadan kelgan qiymatlarni bazaga yozishga tayyorlaydi.
     *
     * Uch tilli maydon toʻliq boʻsh kelsa (`{"uz":"","ru":"","en":""}`) —
     * `null` qilib yoziladi. Aks holda boʻsh maydon "toʻldirilgan" deb
     * hisoblanib, saytda boʻsh blok sifatida chiqishi mumkin edi.
     * Boʻsh tanlov (`""`) ham `null` boʻladi.
     *
     * @param  array<string>  $fields  Tozalanadigan maydonlar
     */
    protected function blankToNull(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];

            if (is_array($value)) {
                $filled = array_filter($value, fn ($item) => trim(strip_tags((string) $item)) !== '');
                $data[$field] = $filled === [] ? null : $value;
            } elseif (is_string($value) && trim($value) === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }
}
