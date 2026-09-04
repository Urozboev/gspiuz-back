<?php

namespace Tests\Feature;

use App\Models\Appeal;
use App\Models\Application;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Saytdagi ommaviy formalar: murojaat va bogʻlanish.
 *
 * Bular yagona autentifikatsiyasiz yozuv nuqtalari, shuning uchun
 * validatsiya va nosozlikka chidamlilik alohida tekshiriladi.
 */
class PublicFormsTest extends TestCase
{
    private array $trash = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Telegramga haqiqiy soʻrov ketmasin.
        Http::fake();
    }

    protected function tearDown(): void
    {
        foreach ($this->trash as $model) {
            $model->forceDelete();
        }

        parent::tearDown();
    }

    private function url(string $path): string
    {
        return '/' . trim((string) config('api.prefix'), '/') . $path;
    }

    public function test_appeal_is_accepted_and_returns_a_ticket(): void
    {
        $response = $this->postJson($this->url('/murojaat'), [
            'type'         => array_key_first(Appeal::types()),
            'name'         => 'Sinov Foydalanuvchi',
            'phone_number' => '+998 90 123 45 67',
            'email'        => 'sinov@example.com',
            'message'      => 'Bu sinov murojaati.',
        ]);

        $response->assertSuccessful();

        $ticket = $response->json('data.ticket') ?? $response->json('ticket');

        $this->assertNotEmpty($ticket, 'Murojaat raqami qaytmadi');

        $appeal = Appeal::where('ticket', $ticket)->first();

        $this->assertNotNull($appeal, 'Murojaat saqlanmadi');
        $this->trash[] = $appeal;

        // Raqam bo'yicha holatini ko'rish mumkin bo'lishi kerak.
        $this->getJson($this->url('/murojaat/' . $ticket))->assertOk();
    }

    public function test_appeal_requires_name_and_message(): void
    {
        $this->postJson($this->url('/murojaat'), [
            'type' => array_key_first(Appeal::types()),
        ])->assertStatus(422);
    }

    public function test_appeal_rejects_an_unknown_type(): void
    {
        $this->postJson($this->url('/murojaat'), [
            'type'    => 'bunday-tur-yoq',
            'name'    => 'Sinov',
            'message' => 'Matn',
        ])->assertStatus(422);
    }

    public function test_appeal_rejects_a_dangerous_file(): void
    {
        $this->postJson($this->url('/murojaat'), [
            'type'    => array_key_first(Appeal::types()),
            'name'    => 'Sinov',
            'message' => 'Matn',
            'file'    => UploadedFile::fake()->create('zararli.php', 10, 'application/x-php'),
        ])->assertStatus(422);
    }

    public function test_unknown_ticket_returns_404(): void
    {
        $this->getJson($this->url('/murojaat/YOQ-BUNDAY-RAQAM'))->assertNotFound();
    }

    public function test_contact_form_saves_the_message(): void
    {
        $name = 'Aloqa sinovi ' . uniqid();

        $response = $this->postJson($this->url('/contacts'), [
            'name'         => $name,
            'phone_number' => '+998 90 000 00 00',
            'message'      => 'Sinov xabari',
        ]);

        $response->assertStatus(201);

        $application = Application::where('name', $name)->first();

        $this->assertNotNull($application, 'Ariza saqlanmadi');
        $this->trash[] = $application;
    }

    public function test_contact_form_requires_name_and_message(): void
    {
        $this->postJson($this->url('/contacts'), ['phone_number' => '+998'])
            ->assertStatus(422);
    }

    /**
     * Telegram javob bermasa ham foydalanuvchining xabari saqlanishi va
     * unga xatolik koʻrsatilmasligi kerak.
     */
    public function test_contact_form_survives_a_telegram_outage(): void
    {
        Http::fake(fn () => throw new \RuntimeException('Telegram ishlamayapti'));

        $name = 'Telegram sinovi ' . uniqid();

        $response = $this->postJson($this->url('/contacts'), [
            'name'    => $name,
            'message' => 'Sinov xabari',
        ]);

        $response->assertStatus(201);

        $application = Application::where('name', $name)->first();

        $this->assertNotNull($application, 'Telegram nosozligi xabarni yoʻqotmasligi kerak');
        $this->trash[] = $application;
    }
}
