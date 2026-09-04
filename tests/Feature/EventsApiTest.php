<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\SiteInfo;
use App\Models\User;
use Tests\TestCase;

/**
 * Tadbirlar kalendari va hujjat qabuli hisoblagichi.
 */
class EventsApiTest extends TestCase
{
    private array $trash = [];

    protected function tearDown(): void
    {
        foreach ($this->trash as $model) {
            $model->forceDelete();
        }

        parent::tearDown();
    }

    private function api(string $path, string $locale = 'uz')
    {
        $prefix = trim((string) config('api.prefix'), '/');

        return $this->withHeaders(['Accept-Language' => $locale])->getJson('/' . $prefix . $path);
    }

    private function makeEvent(array $attributes = []): Event
    {
        $event = Event::create(array_merge([
            'title'    => ['uz' => 'Sinov tadbiri', 'ru' => 'Тестовое событие', 'en' => 'Test event'],
            'desc'     => ['uz' => 'Tavsif', 'ru' => 'Описание', 'en' => 'Description'],
            'location' => ['uz' => 'Katta zal', 'ru' => 'Большой зал', 'en' => 'Main hall'],
            'slug'     => 'sinov-tadbiri-' . uniqid(),
            'date'     => now()->addDays(3)->toDateString(),
            'time'     => '14:00',
            'type'     => 'konferensiya',
            'active'   => 1,
        ], $attributes));

        $this->trash[] = $event;

        return $event;
    }

    public function test_events_endpoint_returns_the_agreed_shape(): void
    {
        $this->makeEvent();

        $this->api('/events')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [[
                    'id', 'slug', 'title', 'desc', 'date', 'end_date',
                    'time', 'location', 'type', 'url', 'image',
                ]],
                'meta' => ['total', 'per_page', 'current_page', 'last_page'],
            ]);
    }

    public function test_events_are_sorted_by_date(): void
    {
        $this->makeEvent(['date' => now()->addDays(20)->toDateString()]);
        $this->makeEvent(['date' => now()->addDays(2)->toDateString()]);

        $dates = collect($this->api('/events')->assertOk()->json('data'))->pluck('date')->all();

        $sorted = $dates;
        sort($sorted);

        $this->assertSame($sorted, $dates, 'Tadbirlar sana boʻyicha saralanishi kerak');
    }

    /**
     * Kalendar orqaga varaqlanadi, shuning uchun oʻtgan tadbirlar ham
     * qaytarilishi kerak — kamida joriy yildan.
     */
    public function test_past_events_are_still_returned(): void
    {
        $past = $this->makeEvent([
            'date'  => now()->startOfYear()->addDays(10)->toDateString(),
            'title' => ['uz' => 'Oʻtgan tadbir ' . uniqid()],
        ]);

        $titles = collect($this->api('/events')->assertOk()->json('data'))->pluck('title');

        $this->assertContains($past->title['uz'], $titles->all(), 'Oʻtgan tadbir tushib qolmasligi kerak');
    }

    public function test_inactive_events_are_hidden(): void
    {
        $hidden = $this->makeEvent([
            'active' => 0,
            'title'  => ['uz' => 'Yashirin tadbir ' . uniqid()],
        ]);

        $titles = collect($this->api('/events')->assertOk()->json('data'))->pluck('title');

        $this->assertNotContains($hidden->title['uz'], $titles->all());
        $this->api('/events/' . $hidden->slug)->assertNotFound();
    }

    /** @dataProvider locales */
    public function test_event_text_follows_accept_language(string $locale, string $expected): void
    {
        $event = $this->makeEvent();

        $this->api('/events/' . $event->slug, $locale)
            ->assertOk()
            ->assertJsonPath('data.title', $expected);
    }

    public static function locales(): array
    {
        return [
            'uz' => ['uz', 'Sinov tadbiri'],
            'ru' => ['ru', 'Тестовое событие'],
            'en' => ['en', 'Test event'],
        ];
    }

    public function test_multi_day_event_keeps_its_end_date(): void
    {
        $event = $this->makeEvent([
            'date'     => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(8)->toDateString(),
        ]);

        $this->api('/events/' . $event->slug)
            ->assertOk()
            ->assertJsonPath('data.end_date', $event->end_date->toDateString());
    }

    public function test_unknown_event_returns_404(): void
    {
        $this->api('/events/bunday-tadbir-yoq')->assertNotFound();
    }

    public function test_event_created_in_admin_appears_on_the_site(): void
    {
        $admin = User::where('role', 'admin')->orderBy('id')->firstOrFail();
        $name = 'Admin tadbiri ' . uniqid();

        $this->actingAs($admin)->post('/admin/events', [
            'title'    => ['uz' => $name, 'ru' => $name, 'en' => $name],
            'desc'     => ['uz' => 'Matn', 'ru' => 'Matn', 'en' => 'Matn'],
            'location' => ['uz' => 'Zal', 'ru' => 'Zal', 'en' => 'Hall'],
            'date'     => now()->addDays(6)->toDateString(),
            'time'     => '10:00',
            'active'   => 1,
        ])->assertStatus(302);

        $event = Event::where('title->uz', $name)->first();

        $this->assertNotNull($event, 'Tadbir yaratilmadi');
        $this->trash[] = $event;

        $titles = collect($this->api('/events')->assertOk()->json('data'))->pluck('title');

        $this->assertContains($name, $titles->all(), 'Tadbir saytda koʻrinmadi');
    }

    public function test_admin_rejects_an_end_date_before_the_start(): void
    {
        $admin = User::where('role', 'admin')->orderBy('id')->firstOrFail();
        $name = 'Notoʻgʻri sana ' . uniqid();

        $this->actingAs($admin)->post('/admin/events', [
            'title'    => ['uz' => $name],
            'date'     => now()->addDays(10)->toDateString(),
            'end_date' => now()->toDateString(),
        ]);

        $this->assertNull(Event::where('title->uz', $name)->first());
    }

    public function test_admission_countdown_fields_are_served(): void
    {
        $siteInfo = SiteInfo::first();

        $original = [
            'admission_starts_at' => $siteInfo->admission_starts_at,
            'admission_ends_at'   => $siteInfo->admission_ends_at,
            'admission_url'       => $siteInfo->admission_url,
        ];

        $siteInfo->forceFill([
            'admission_starts_at' => '2026-06-20',
            'admission_ends_at'   => '2026-07-15',
            'admission_url'       => 'https://qabul.gspi.uz',
        ])->save();

        $this->api('/siteinfo')
            ->assertOk()
            ->assertJsonPath('data.admission_starts_at', '2026-06-20')
            ->assertJsonPath('data.admission_ends_at', '2026-07-15')
            ->assertJsonPath('data.admission_url', 'https://qabul.gspi.uz');

        // Bo'sh bo'lsa null qaytishi kerak — frontend blokni yashiradi.
        $siteInfo->forceFill($original)->save();

        $this->api('/siteinfo')
            ->assertOk()
            ->assertJsonPath('data.admission_starts_at', null);
    }
}
