<?php

namespace Database\Seeders;

use App\Models\SiteInfo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

/**
 * Institut logotipini saytga va admin panelga o'rnatadi.
 *
 * Manba: public/assets/img/gspi-logo.png (gspi.uz dan olingan rasmiy gerb).
 * Undan admin paneldagi yuklash tartibiga mos uch o'lcham yasaladi va
 * `site_infos` yozuvidagi logo / logo_dark / favicon maydonlari to'ldiriladi.
 *
 *   php artisan db:seed --class=GspiBrandingSeeder
 */
class GspiBrandingSeeder extends Seeder
{
    private const SOURCE = 'assets/img/gspi-logo.png';

    private const FILE_NAME = 'gspi-logo.png';

    public function run(): void
    {
        $source = public_path(self::SOURCE);

        if (!is_file($source)) {
            $this->command?->warn('Logotip topilmadi: ' . self::SOURCE);

            return;
        }

        $this->publishSizes($source);
        $this->attachToSiteInfo();
    }

    /** Admin paneldagi yuklash bilan bir xil: asl, 600 va 200. */
    private function publishSizes(string $source): void
    {
        foreach ([null => null, '600' => 600, '200' => 200] as $folder => $width) {
            $directory = public_path('upload/images' . ($folder ? '/' . $folder : ''));
            File::ensureDirectoryExists($directory, 0755, true);

            $target = $directory . '/' . self::FILE_NAME;

            if ($width === null) {
                File::copy($source, $target);

                continue;
            }

            Image::make($source)
                ->resize($width, null, fn ($constraint) => $constraint->aspectRatio())
                ->save($target, $width === 200 ? 60 : 80);
        }

        $this->command?->info('Logotip uch o\'lchamda upload/images ga joylandi.');
    }

    /** Sayt ma'lumotlaridagi logo maydonlari. */
    private function attachToSiteInfo(): void
    {
        $siteInfo = SiteInfo::first();

        if (!$siteInfo) {
            $this->command?->warn('site_infos bo\'sh — logotip biriktirilmadi.');

            return;
        }

        $siteInfo->forceFill([
            'logo'      => self::FILE_NAME,
            'logo_dark' => self::FILE_NAME,
            'favicon'   => self::FILE_NAME,
            // Sayt boshi va pastidagi shiorlar. Avval frontend kodida
            // uch tilda yozib qoʻyilgan edi.
            'tagline' => $siteInfo->tagline ?: [
                'uz' => 'Sirdaryo yoshlari taʼlim va taraqqiyot yoʻlida!',
                'ru' => 'Молодежь Сырдарьи на пути к образованию и прогрессу!',
                'en' => 'The youth of Sirdaryo on the path of education and progress!',
            ],
            'slogan' => $siteInfo->slogan ?: [
                'uz' => '2022-yildan pedagogika xizmatidamiz',
                'ru' => 'С 2022 года готовим педагогические кадры',
                'en' => 'Training teachers since 2022',
            ],
        ])->save();

        $this->command?->info('Logotip sayt ma\'lumotlariga biriktirildi.');
    }
}
