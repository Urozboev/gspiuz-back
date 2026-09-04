<?php

namespace Tests\Feature;

use App\Models\SiteInfo;
use App\Models\User;
use Tests\TestCase;

/**
 * "Sayt maʼlumotlari" formasini saqlash.
 *
 * Bu forma butun sayt boʻylab ishlatiladigan qiymatlarni saqlaydi: logotip,
 * manzil, aloqa, rekvizitlar, shiorlar, qabul sanalari. `yt_url` ustuni
 * jadvalda boʻlmagani uchun u har safar 500 xatosi bilan yiqilardi.
 */
class SiteInfoSaveTest extends TestCase
{
    public function test_site_info_form_saves_without_errors(): void
    {
        $admin = User::where('role', 'admin')->orderBy('id')->firstOrFail();
        $siteInfo = SiteInfo::first();

        // Xom qiymatlar: cast qilingan massivni qaytarib yozish JSON ni
        // ikkinchi marta kodlaydi va yozuvni shishirib yuboradi.
        $before = collect($siteInfo->getAttributes())->except('id')->all();

        $response = $this->actingAs($admin)->post('/admin/site_infos', [
            'title'        => ['uz' => 'Sinov nomi', 'ru' => 'Sinov', 'en' => 'Test'],
            'desc'         => ['uz' => 'Tavsif', 'ru' => 'Tavsif', 'en' => 'Desc'],
            'address'      => ['uz' => 'Guliston', 'ru' => 'Guliston', 'en' => 'Gulistan'],
            'phone_number' => '+998 67 225 40 60',
            'email'        => 'info@gspi.uz',
            'tagline'      => ['uz' => 'Shior', 'ru' => 'Shior', 'en' => 'Tagline'],
            'slogan'       => ['uz' => 'Qoʻshimcha', 'ru' => 'Qoʻshimcha', 'en' => 'Slogan'],
        ]);

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');

        $siteInfo->refresh();

        $this->assertSame('Sinov nomi', $siteInfo->title['uz']);
        $this->assertSame('Shior', $siteInfo->tagline['uz']);

        // Avvalgi holatga qaytaramiz.
        \Illuminate\Support\Facades\DB::table('site_infos')->where('id', $siteInfo->id)->update($before);
    }

    public function test_video_link_field_is_optional(): void
    {
        $admin = User::where('role', 'admin')->orderBy('id')->firstOrFail();
        $siteInfo = SiteInfo::first();
        $before = collect($siteInfo->getAttributes())->except('id')->all();

        // `yt_url` yuborilmaydi — forma baribir saqlanishi kerak.
        $this->actingAs($admin)->post('/admin/site_infos', [
            'title' => ['uz' => 'Videosiz sinov', 'ru' => 'X', 'en' => 'X'],
        ])->assertStatus(302);

        $siteInfo->refresh();
        $this->assertSame('Videosiz sinov', $siteInfo->title['uz']);

        \Illuminate\Support\Facades\DB::table('site_infos')->where('id', $siteInfo->id)->update($before);
    }
}
