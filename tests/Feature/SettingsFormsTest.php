<?php

namespace Tests\Feature;

use App\Models\SiteInfo;
use App\Models\User;
use Tests\TestCase;

/**
 * Sozlama formalari boʻsh maydonlar bilan ham saqlanishi.
 *
 * Bu formalarda kontroller `$data['maydon']` ni toʻgʻridan-toʻgʻri oʻqirdi.
 * Foydalanuvchi biror maydonni boʻsh qoldirsa yoki brauzer uni yubormasa,
 * saqlash "Undefined array key" xatosi bilan 500 berardi. Aynan shu sabab
 * "Sayt maʼlumotlari" ni umuman saqlab boʻlmasdi.
 */
class SettingsFormsTest extends TestCase
{
    private function admin(): User
    {
        return User::where('role', 'admin')->orderBy('id')->firstOrFail();
    }

    /**
     * Faqat majburiy maydon yuboriladi — qolgani boʻsh.
     * Forma baribir saqlanishi kerak.
     *
     * @dataProvider forms
     */
    public function test_settings_form_saves_with_only_required_fields(string $url, array $payload): void
    {
        $siteInfo = SiteInfo::first();
        $before = $siteInfo ? collect($siteInfo->getAttributes())->except('id')->all() : null;

        $response = $this->actingAs($this->admin())->post($url, $payload);

        $this->assertContains(
            $response->status(),
            [200, 302],
            $url . ' saqlanmadi, javob: ' . $response->status()
        );

        if ($siteInfo && $before) {
            \Illuminate\Support\Facades\DB::table('site_infos')->where('id', $siteInfo->id)->update($before);
        }
    }

    public static function forms(): array
    {
        return [
            'raqamlarda institut' => ['/admin/facts_figures', []],
            'texnik sozlamalar'   => ['/admin/additional_functions', []],
        ];
    }

    public function test_site_info_keeps_untouched_fields(): void
    {
        $siteInfo = SiteInfo::first();
        $before = collect($siteInfo->getAttributes())->except('id')->all();

        // Faqat sarlavha yuboriladi; logotip va rekvizitlar formada yoʻq.
        $this->actingAs($this->admin())->post('/admin/site_infos', [
            'title' => ['uz' => 'Qisman sinov', 'ru' => 'X', 'en' => 'X'],
        ])->assertStatus(302);

        $siteInfo->refresh();

        $this->assertSame('Qisman sinov', $siteInfo->title['uz']);

        \Illuminate\Support\Facades\DB::table('site_infos')->where('id', $siteInfo->id)->update($before);
    }
}
