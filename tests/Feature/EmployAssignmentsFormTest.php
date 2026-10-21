<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employ;
use App\Models\EmployMeta;
use App\Models\User;
use Tests\TestCase;

/**
 * Xodim formasidagi "Ish joylari": bitta xodim — bir nechta boʻlim.
 */
class EmployAssignmentsFormTest extends TestCase
{
    private array $departments = [];

    private array $people = [];

    protected function tearDown(): void
    {
        EmployMeta::withTrashed()->whereIn('employ_id', $this->people)->forceDelete();
        Employ::withTrashed()->whereIn('id', $this->people)->forceDelete();
        Department::withTrashed()->whereIn('id', $this->departments)->forceDelete();

        parent::tearDown();
    }

    private function admin(): User
    {
        return User::where('role', 'admin')->orderBy('id')->firstOrFail();
    }

    private function department(): Department
    {
        $department = Department::create([
            'name' => ['uz' => 'Sinov ' . uniqid()],
            'slug' => 'sinov-' . uniqid(),
            'structure_type_id' => Department::TYPE_UNIT,
            'active' => 1,
        ]);

        $this->departments[] = $department->id;

        return $department;
    }

    private function base(): array
    {
        return [
            'first_name' => ['uz' => 'Ali', 'ru' => '', 'en' => ''],
            'last_name' => ['uz' => 'Formatov', 'ru' => '', 'en' => ''],
            'surname' => ['uz' => '', 'ru' => '', 'en' => ''],
            'assignments_present' => 1,
        ];
    }

    public function test_new_employee_is_created_once_with_two_workplaces(): void
    {
        $a = $this->department();
        $b = $this->department();

        $response = $this->actingAs($this->admin())->post(route('employs.store'), $this->base() + [
            'assignments' => [
                ['department_id' => $a->id, 'position_id' => 4, 'order' => 1, 'active' => 1],
                ['department_id' => $b->id, 'position_id' => 8, 'order' => 2, 'active' => 1],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $employ = Employ::where('last_name->uz', 'Formatov')->latest('id')->firstOrFail();
        $this->people[] = $employ->id;

        $this->assertSame(
            [$a->id => 4, $b->id => 8],
            EmployMeta::where('employ_id', $employ->id)->orderBy('department_id')->pluck('position_id', 'department_id')->all()
        );

        // Boʻsh tarjimalar `null` boʻlib yozilgan — "toʻldirilgan" emas.
        $this->assertNull($employ->surname);
    }

    public function test_editing_workplaces_adds_updates_and_removes(): void
    {
        $a = $this->department();
        $b = $this->department();
        $c = $this->department();

        $this->actingAs($this->admin())->post(route('employs.store'), $this->base() + [
            'assignments' => [
                ['department_id' => $a->id, 'position_id' => 4, 'active' => 1],
                ['department_id' => $b->id, 'position_id' => 8, 'active' => 1],
            ],
        ]);

        $employ = Employ::where('last_name->uz', 'Formatov')->latest('id')->firstOrFail();
        $this->people[] = $employ->id;

        $metaA = EmployMeta::where('employ_id', $employ->id)->where('department_id', $a->id)->firstOrFail();
        $metaB = EmployMeta::where('employ_id', $employ->id)->where('department_id', $b->id)->firstOrFail();

        // A — lavozim oʻzgaradi, B — olib tashlanadi, C — qoʻshiladi.
        $this->actingAs($this->admin())->put(route('employs.update', $employ->id), $this->base() + [
            'assignments' => [
                ['id' => $metaA->id, 'department_id' => $a->id, 'position_id' => 5, 'active' => 1],
                ['department_id' => $c->id, 'position_id' => 8, 'active' => 1],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(5, (int) $metaA->fresh()->position_id);
        $this->assertSame($metaA->id, $metaA->fresh()->id, 'Mavjud tayinlov qayta yaratilmasligi kerak');
        $this->assertSoftDeleted('employ_metas', ['id' => $metaB->id]);
        $this->assertTrue(EmployMeta::where('employ_id', $employ->id)->where('department_id', $c->id)->exists());
    }

    /**
     * Formada "Ish joylari" boʻlimi boʻlmasa (`assignments_present` yoʻq)
     * tayinlovlarga tegilmaydi — sahifa formasidagi xato takrorlanmasin.
     */
    public function test_a_save_without_the_workplaces_section_keeps_them(): void
    {
        $a = $this->department();

        $this->actingAs($this->admin())->post(route('employs.store'), $this->base() + [
            'assignments' => [['department_id' => $a->id, 'position_id' => 4, 'active' => 1]],
        ]);

        $employ = Employ::where('last_name->uz', 'Formatov')->latest('id')->firstOrFail();
        $this->people[] = $employ->id;

        $payload = $this->base();
        unset($payload['assignments_present']);

        $this->actingAs($this->admin())->put(route('employs.update', $employ->id), $payload)->assertRedirect();

        $this->assertSame(1, EmployMeta::where('employ_id', $employ->id)->count());
    }

    public function test_edit_form_shows_workplaces_and_full_names(): void
    {
        $a = $this->department();

        $this->actingAs($this->admin())->post(route('employs.store'), $this->base() + [
            'assignments' => [['department_id' => $a->id, 'position_id' => 4, 'active' => 1]],
        ]);

        $employ = Employ::where('last_name->uz', 'Formatov')->latest('id')->firstOrFail();
        $this->people[] = $employ->id;

        $this->actingAs($this->admin())->get(route('employs.edit', $employ->id))
            ->assertOk()
            ->assertSee('Ish joylari')
            ->assertSee('name="assignments_present"', false);

        // Familiya boʻyicha qidiruv ham ishlaydi.
        $this->actingAs($this->admin())->get(route('employs.index', ['search' => 'Formatov']))
            ->assertOk()
            ->assertSee('Formatov');
    }
}
