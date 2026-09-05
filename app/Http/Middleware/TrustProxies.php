<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Ishonchli proksilar.
     *
     * Sayt Kerio Control teskari proksisi ortida turadi: HTTPS'ni Kerio
     * tugatadi va backendga toza HTTP yuboradi. Proksi ishonchli deb
     * belgilanmasa, Laravel soʻrovni HTTP deb hisoblaydi va `url()` bilan
     * qurilgan manzillarni — rasm, fayl, qayta yoʻnaltirish — `http://`
     * qilib chiqaradi. HTTPS sahifada bunday manzillar brauzer tomonidan
     * bloklanadi.
     *
     * `.env` dagi `TRUSTED_PROXIES` da Kerio'ning ichki IP manzili
     * koʻrsatiladi. Bir nechtasini vergul bilan ajratish mumkin.
     * Boʻsh qoldirilsa hech qanday proksi ishonchli boʻlmaydi.
     */
    public function __construct()
    {
        $proxies = trim((string) config('app.trusted_proxies'));

        if ($proxies === '') {
            return;
        }

        // `*` — har qanday proksi. Faqat backend tashqi internetdan
        // toʻgʻridan-toʻgʻri yetib boʻlmaydigan boʻlsa ishlatish mumkin.
        $this->proxies = $proxies === '*'
            ? '*'
            : array_values(array_filter(array_map('trim', explode(',', $proxies))));
    }

    /**
     * Proksi qaysi sarlavhalar orqali aniqlanadi.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
