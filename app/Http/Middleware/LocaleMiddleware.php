<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use App\Models\Lang;

class LocaleMiddleware
{
    // Asosiy til
    protected static $mainLanguage = 'uz';

    // Tillar ro'yxati
    protected static $languages = [];

    public static function getLocaleFromHeader($request)
    {
        // Tillarni Lang modelidan olish
        self::$languages = Lang::pluck('code')->toArray();

        // Headerdan 'Accept-Language' ni olish
        $locale = $request->header('Accept-Language');

        // Agar headerda til ko'rsatilgan bo'lsa va u tillar ro'yxatida mavjud bo'lsa
        if ($locale && in_array($locale, self::$languages)) {
            return $locale; // Headerdan olingan til qaytariladi
        }

        return null;
    }

    public function handle($request, Closure $next)
    {
        // Foydalanuvchining tilini headerdan olish
        $locale = self::getLocaleFromHeader($request);

        // Agar til topilsa, uni o'rnatamiz
        if ($locale) {
            App::setLocale($locale);
        } else {
            // Aks holda asosiy tilni o'rnatamiz
            App::setLocale(self::$mainLanguage);
        }

        return $next($request); // So'rovni keyingi middleware ga uzatamiz
    }
}
