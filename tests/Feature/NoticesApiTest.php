<?php

namespace Tests\Feature;

use App\Models\Employ;
use App\Models\EmployMeta;
use App\Models\Rek;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * GET /popups — saytga kirilganda ochiladigan modal xabarlar.
 * GET /birthdays — bugun tugʻilgan kuni boʻlgan xodimlar.
 */
class NoticesApiTest extends TestCase
{
    /** Test oʻzi yaratgan xodim — oxirida oʻchiriladi. */
    private ?Employ $seeded = null;

    private ?EmployMeta $seededMeta = null;

    protected function tearDown(): void
    {
        $this->seededMeta?->forceDelete();
        $this->seeded?->forceDelete();

        parent::tearDown();
    }

    private function api(string $path, string $locale = 'uz')
    {
        $prefix = trim((string) config('api.prefix'), '/');

        return $this->withHeaders(['Accept-Language' => $locale])->getJson('/' . $prefix . $path);
    }

    /**
     * Tugʻilgan kuni bugun boʻlgan xodim tayyorlaydi.
     *
     * Mavjud xodimga tayanmaydi: namunaviy maʼlumot tozalangandan keyin
     * baza boʻsh boʻlishi mumkin, test esa baribir ishlashi kerak.
     */
    private function employeeBornToday(int $age = 45): Employ
    {
        $birthday = now()->subYears($age)->format('Y-m-d');

        $existing = Employ::whereHas('employMeta', fn ($q) => $q->where('active', 1))->first();

        if ($existing) {
            $existing->forceFill(['birthday' => $birthday])->save();

            return $existing;
        }

        $department = DB::table('departments')->orderBy('id')->first();

        $this->assertNotNull($department, 'Boʻlim topilmadi — tuzilma boʻsh');

        $name = ['uz' => 'Sinov', 'ru' => 'Sinov', 'en' => 'Sinov'];

        $this->seeded = Employ::create([
            'first_name' => $name,
            'last_name' => ['uz' => 'Tugʻilgan', 'ru' => 'Tugʻilgan', 'en' => 'Tugʻilgan'],
            'surname' => $name,
            'slug' => 'sinov-tugilgan-' . uniqid(),
            'birthday' => $birthday,
        ]);

        $this->seededMeta = EmployMeta::create([
            'employ_id'       => $this->seeded->id,
            'department_id'   => $department->id,
            'position_id'     => 4,
            'employ_staff_id' => 1,
            'employ_form_id'  => 1,
            'employ_type_id'  => 2,
            'slug'            => 'sinov-tayinlov-' . uniqid(),
            'active'          => 1,
            'order'           => 1,
        ]);

        return $this->seeded;
    }

    public function test_popups_return_expected_shape(): void
    {
        $this->api('/popups')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'title', 'desc', 'image' => ['lg', 'md', 'sm'], 'url', 'action', 'starts_at', 'ends_at']],
            ]);
    }

    public function test_popup_appears_and_disappears_on_schedule(): void
    {
        $today = now();

        $future = Rek::create([
            'title'     => ['uz' => 'Kelajakdagi xabar'],
            'active'    => 1,
            'starts_at' => $today->copy()->addDays(3)->toDateString(),
            'ends_at'   => $today->copy()->addDays(10)->toDateString(),
        ]);

        $past = Rek::create([
            'title'     => ['uz' => 'Muddati tugagan xabar'],
            'active'    => 1,
            'starts_at' => $today->copy()->subDays(10)->toDateString(),
            'ends_at'   => $today->copy()->subDays(3)->toDateString(),
        ]);

        $current = Rek::create([
            'title'     => ['uz' => 'Hozirgi xabar'],
            'active'    => 1,
            'starts_at' => $today->copy()->subDay()->toDateString(),
            'ends_at'   => $today->copy()->addDay()->toDateString(),
        ]);

        $switchedOff = Rek::create([
            'title'  => ['uz' => 'Oʻchirilgan xabar'],
            'active' => 0,
        ]);

        $titles = collect($this->api('/popups')->assertOk()->json('data'))->pluck('title');

        $this->assertContains('Hozirgi xabar', $titles->all());
        $this->assertNotContains('Kelajakdagi xabar', $titles->all(), 'Muddati kelmagan xabar chiqmasligi kerak');
        $this->assertNotContains('Muddati tugagan xabar', $titles->all(), 'Muddati tugagan xabar chiqmasligi kerak');
        $this->assertNotContains('Oʻchirilgan xabar', $titles->all(), 'Oʻchirilgan xabar chiqmasligi kerak');

        foreach ([$future, $past, $current, $switchedOff] as $notice) {
            $notice->delete();
        }
    }

    public function test_birthdays_return_only_todays_people(): void
    {
        // Bugun hech kimning tugʻilgan kuni boʻlmasligi mumkin, shuning
        // uchun test oʻzi bitta yozuv tayyorlaydi — natija kalendarga
        // bogʻliq boʻlib qolmasin.
        $employ = $this->employeeBornToday(45);
        $original = $employ->birthday;

        $response = $this->api('/birthdays');

        $response->assertOk()->assertJsonStructure([
            'data' => [['id', 'slug', 'full_name', 'position', 'department', 'photo', 'age']],
        ]);

        $today = now()->format('m-d');

        foreach ($response->json('data') as $person) {
            $this->assertSame($today, $person['date'], 'Faqat bugungi tugʻilgan kunlar qaytishi kerak');
            $this->assertIsInt($person['age']);
        }

        $this->assertSame(45, collect($response->json('data'))->firstWhere('id', $employ->id)['age']);

        $employ->forceFill(['birthday' => $original])->save();
    }

    public function test_birthdays_window_includes_more_people(): void
    {
        $today = count($this->api('/birthdays')->json('data'));
        $month = count($this->api('/birthdays?days=31')->json('data'));

        $this->assertGreaterThanOrEqual($today, $month);
    }

    public function test_employees_without_a_birthday_are_skipped(): void
    {
        // Roʻyxat boʻsh boʻlmasligi uchun kamida bitta sanali xodim kerak.
        $this->employeeBornToday();

        $this->assertGreaterThan(0, Employ::whereNotNull('birthday')->count());

        // Yil bo'yi oynasi hamma sanani qamraydi, lekin sanasizlar chiqmaydi.
        $all = $this->api('/birthdays?days=31')->json('data');

        foreach ($all as $person) {
            $this->assertNotNull($person['date']);
        }
    }
}
