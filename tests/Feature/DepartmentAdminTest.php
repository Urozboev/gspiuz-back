<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employ;
use App\Models\EmployMeta;
use App\Models\User;
use Tests\TestCase;

/**
 * Boʻlim, fakultet va kafedra admin paneldan boshqariladi.
 *
 * Foydalanuvchi talablari:
 *   1. Kafedra va fakultet haqida yangilikdagi kabi erkin maʼlumot.
 *   2. Boshqarma va boʻlimlar alohida roʻyxat, har biri oʻz sahifasi
 *      bilan — maʼlumot va xodimlari.
 *   3. Xodim bir marta qoʻshiladi va bir nechta boʻlimda ishlatiladi.
 */
class DepartmentAdminTest extends TestCase
{
    private array $departments = [];

    private array $people = [];

    protected function tearDown(): void
    {
        EmployMeta::withTrashed()->whereIn('department_id', $this->departments)->forceDelete();
        Department::withTrashed()->whereIn('id', $this->departments)->forceDelete();
        EmployMeta::withTrashed()->whereIn('employ_id', $this->people)->forceDelete();
        Employ::withTrashed()->whereIn('id', $this->people)->forceDelete();

        parent::tearDown();
    }

    private function admin(): User
    {
        return User::where('role', 'admin')->orderBy('id')->firstOrFail();
    }

    private function api(string $path, string $locale = 'uz')
    {
        $prefix = trim((string) config('api.prefix'), '/');

        return $this->withHeaders(['Accept-Language' => $locale])->getJson('/' . $prefix . $path);
    }

    private function department(array $attributes = []): Department
    {
        $department = Department::create(array_merge([
            'name' => ['uz' => 'Sinov boʻlimi', 'ru' => 'Sinov', 'en' => 'Test'],
            'slug' => 'sinov-bolimi-' . uniqid(),
            'structure_type_id' => Department::TYPE_UNIT,
            'active' => 1,
        ], $attributes));

        $this->departments[] = $department->id;

        return $department;
    }

    private function person(string $last = 'Sinovov'): Employ
    {
        $employ = Employ::create([
            'first_name' => ['uz' => 'Ali', 'ru' => 'Ali', 'en' => 'Ali'],
            'last_name' => ['uz' => $last, 'ru' => $last, 'en' => $last],
            'surname' => ['uz' => 'Valiyevich', 'ru' => 'Valiyevich', 'en' => 'Valiyevich'],
            'slug' => 'sinov-' . uniqid(),
        ]);

        $this->people[] = $employ->id;

        return $employ;
    }

    // ------------------------------------------------------------------

    public function test_admin_pages_open(): void
    {
        $department = $this->department();

        $this->actingAs($this->admin())->get(route('departaments.index'))->assertOk();
        $this->actingAs($this->admin())->get(route('departaments.index', ['type' => 2]))->assertOk();
        $this->actingAs($this->admin())->get(route('departaments.create'))->assertOk();
        $this->actingAs($this->admin())->get(route('departaments.edit', $department->id))->assertOk()
            ->assertSee('Boʻlim haqida')
            ->assertSee('Xodim qoʻshish');
    }

    /** 1-talab: boʻlim haqida maʼlumot saqlanadi va saytga chiqadi. */
    public function test_department_description_reaches_the_site(): void
    {
        $department = $this->department(['structure_type_id' => Department::TYPE_KAFEDRA]);

        $this->actingAs($this->admin())->put(route('departaments.update', $department->id), [
            'name' => ['uz' => 'Sinov kafedrasi', 'ru' => 'Тест', 'en' => 'Test chair'],
            'description' => ['uz' => '<p>Kafedra <strong>1995-yilda</strong> tashkil etilgan.</p>', 'ru' => '<p>Основана в 1995.</p>', 'en' => ''],
            'address' => ['uz' => '2-bino, 214-xona', 'ru' => '', 'en' => ''],
            'structure_type_id' => Department::TYPE_KAFEDRA,
            'phone' => '+998 67 225 40 60',
            'email' => 'kafedra@gspi.uz',
            'active' => 1,
            'order' => 3,
            'slug' => $department->slug,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $json = $this->api('/kafedralar/' . $department->slug)->assertOk()->json('department');

        $this->assertSame('Sinov kafedrasi', $json['name']);
        $this->assertStringContainsString('1995-yilda', $json['description']);
        $this->assertSame('+998 67 225 40 60', $json['phone']);
        $this->assertSame('2-bino, 214-xona', $json['address']);

        // Ruscha matn ruscha soʻrovda, inglizchasi boʻsh — oʻzbekchaga tushadi.
        $this->assertStringContainsString('1995', $this->api('/kafedralar/' . $department->slug, 'ru')->json('department.description'));
        $this->assertStringContainsString('1995-yilda', $this->api('/kafedralar/' . $department->slug, 'en')->json('department.description'));
    }

    /** Saqlash rasmni yoʻqotmasligi kerak — oldingi xatoning takrorlanmasligi. */
    public function test_saving_without_a_new_image_keeps_the_old_one(): void
    {
        $department = $this->department(['image' => 'eski-rasm.webp']);

        $this->actingAs($this->admin())->put(route('departaments.update', $department->id), [
            'name' => ['uz' => 'Yangi nom'],
            'structure_type_id' => Department::TYPE_UNIT,
            'slug' => $department->slug,
        ])->assertRedirect();

        $this->assertSame('eski-rasm.webp', $department->fresh()->image);

        $this->actingAs($this->admin())->put(route('departaments.update', $department->id), [
            'name' => ['uz' => 'Yangi nom'],
            'structure_type_id' => Department::TYPE_UNIT,
            'slug' => $department->slug,
            'delete_image' => 1,
        ])->assertRedirect();

        $this->assertNull($department->fresh()->image);
    }

    /** 2-talab: boshqarma va boʻlimlar roʻyxati alohida. */
    public function test_units_list_contains_only_requested_type(): void
    {
        $unit = $this->department(['structure_type_id' => Department::TYPE_UNIT]);
        $kafedra = $this->department(['structure_type_id' => Department::TYPE_KAFEDRA]);
        $hidden = $this->department(['structure_type_id' => Department::TYPE_UNIT, 'active' => 0]);

        $slugs = collect($this->api('/units?type=2')->assertOk()->json('data'))->pluck('slug');

        $this->assertContains($unit->slug, $slugs);
        $this->assertNotContains($kafedra->slug, $slugs);
        $this->assertNotContains($hidden->slug, $slugs, 'Yashirin boʻlim roʻyxatda chiqmasligi kerak');
    }

    /** 3-talab: bitta xodim ikki boʻlimda — nusxasiz. */
    public function test_one_employee_serves_two_departments(): void
    {
        $first = $this->department();
        $second = $this->department(['structure_type_id' => Department::TYPE_KAFEDRA]);
        $person = $this->person('Ikkitayev');

        foreach ([[$first, 4], [$second, 8]] as [$department, $position]) {
            $this->actingAs($this->admin())->post(route('departaments.staff.store', $department->id), [
                'employ_id' => $person->id,
                'position_id' => $position,
            ])->assertRedirect()->assertSessionHasNoErrors();
        }

        // Xodim bitta, tayinlov ikkita.
        $this->assertSame(1, Employ::where('id', $person->id)->count());
        $this->assertSame(2, EmployMeta::where('employ_id', $person->id)->count());

        // Ikkala sahifada ham koʻrinadi, har birida oʻz lavozimi bilan.
        $boss = $this->api('/department/' . $first->slug)->json('department_boss');
        $this->assertSame($person->id, $boss['id'] ?? null);
        $this->assertSame(4, $boss['employ_meta']['position_id']);

        $others = collect($this->api('/kafedralar/' . $second->slug)->json('simple_employee'));
        $this->assertSame(8, $others->firstWhere('id', $person->id)['employ_meta']['position_id'] ?? null);

        // Ismi bir joyda oʻzgarsa — ikkala boʻlimda ham oʻzgaradi.
        $person->update(['last_name' => ['uz' => 'Yangifamiliya', 'ru' => 'Yangifamiliya', 'en' => 'Yangifamiliya']]);

        $this->assertStringContainsString('Yangifamiliya', $this->api('/department/' . $first->slug)->getContent());
        $this->assertStringContainsString('Yangifamiliya', $this->api('/kafedralar/' . $second->slug)->getContent());
    }

    public function test_the_same_employee_is_not_added_twice_to_one_department(): void
    {
        $department = $this->department();
        $person = $this->person();

        foreach ([1, 2] as $_) {
            $this->actingAs($this->admin())->post(route('departaments.staff.store', $department->id), [
                'employ_id' => $person->id,
                'position_id' => 8,
            ]);
        }

        $this->assertSame(1, EmployMeta::where('employ_id', $person->id)->where('department_id', $department->id)->count());
    }

    public function test_removing_from_a_department_keeps_the_employee(): void
    {
        $department = $this->department();
        $person = $this->person();

        $this->actingAs($this->admin())->post(route('departaments.staff.store', $department->id), [
            'employ_id' => $person->id,
            'position_id' => 8,
        ]);

        $assignment = EmployMeta::where('employ_id', $person->id)->firstOrFail();

        $this->actingAs($this->admin())
            ->delete(route('departaments.staff.destroy', [$department->id, $assignment->id]))
            ->assertRedirect();

        $this->assertSoftDeleted('employ_metas', ['id' => $assignment->id]);
        $this->assertNotSoftDeleted('employs', ['id' => $person->id]);
        $this->assertNull($this->api('/department/' . $department->slug)->json('department_boss'));
    }

    /** Boshqa boʻlimning tayinlovini bu boʻlim orqali oʻchirib boʻlmaydi. */
    public function test_cannot_remove_an_assignment_through_another_department(): void
    {
        $mine = $this->department();
        $other = $this->department();
        $person = $this->person();

        $this->actingAs($this->admin())->post(route('departaments.staff.store', $other->id), [
            'employ_id' => $person->id,
            'position_id' => 8,
        ]);

        $assignment = EmployMeta::where('employ_id', $person->id)->firstOrFail();

        $this->actingAs($this->admin())
            ->delete(route('departaments.staff.destroy', [$mine->id, $assignment->id]))
            ->assertNotFound();

        $this->assertNotSoftDeleted('employ_metas', ['id' => $assignment->id]);
    }
}
