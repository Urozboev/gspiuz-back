<?php

namespace Tests\Feature;

use App\Models\SiteInfo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Telefon raqamlari admin paneldan boshqariladi.
 *
 * Saytda raqam uch joyda koʻrinadi: tepadagi sarlavha, "Call markaz"
 * yozuvi va pastki qism. Ilgari uchalasi bitta maydondan kelardi, va
 * formada namuna matn maydon ICHIGA yozilgani uchun boʻsh bazada u
 * haqiqiy raqam sifatida saqlanib ketishi mumkin edi.
 */
class PhoneNumbersTest extends TestCase
{
    private function admin(): User
    {
        return User::where('role', 'admin')->orderBy('id')->firstOrFail();
    }

    private function apiSiteInfo(): array
    {
        $prefix = trim((string) config('api.prefix'), '/');

        $json = $this->withHeaders(['Accept-Language' => 'uz'])
            ->getJson('/' . $prefix . '/siteinfo')
            ->assertOk()
            ->json();

        return $json['data'] ?? $json;
    }

    public function test_both_numbers_are_saved_and_returned(): void
    {
        $siteInfo = SiteInfo::first();
        $before = collect($siteInfo->getAttributes())->except('id')->all();

        try {
            $this->actingAs($this->admin())->post('/admin/site_infos', [
                'title' => ['uz' => 'Sinov', 'ru' => 'Sinov', 'en' => 'Test'],
                'phone_number' => '+998 67 225 40 60 | +998 67 225 40 61',
                'call_center' => '+998 55 500 00 00',
            ])->assertStatus(302)->assertSessionMissing('errors');

            $api = $this->apiSiteInfo();

            $this->assertSame('+998 67 225 40 60 | +998 67 225 40 61', $api['phone_number']);
            $this->assertSame('+998 55 500 00 00', $api['call_center']);
        } finally {
            DB::table('site_infos')->where('id', $siteInfo->id)->update($before);
        }
    }

    /** Call markaz ixtiyoriy: boʻsh yuborilsa `null` boʻlib qolishi kerak. */
    public function test_call_center_is_optional(): void
    {
        $siteInfo = SiteInfo::first();
        $before = collect($siteInfo->getAttributes())->except('id')->all();

        try {
            $this->actingAs($this->admin())->post('/admin/site_infos', [
                'title' => ['uz' => 'Sinov', 'ru' => 'Sinov', 'en' => 'Test'],
                'phone_number' => '+998 67 225 40 60',
                'call_center' => '',
            ])->assertStatus(302);

            $this->assertNull($this->apiSiteInfo()['call_center']);
        } finally {
            DB::table('site_infos')->where('id', $siteInfo->id)->update($before);
        }
    }

    /** Formada namuna matn maydon ichiga emas, `placeholder` da boʻlishi kerak. */
    public function test_form_does_not_prefill_a_fake_number(): void
    {
        $html = $this->actingAs($this->admin())->get('/admin/site_infos')->assertOk()->getContent();

        $this->assertStringNotContainsString('+1 234 56 78', $html, 'Namuna raqam maydon ichida qolgan');
        $this->assertStringContainsString('name="call_center"', $html, 'Call markaz maydoni formada yoʻq');
    }
}
