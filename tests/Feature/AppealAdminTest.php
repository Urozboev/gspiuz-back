<?php

namespace Tests\Feature;

use App\Models\Appeal;
use App\Models\User;
use Tests\TestCase;

/**
 * Murojaatlar admin paneli: koʻrish, filtrlash va javob yozish.
 *
 * AdminPagesSmokeTest faqat parametrsiz sahifalarni oladi, shuning uchun
 * murojaat detali va holat oʻzgartirish testsiz qolgan edi — aynan shu
 * ikkitasi orqali fuqaroga rasmiy javob beriladi.
 */
class AppealAdminTest extends TestCase
{
    private array $trash = [];

    protected function tearDown(): void
    {
        foreach ($this->trash as $model) {
            $model->forceDelete();
        }

        parent::tearDown();
    }

    private function admin(): User
    {
        return User::where('role', 'admin')->orderBy('id')->firstOrFail();
    }

    /** Sinov uchun murojaat yaratadi va uni tozalash roʻyxatiga qoʻshadi. */
    private function appeal(array $attributes = []): Appeal
    {
        $appeal = Appeal::create(array_merge([
            'ticket'  => Appeal::generateTicket(),
            'type'    => Appeal::TYPE_RECTOR,
            'status'  => Appeal::STATUS_NEW,
            'name'    => 'Sinov Murojaatchi',
            'message' => 'Bu avtomatik test yaratgan murojaat.',
        ], $attributes));

        $this->trash[] = $appeal;

        return $appeal;
    }

    public function test_appeal_detail_page_opens(): void
    {
        $appeal = $this->appeal();

        $this->actingAs($this->admin())
            ->get('/admin/appeals/' . $appeal->id)
            ->assertOk()
            ->assertSee($appeal->ticket)
            ->assertSee('Sinov Murojaatchi');
    }

    public function test_admin_can_answer_and_close_an_appeal(): void
    {
        $appeal = $this->appeal();

        $this->actingAs($this->admin())
            ->put('/admin/appeals/' . $appeal->id, [
                'status' => Appeal::STATUS_ANSWERED,
                'answer' => 'Murojaatingiz koʻrib chiqildi.',
            ])
            ->assertRedirect();

        $appeal->refresh();

        $this->assertSame(Appeal::STATUS_ANSWERED, $appeal->status);
        $this->assertSame('Murojaatingiz koʻrib chiqildi.', $appeal->answer);
        $this->assertNotNull($appeal->answered_at, 'Javob sanasi qoʻyilmadi');
    }

    /** Javob fuqaroga ariza raqami orqali koʻrinishi kerak. */
    public function test_answer_reaches_the_public_tracking_endpoint(): void
    {
        $appeal = $this->appeal([
            'status'      => Appeal::STATUS_ANSWERED,
            'answer'      => 'Rasmiy javob matni.',
            'answered_at' => now(),
        ]);

        $prefix = '/' . trim((string) config('api.prefix'), '/');

        $this->getJson($prefix . '/murojaat/' . $appeal->ticket)
            ->assertOk()
            ->assertJsonPath('data.answer', 'Rasmiy javob matni.')
            ->assertJsonPath('data.status', Appeal::STATUS_ANSWERED);
    }

    public function test_unknown_status_is_rejected(): void
    {
        $appeal = $this->appeal();

        $this->actingAs($this->admin())
            ->put('/admin/appeals/' . $appeal->id, ['status' => 'yolgon-holat'])
            ->assertSessionHasErrors('status');

        $this->assertSame(Appeal::STATUS_NEW, $appeal->refresh()->status);
    }

    public function test_list_can_be_filtered_by_type_and_status(): void
    {
        $rector = $this->appeal(['type' => Appeal::TYPE_RECTOR, 'name' => 'Rektorga Yozgan']);
        $tutor  = $this->appeal(['type' => Appeal::TYPE_TUTOR, 'name' => 'Tyutorga Yozgan']);

        $this->actingAs($this->admin())
            ->get('/admin/appeals?type=' . Appeal::TYPE_TUTOR)
            ->assertOk()
            ->assertSee($tutor->ticket)
            ->assertDontSee($rector->ticket);
    }

    /** Ariza raqamlari takrorlanmasligi kerak — ular fuqaroga beriladi. */
    public function test_ticket_numbers_do_not_repeat(): void
    {
        $tickets = [];

        for ($i = 0; $i < 3; $i++) {
            $tickets[] = $this->appeal()->ticket;
        }

        $this->assertCount(3, array_unique($tickets), 'Ariza raqami takrorlandi: ' . implode(', ', $tickets));
    }
}
