<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Sayt har bir sahifada so'raydigan uchta narsa — bitta javobda.
 *
 * Header va layout `siteinfo`, `menu` va `popups` ni talab qiladi, ya'ni
 * ular har bir sahifa yuklanishida uch marta alohida so'raladi. Bu yerda
 * ular birlashtiriladi: sahifa boshiga uchta so'rov o'rniga bitta.
 *
 * Mazmuni o'zgarmaydi — quyidagi uchtasi bilan bir xil:
 *   GET /siteinfo
 *   GET /menu
 *   GET /popups
 */
class BootstrapController extends Controller
{
    public function __invoke(
        Request $request,
        ApiController $siteInfo,
        MenuMontroller $menu,
        NoticeController $notices
    ) {
        return response()->json([
            'siteinfo' => $this->payload($siteInfo->getCompany()),
            'menu'     => $this->payload($menu->get_menu($request)),
            'popups'   => $this->payload($notices->popups()),
        ]);
    }

    /**
     * Kontroller javobidan ma'lumotni ajratib oladi.
     *
     * Uchala endpoint turli shaklda qaytaradi: `siteinfo` — {data: {...}},
     * `menu` — to'g'ridan-to'g'ri ro'yxat, `popups` — {data: [...]}.
     * Frontend uchun ularni bir xil ko'rinishga keltiramiz.
     */
    private function payload($response)
    {
        $decoded = json_decode($response->getContent(), true);

        if (is_array($decoded) && array_key_exists('data', $decoded) && count($decoded) <= 2) {
            return $decoded['data'];
        }

        return $decoded;
    }
}
