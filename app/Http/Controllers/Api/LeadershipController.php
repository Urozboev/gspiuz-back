<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employ;
use App\Models\EmployMeta;
use App\Models\Position;
use Illuminate\Http\Request;

class LeadershipController extends Controller
{
    /**
     * Boʻlim sahifasida "rahbar" sifatida koʻrsatiladigan lavozimlar:
     * rektor, prorektor, dekan, boʻlim boshligʻi, kafedra mudiri.
     */
    private const LEADERSHIP_POSITIONS = [1, 2, 3, 4, 5];

    public function get_employ_meta(Request $request)
    {
        $locale = $request->get('locale', app()->getLocale()); // Get the locale for translations

        // Retrieve all EmployMeta data with their relationships
        $employMetas = EmployMeta::with([
            'employ',
            'department',
            'position',
            'employ_form',
            'employ_staff',
            'employ_type'
        ])->get();

        // Map through the data and localize the response
        $translatedEmployMetas = $employMetas->map(function ($employMeta) use ($locale) {
            return [
                'id' => $employMeta->id,
                'employ_id' => $employMeta->employ_id,
                'department_id' => $employMeta->department_id,
                'position_id' => $employMeta->position_id,
                'employ_staff_id' => $employMeta->employ_staff_id,
                'employ_form_id' => $employMeta->employ_form_id,
                'contrakt_date' => $employMeta->contrakt_date,
                'contrakt_number' => $employMeta->contrakt_number,
                'employ_type_id' => $employMeta->employ_type_id,
                'active' => $employMeta->active,
                'employ' => $employMeta->employ ? [
                    'id' => $employMeta->employ->id,
                    'first_name' => $employMeta->employ->first_name[$locale] ?? null,
                    'last_name' => $employMeta->employ->last_name[$locale] ?? null,
                    'surname' => $employMeta->employ->surname[$locale] ?? null,
                    'email' => $employMeta->employ->email,
                    'address' => $employMeta->employ->address[$locale],
                    'status' => $employMeta->employ->status,
                    'birthday' => $employMeta->employ->birthday,
                    'gender' => $employMeta->employ->gender,
                    'special' => $employMeta->employ->special,
                    'photo' => $employMeta->employ->photo ? url('/upload/images/' . $employMeta->employ->photo) : null,
                    'phone' => $employMeta->employ->phone,
                    'dec' => $employMeta->employ->dec[$locale] ?? $employMeta->employ->dec,
                    'started_work' => $employMeta->employ->start_time,
                    'leader' => $employMeta->employ->leader,
                    'professor' => $employMeta->employ->professor,
                ] : null,
                'department' => $employMeta->department ? [
                    'id' => $employMeta->department->id,
                    'name' => $employMeta->department->name[$locale] ?? $employMeta->department->name,
                    'structure_type' => $employMeta->department->structureType ? [
                        'id' => $employMeta->department->structureType->id,
                        'name' => $employMeta->department->structureType->name[$locale] ?? $employMeta->department->structureType->name,
                    ] : null,
                    'parent' => $employMeta->department->parent ? [
                        'id' => $employMeta->department->parent->id,
                        'name' => $employMeta->department->parent->name[$locale] ?? $employMeta->department->parent->name,
                    ] : null,
                    'children' => $employMeta->department->children->map(function ($child) use ($locale) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name[$locale] ?? $child->name,
                            'active' => $child->active,
                            'code' => $child->code,
                        ];
                    }),
                    'active' => $employMeta->department->active,
                    'code' => $employMeta->department->code,
                ] : null,
                'position' => $employMeta->position ? [
                    'id' => $employMeta->position->id,
                    'name' => $employMeta->position->name[$locale] ?? $employMeta->position->name,
                ] : null,
                'employ_form' => $employMeta->employ_form ? [
                    'id' => $employMeta->employ_form->id,
                    'name' => $employMeta->employ_form->name[$locale] ?? $employMeta->employ_form->name,
                ] : null,
                'employ_staff' => $employMeta->employ_staff ? [
                    'id' => $employMeta->employ_staff->id,
                    'name' => $employMeta->employ_staff->name[$locale] ?? $employMeta->employ_staff->name,
                ] : null,
                'employ_type' => $employMeta->employ_type ? [
                    'id' => $employMeta->employ_type->id,
                    'name' => $employMeta->employ_type->name[$locale] ?? $employMeta->employ_type->name,
                ] : null,
            ];
        });

        return response()->json($translatedEmployMetas);
    }

    /**
     * Get a single EmployMeta with relationships.
     */
    public function show_employ_meta(Request $request, $id)
    {
        $locale = $request->get('locale', app()->getLocale()); // Get the locale for translations

        // Retrieve a single EmployMeta record with relationships
        $employMeta = EmployMeta::with([
            'employ',
            'department.structureType',
            'department.parent',
            'department.children',
            'position',
            'employ_form',
            'employ_staff',
            'employ_type'
        ])->findOrFail($id);

        // Localize and format the response
        $translatedEmployMeta = [
            'id' => $employMeta->id,
            'employ_id' => $employMeta->employ_id,
            'department_id' => $employMeta->department_id,
            'position_id' => $employMeta->position_id,
            'employ_staff_id' => $employMeta->employ_staff_id,
            'employ_form_id' => $employMeta->employ_form_id,
            'contrakt_date' => $employMeta->contrakt_date,
            'contrakt_number' => $employMeta->contrakt_number,
            'employ_type_id' => $employMeta->employ_type_id,
            'active' => $employMeta->active,
            'employ' => $employMeta->employ ? [
                'id' => $employMeta->employ->id,
                'first_name' => $employMeta->employ->first_name[$locale] ?? null,
                'last_name' => $employMeta->employ->last_name[$locale] ?? null,
                'surname' => $employMeta->employ->surname[$locale] ?? null,
                'email' => $employMeta->employ->email,
                'address' => $employMeta->employ->address[$locale],
                'status' => $employMeta->employ->status,
                'birthday' => $employMeta->employ->birthday,
                'gender' => $employMeta->employ->gender,
                'special' => $employMeta->employ->special,
                'photo' => $employMeta->employ->photo ? url('/upload/images/' . $employMeta->employ->photo) : null,
                'phone' => $employMeta->employ->phone,
                'dec' => $employMeta->employ->dec[$locale] ?? $employMeta->employ->dec,
                'started_work' => $employMeta->employ->start_time,
                'leader' => $employMeta->employ->leader,
                'professor' => $employMeta->employ->professor,
            ] : null,
            'department' => $employMeta->department ? [
                'id' => $employMeta->department->id,
                'name' => $employMeta->department->name[$locale] ?? $employMeta->department->name,
                'structure_type' => $employMeta->department->structureType ? [
                    'id' => $employMeta->department->structureType->id,
                    'name' => $employMeta->department->structureType->name[$locale] ?? $employMeta->department->structureType->name,
                ] : null,
                'parent' => $employMeta->department->parent ? [
                    'id' => $employMeta->department->parent->id,
                    'name' => $employMeta->department->parent->name[$locale] ?? $employMeta->department->parent->name,
                ] : null,
                'children' => $employMeta->department->children->map(function ($child) use ($locale) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name[$locale] ?? $child->name,
                        'active' => $child->active,
                        'code' => $child->code,
                    ];
                }),
                'active' => $employMeta->department->active,
                'code' => $employMeta->department->code,
            ] : null,
            'position' => $employMeta->position ? [
                'id' => $employMeta->position->id,
                'name' => $employMeta->position->name[$locale] ?? $employMeta->position->name,
            ] : null,
            'employ_form' => $employMeta->employ_form ? [
                'id' => $employMeta->employ_form->id,
                'name' => $employMeta->employ_form->name[$locale] ?? $employMeta->employ_form->name,
            ] : null,
            'employ_staff' => $employMeta->employ_staff ? [
                'id' => $employMeta->employ_staff->id,
                'name' => $employMeta->employ_staff->name[$locale] ?? $employMeta->employ_staff->name,
            ] : null,
            'employ_type' => $employMeta->employ_type ? [
                'id' => $employMeta->employ_type->id,
                'name' => $employMeta->employ_type->name[$locale] ?? $employMeta->employ_type->name,
            ] : null,
        ];

        return response()->json($translatedEmployMeta);
    }




    public function getRahbariyatEmployees()
    {
        $locale = app()->getLocale(); // Get the current locale

        // Fetch all employees in the "Rahbariyat" department
        $employees = EmployMeta::with([
            'department.structureType',
            'department.parent',
            'position',
            'employ',
        ])
            ->whereHas('department', function ($query) {
                // Rahbariyat tuzilma turi (structure_types.id = 1).
                // Avval bitta bo'lim ID'siga qattiq bog'langan edi.
                $query->where('structure_type_id', 1);
            })
            ->get();

        // Group employees by department and map the response
        $response = $employees->groupBy('department.id')->map(function ($group) use ($locale) {
            $department = $group->first()->department;

            return [
                'id' => $department->id,
                'name' => data_get($department->name, $locale) ?? data_get($department->name, $this->main_lang->code),
                'structure_type' => $department->structureType ? [
                    'id' => $department->structureType->id,
                    'name' => data_get($department->structureType->name, $locale) ?? data_get($department->structureType->name, $this->main_lang->code),
                ] : null,
                'parent' => $department->parent ? [
                    'id' => $department->parent->id,
                    'name' => data_get($department->parent->name, $locale) ?? data_get($department->parent->name, $this->main_lang->code),
                ] : null,
                'active' => $department->active,
                'code' => $department->code,

                // Professor employees
                'professor_employ' => $group->filter(function ($employee) {
                    // Professor-o'qituvchilar: ilmiy unvoni bor, ammo rahbar emas.
                    $employ = $employee->employ;

                    return (bool) optional($employ)->professor && !optional($employ)->leader;
                })->map(function ($employee) use ($locale) {
                    return [
                        'id' => $employee->id,
                        'slug' => $employee->slug,
                        'id_employ' => $employee->employ->id,
                        'first_name' => data_get($employee->employ->first_name, $locale) ?? data_get($employee->employ->first_name, $this->main_lang->code),
                        'last_name' => data_get($employee->employ->last_name, $locale) ?? data_get($employee->employ->last_name, $this->main_lang->code),
                        'surname' => $employee->employ->surname[$locale] ?? null,
                        'email' => $employee->employ->email,
                        'address' => data_get($employee->employ->address, $locale) ?? data_get($employee->employ->address, $this->main_lang->code),
                        'status' => $employee->employ->status,
                        'birthday' => $employee->employ->birthday,
                        'gender' => $employee->employ->gender,
                        'special' => $employee->employ->special,
                        'photo' => $employee->employ->photo ? url('/upload/images/' . $employee->employ->photo) : null,
                        'phone' => $employee->employ->phone,
                        'dec' => data_get($employee->employ->dec, $locale) ?? data_get($employee->employ->dec, $this->main_lang->code),
                        'started_work' => $employee->employ->start_time,
                        'leader' => $employee->employ->leader,
                        'professor' => $employee->employ->professor,
                        'department_id' => $employee->department_id,
                        'position_id' => $employee->position_id,
                        'employ_staff_id' => $employee->employ_staff_id,
                        'employ_form_id' => $employee->employ_form_id,
                        'employ_type_id' => $employee->employ_type_id,
                        'active' => $employee->active,
                        'contrakt_date' => $employee->contrakt_date,
                        'contrakt_number' => $employee->contrakt_number,
                        'position' => $employee->position,
                        'employ_form' => $employee->employ_form->name[$locale] ?? $employee->employ_form->name,
                        'employ_staff' => $employee->employ_staff->name[$locale] ?? $employee->employ_staff->name,
                        'employ_type' => $employee->employ_type->name[$locale] ?? $employee->employ_type->name,
                    ];
                })->values(),

                // Management employees
                'manage_employ' => $group->filter(function ($employee) {
                    // Rahbariyat — "leader" bayrog'i bo'yicha (rektor, prorektorlar).
                    $employ = $employee->employ;

                    return (bool) optional($employ)->leader || !optional($employ)->professor;
                })->map(function ($employee) use ($locale) {
                    return [
                        'id' => $employee->id,
                        'slug' => $employee->slug,
                        'id_employ' => $employee->employ->id ?? null,
                        'first_name' => data_get($employee->employ->first_name, $locale) ?? data_get($employee->employ->first_name, $this->main_lang->code) ?? null,
                        'last_name' => data_get($employee->employ->last_name, $locale) ?? data_get($employee->employ->last_name, $this->main_lang->code) ?? null ,
                        'surname' => $employee->employ->surname[$locale] ?? null,
                        'email' => $employee->employ->email ?? null,
                        'address' => data_get($employee->employ->address, $locale) ?? data_get($employee->employ->address, $this->main_lang->code) ?? null,
                        'status' => $employee->employ->status ?? null,
                        'birthday' => $employee->employ->birthday ?? null,
                        'gender' => $employee->employ->gender ?? null,
                        'special' => $employee->employ->special ?? null,
                        'photo' => optional($employee->employ)->photo
                            ? url('/upload/images/' . $employee->employ->photo)
                            : null,

//                        'photo' => $employee->employ->photo ? url('/upload/images/' . $employee->employ->photo) : null ?? null,
                        'phone' => $employee->employ->phone ?? null,
                        'dec' => data_get($employee->employ->dec, $locale) ?? data_get($employee->employ->dec, $this->main_lang->code) ?? null,
                        'started_work' => $employee->employ->start_time ?? null,
                        'leader' => $employee->employ->leader ?? null,
                        'professor' => $employee->employ->professor ?? null,
                        'department_id' => $employee->department_id ?? null,
                        'position_id' => $employee->position_id ?? null,
                        'employ_staff_id' => $employee->employ_staff_id ?? null,
                        'employ_form_id' => $employee->employ_form_id ?? null,
                        'employ_type_id' => $employee->employ_type_id ?? null,
                        'active' => $employee->active ?? null,
                        'contrakt_date' => $employee->contrakt_date ?? null,
                        'contrakt_number' => $employee->contrakt_number ?? null,
                        'position' => $employee->position,
                        'employ_form' => data_get($employee->employ_form->name, $locale) ?? data_get($employee->employ_form->name, $this->main_lang->code) ?? null,
                        'employ_staff' => data_get($employee->employ_staff->name, $locale) ?? data_get($employee->employ_staff->name, $this->main_lang->code) ?? null,
                        'employ_type' => data_get($employee->employ_type->name, $locale) ?? data_get($employee->employ_type->name, $this->main_lang->code) ?? null,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json($response);
    }
//    public function getEmployeeDetails($id)
//    {
//        $locale = app()->getLocale(); // Get the current locale
//
//        // Fetch the employee by ID, along with the related models
//        $employee = EmployMeta::with([
//            'department.structureType',
//            'department.parent',
//            'position',
//            'employ',
//            'employ_form',
//            'employ_staff',
//            'employ_type',
//        ])
//            ->findOrFail($id); // This will return 404 if not found
//
//        // Map the employee details to the response structure
//        $response = [
//            'id' => $employee->id,
//            'first_name' => $employee->employ->first_name[$locale],
//            'last_name' => $employee->employ->last_name[$locale],
//            'surname' => $employee->employ->surname[$locale],
//            'email' => $employee->employ->email,
//            'address' => $employee->employ->address[$locale],
//            'status' => $employee->employ->status,
//            'birthday' => $employee->employ->birthday,
//            'gender' => $employee->employ->gender,
//            'special' => $employee->employ->special,
//            'photo' => $employee->employ->photo ? url('/upload/images/' . $employee->employ->photo) : null,
//            'phone' => $employee->employ->phone,
//            'dec' => $employee->employ->dec[$locale] ?? $employee->employ->dec,
//            'started_work' => $employee->employ->start_time,
//            'leader' => $employee->employ->leader,
//            'professor' => $employee->employ->professor,
//            'department' => [
//                'id' => $employee->department->id,
//                'name' => $employee->department->name[$locale] ?? $employee->department->name,
//                'structure_type' => $employee->department->structureType ? [
//                    'id' => $employee->department->structureType->id,
//                    'name' => $employee->department->structureType->name[$locale] ?? $employee->department->structureType->name,
//                ] : null,
//                'parent' => $employee->department->parent ? [
//                    'id' => $employee->department->parent->id,
//                    'name' => $employee->department->parent->name[$locale] ?? $employee->department->parent->name,
//                ] : null,
//                'active' => $employee->department->active,
//                'code' => $employee->department->code,
//            ],
//            'position' => [
//                'id' => $employee->position->id,
//                'name' => $employee->position->name[$locale] ?? $employee->position->name,
//            ],
//            'contrakt_date' => $employee->contrakt_date,
//            'contrakt_number' => $employee->contrakt_number,
//            'employ_form' => $employee->employ_form->name[$locale],
//            'employ_staff' => $employee->employ_staff->name[$locale],
//            'employ_type' => $employee->employ_type->name[$locale],
//        ];
//
//        return response()->json($response);
//    }

    public function getEmployeeDetails($slug)
    {
        $locale = app()->getLocale(); // Joriy tilni olish

        // EmployMeta slug bo‘yicha ma'lumotni olish
        $employee = EmployMeta::with([
            'department.structureType',
            'department.parent',
            'position',
            'employ',
            'employ_form',
            'employ_staff',
            'employ_type',
        ])
            ->where('slug', $slug) // EmployMeta slug bo‘yicha qidirish
            ->firstOrFail(); // 404 qaytaradi agar topilmasa

        // Ma'lumotlarni JSON formatida qaytarish
        $response = [
            'id' => $employee->id,
            'slug' => $employee->slug, // EmployMeta slug
            'first_name' => data_get($employee->employ->first_name, $locale) ?? data_get($employee->employ->first_name, $this->main_lang->code),
            'last_name' => data_get($employee->employ->last_name, $locale) ?? data_get($employee->employ->last_name, $this->main_lang->code),
            'surname' => $employee->employ->surname[$locale] ?? null,
            'email' => $employee->employ->email,
            'address' => data_get($employee->employ->address, $locale) ?? data_get($employee->employ->address, $this->main_lang->code),
            'status' => $employee->employ->status,
            'birthday' => $employee->employ->birthday,
            'gender' => $employee->employ->gender,
            'special' => $employee->employ->special,
            'photo' => $employee->employ->photo ? url('/upload/images/' . $employee->employ->photo) : null,
            'phone' => $employee->employ->phone,
            'dec' => data_get($employee->employ->dec, $locale) ?? data_get($employee->employ->dec, $this->main_lang->code),
            'started_work' => $employee->employ->start_time,
            'leader' => $employee->employ->leader,
            'professor' => $employee->employ->professor,
            'department' => [
                'id' => $employee->department->id,
                'name' => data_get($employee->department->name, $locale) ?? data_get($employee->department->name, $this->main_lang->code),
                'structure_type' => $employee->department->structureType ? [
                    'id' => $employee->department->structureType->id,
                    'name' => data_get($employee->department->structureType->name, $locale) ?? data_get($employee->department->structureType->name, $this->main_lang->code),
                ] : null,
                'parent' => $employee->department->parent ? [
                    'id' => $employee->department->parent->id,
                    'name' => data_get($employee->department->parent->name, $locale) ?? data_get($employee->department->parent->name, $this->main_lang->code),
                ] : null,
                'active' => $employee->department->active,
                'code' => $employee->department->code,
            ],
            'position' => [
                'id' => $employee->position->id,
                'name' => data_get($employee->position->name, $locale) ?? data_get($employee->position->name, $this->main_lang->code),
            ],
            'contrakt_date' => $employee->contrakt_date,
            'contrakt_number' => $employee->contrakt_number,
            'employ_form' => data_get($employee->employ_form->name, $locale) ?? data_get($employee->employ_form->name, $this->main_lang->code),
            'employ_staff' => data_get($employee->employ_staff->name, $locale) ?? data_get($employee->employ_staff->name, $this->main_lang->code),
            'employ_type' => data_get($employee->employ_type->name, $locale) ?? data_get($employee->employ_type->name, $this->main_lang->code),
        ];

        // Bitta xodim bir nechta bo'limda ishlashi mumkin — masalan
        // prorektor, ayni paytda kengash a'zosi. Yuqoridagi maydonlar
        // ochilgan tayinlovga tegishli; bu ro'yxat esa hammasini beradi.
        $response['assignments'] = EmployMeta::with(['department', 'position'])
            ->where('employ_id', $employee->employ_id)
            ->where('active', 1)
            ->orderBy('order')
            ->get()
            ->map(fn ($meta) => [
                'slug'       => $meta->slug,
                'department' => $meta->department
                    ? (data_get($meta->department->name, $locale)
                        ?? data_get($meta->department->name, $this->main_lang->code))
                    : null,
                'position'   => $meta->position
                    ? (data_get($meta->position->name, $locale)
                        ?? data_get($meta->position->name, $this->main_lang->code))
                    : null,
                'current'    => $meta->id === $employee->id,
            ])
            ->values();

        return response()->json($response);
    }


//    public function getDepartmentEmployees($slug)
//    {
//        $locale = app()->getLocale(); // Get the current locale
//        $employeeBoss = Employ::whereHas('employMeta',function ($query) use ($id){
//            $query->whereHas('department', function ($subQuery) use ($id){
//               $subQuery->where('id', $id);
//            })->whereHas('position', function ($subQuery){
//                $subQuery->where('active', 1)->where('id',4);
//            });
//        })->with(['employMeta'=>function ($query) {
//            $query->with(['department.structureType','department.parent','position']);
//        }])->first();
//        $simpleEmployees = Employ::whereHas('employMeta',function ($query) use ($id){
//            $query->whereHas('department', function ($subQuery) use ($id){
//                $subQuery->where('id', $id);
//            })->whereHas('position', function ($subQuery){
//                $subQuery->where('active', 1)->where('id','!=',4);
//            });
//        })->with(['employMeta'=>function ($query) {
//            $query->with(['department.structureType','department.parent','position']);
//        }])->get();
//        $employees =  [
//            'department_boss' => $employeeBoss,
//            'simple_employee'=> $simpleEmployees,
//        ];
//         return response()->json($employees);
//
//    }
    public function getDepartmentEmployees($slug)
    {
        $locale = app()->getLocale(); // Joriy tilni olish

        // Slug orqali bo'limni topish
        $department = Department::where('slug', $slug)->firstOrFail();

        // Rahbarni topish.
        //
        // Rahbarlik lavozimlari bittasi emas: rektor, prorektor, dekan,
        // boʻlim boshligʻi, kafedra mudiri. Ilgari bu yerda faqat
        // `id = 4` (boʻlim boshligʻi) qidirilardi, shuning uchun rektor
        // va kafedra mudiri oʻz sahifasida rahbar sifatida chiqmasdi.
        $employeeBoss = Employ::whereHas('employMeta', function ($query) use ($department) {
            $query->where('department_id', $department->id)
                ->whereHas('position', function ($subQuery) {
                    $subQuery->where('active', 1)->whereIn('id', self::LEADERSHIP_POSITIONS);
                });
        })->with(['employMeta' => function ($query) use ($department) {
            // Xodim bir nechta bo'limda bo'lishi mumkin — shu sahifaga
            // tegishli tayinlov olinadi, birinchisi emas.
            $query->where('department_id', $department->id)
                ->with(['department.structureType', 'department.parent', 'position','employ_type']);
        }])->first();

        // Qolgan xodimlar — boʻlimdagi hamma, rahbardan tashqari.
        //
        // Ilgari bu yerda `position_id != 4` sharti turgan edi. Bir boʻlimda
        // bir nechta "boʻlim boshligʻi" boʻlsa (masalan institut kengashi
        // tarkibida), ulardan bittasi rahbar sifatida chiqib, qolganlari
        // hech qayerda koʻrinmay qolardi.
        $simpleEmployees = Employ::whereHas('employMeta', function ($query) use ($department) {
            $query->where('department_id', $department->id)
                ->whereHas('position', fn ($subQuery) => $subQuery->where('active', 1));
        })->when($employeeBoss, fn ($query) => $query->where('id', '!=', $employeeBoss->id))
            ->with(['employMeta' => function ($query) use ($department) {
                $query->where('department_id', $department->id)
                    ->with(['department.structureType', 'department.parent', 'position']);
            }])->get();

        // Javobni qaytarish
        return response()->json([
            'department_boss' => $employeeBoss,
            'simple_employee' => $simpleEmployees,
        ]);
    }

    public function getDepartmentEmployeesuser($slug)
    {
        // EmployMeta ma'lumotlarini slug orqali olish
        $simpleEmployees = EmployMeta::with([
            'employ',
            'department',
            'position',
            'employ_form',
            'employ_staff',
            'employ_type'
        ])->where('slug', $slug)->get();

        // Agar hech qanday ma'lumot topilmasa, 404 xato qaytarish
        if ($simpleEmployees->isEmpty()) {
            return response()->json(['message' => 'Employ meta topilmadi'], 404);
        }

        // Har bir topilgan hodim ma'lumotlarini formatlash
        $response = $simpleEmployees->map(function ($simpleEmployee) {
            return [
                'id' => $simpleEmployee->id,
                'first_name' => $simpleEmployee->employ->first_name ?? null,
                'last_name' => $simpleEmployee->employ->last_name ?? null,
                'surname' => $simpleEmployee->employ->surname ?? null,
                'email' => $simpleEmployee->employ->email ?? null,
                'phone' => $simpleEmployee->employ->phone ?? null,
                'birthday' => $simpleEmployee->employ->birthday ?? null,
                'gender' => $simpleEmployee->employ->gender ?? null,
                'status' => $simpleEmployee->employ->status ?? null,
                'photo' => $simpleEmployee->employ->photo ?? null,
                'department' => $simpleEmployee->department->name ?? null,
                'position' => $simpleEmployee->position->name ?? null,
                'employ_form' => $simpleEmployee->employ_form->name ?? null,
                'employ_staff' => $simpleEmployee->employ_staff->name ?? null,
                'employ_type' => $simpleEmployee->employ_type->name ?? null,
                'employ_meta' => $simpleEmployee // To'liq EmployMeta obyektini qo'shish
            ];
        });

        // JSON formatida ma'lumotlarni qaytarish
        return response()->json($response);
    }
    /**
     * Lavozim bo'yicha xodimlar.
     * ?position=4        — lavozim ID bo'yicha (standart: 4 — bo'lim boshlig'i)
     * ?position=tyutor   — lavozim nomi bo'yicha (uz/ru/en ichida qidiradi)
     */
    public function showEmployeesByPosition(Request $request)
    {
        $position = $request->query('position', 4);

        return response()->json($this->employeesByPosition($position));
    }

    /** Tyutorlar ro'yxati — /tutors. */
    public function getTutors()
    {
        return response()->json($this->employeesByPosition('tyutor'));
    }

    /** Lavozimlar ro'yxati — filtr uchun. */
    public function getPositions()
    {
        $locale = app()->getLocale();

        $positions = Position::orderBy('order')->get()->map(function ($position) use ($locale) {
            return [
                'id' => $position->id,
                'name' => $position->name[$locale] ?? null,
            ];
        });

        return response()->json(['data' => $positions]);
    }

    /** Lavozim ID yoki nomi bo'yicha xodimlarni olish. */
    private function employeesByPosition($position)
    {
        $locale = app()->getLocale();

        $employees = Employ::whereHas('employMeta', function ($query) use ($position) {
            $query->whereHas('position', function ($subQuery) use ($position) {
                if (is_numeric($position)) {
                    $subQuery->where('id', (int) $position);
                } else {
                    $subQuery->where('name', 'like', '%' . $position . '%');
                }
            });
        })->with(['employMeta' => function ($query) {
            $query->with(['department.structureType', 'department.parent', 'position', 'employ_type']);
        }])->get();

        // Boshqa endpointlar kabi joriy tilga siqib qaytaramiz.
        return $employees->map(function ($employ) use ($locale) {
            $meta = $employ->employMeta;
            $department = $meta?->department;

            return [
                'id'         => $employ->id,
                'slug'       => $meta?->slug ?? $employ->slug,
                'first_name' => data_get($employ->first_name, $locale),
                'last_name'  => data_get($employ->last_name, $locale),
                'surname'    => data_get($employ->surname, $locale),
                'full_name'  => trim(implode(' ', array_filter([
                    data_get($employ->last_name, $locale),
                    data_get($employ->first_name, $locale),
                    data_get($employ->surname, $locale),
                ]))),
                'position'   => data_get($employ->position, $locale),
                'work_time'  => data_get($employ->work_time, $locale),
                'dec'        => data_get($employ->dec, $locale),
                'address'    => data_get($employ->address, $locale),
                'email'      => $employ->email,
                'phone'      => $employ->phone,
                'gender'     => $employ->gender,
                'leader'     => (bool) $employ->leader,
                'professor'  => (bool) $employ->professor,
                'photo'      => $employ->photo ? url('/upload/images/' . $employ->photo) : null,
                'department' => $department ? [
                    'id'   => $department->id,
                    'slug' => $department->slug,
                    'name' => data_get($department->name, $locale),
                    'structure_type' => $department->structureType ? [
                        'id'   => $department->structureType->id,
                        'name' => data_get($department->structureType->name, $locale),
                    ] : null,
                ] : null,
                'position_ref' => $meta?->position ? [
                    'id'   => $meta->position->id,
                    'name' => data_get($meta->position->name, $locale),
                ] : null,
            ];
        })->values();
    }


    public function getfakultet()
    {
        $locale = app()->getLocale(); // Get the current locale

        $faculted = Department::where('structure_type_id',3)->get();
        return $faculted;
    }

//    public function showfakultet($id)
//    {
//        $locale = app()->getLocale(); // Get the current locale
//
//        $employeeBoss = Employ::whereHas('employMeta',function ($query) use ($id){
//            $query->whereHas('department', function ($subQuery) use ($id){
//                $subQuery->where('id', $id);
//            })->whereHas('position', function ($subQuery){
//                $subQuery->where('active', 1)->where('id',8);
//            });
//        })->with(['employMeta'=>function ($query) {
//            $query->with(['department.structureType','department.parent','position']);
//        }])->first();
//        $simpleEmployees = Employ::whereHas('employMeta',function ($query) use ($id){
//            $query->whereHas('department', function ($subQuery) use ($id){
//                $subQuery->where('id', $id);
//            })->whereHas('position', function ($subQuery){
//                $subQuery->where('active', 1)->where('id',9);
//            });
//        })->with(['employMeta'=>function ($query) {
//            $query->with(['department.structureType','department.parent','position']);
//        }])->get();
//        $employees =  [
//            'department_boss' => $employeeBoss,
//            'simple_employee'=> $simpleEmployees,
//        ];
//        return response()->json($employees);
//    }

    public function showfakultet($slug)
    {
        $locale = app()->getLocale(); // Hozirgi tilni olish

        // Fakultet rahbari (Department Boss)ni topish
        $employeeBoss = Employ::whereHas('employMeta', function ($query) use ($slug) {
            $query->whereHas('department', function ($subQuery) use ($slug) {
                $subQuery->where('slug', $slug); // ID o'rniga SLUG orqali qidirish
            });
        })->where('leader', 1)->whereHas('employMeta.position', function ($query) {
            $query->where('active', 1);
        })->with(['employMeta' => function ($query) use ($slug) {
            // Xodim bir nechta bo'limda bo'lishi mumkin — shu sahifaga
            // tegishli tayinlov olinadi, birinchisi emas.
            $query->whereHas('department', fn ($q) => $q->where('slug', $slug))
                ->with(['department.structureType', 'department.parent', 'position','employ_type']);
        }])->first();

        // Oddiy hodimlar (Simple Employees)ni topish
        $simpleEmployees = Employ::whereHas('employMeta', function ($query) use ($slug) {
            $query->whereHas('department', function ($subQuery) use ($slug) {
                $subQuery->where('slug', $slug); // ID o'rniga SLUG orqali qidirish
            });
        })->where('leader', '!=', 1)->whereHas('employMeta.position', function ($query) {
            $query->where('active', 1);
        })->with(['employMeta' => function ($query) use ($slug) {
            $query->whereHas('department', fn ($q) => $q->where('slug', $slug))
                ->with(['department.structureType', 'department.parent', 'position']);
        }])->get();

        // JSON javob qaytarish
        return response()->json([
            'department_boss' => $employeeBoss,
            'simple_employee' => $simpleEmployees,
        ]);
    }

    public function showfakultetuser($slug)
    {
        // EmployMeta modelini slug orqali olish va barcha bog'liq ma'lumotlarni yuklash
        $simpleEmployee = EmployMeta::with([
            'employ',
            'department',
            'position',
            'employ_form',
            'employ_staff',
            'employ_type'
        ])->where('slug', $slug)->first();

        // Agar ma'lumot topilmasa, 404 xato qaytarish
        if (!$simpleEmployee) {
            return response()->json(['message' => 'Employ meta topilmadi'], 404);
        }

        // Topilgan ma'lumotlarni JSON formatida qaytarish
        return response()->json([
            'id' => $simpleEmployee->id,
            'first_name' => $simpleEmployee->employ->first_name ?? null,
            'last_name' => $simpleEmployee->employ->last_name ?? null,
            'surname' => $simpleEmployee->employ->surname ?? null,
            'email' => $simpleEmployee->employ->email ?? null,
            'phone' => $simpleEmployee->employ->phone ?? null,
            'birthday' => $simpleEmployee->employ->birthday ?? null,
            'gender' => $simpleEmployee->employ->gender ?? null,
            'status' => $simpleEmployee->employ->status ?? null,
            'photo' => $simpleEmployee->employ->photo ?? null,
            'department' => $simpleEmployee->department->name ?? null,
            'position' => $simpleEmployee->position->name ?? null,
            'employ_form' => $simpleEmployee->employ_form->name ?? null,
            'employ_staff' => $simpleEmployee->employ_staff->name ?? null,
            'employ_type' => $simpleEmployee->employ_type->name ?? null,
            'employ_meta' => $simpleEmployee
        ]);
    }

    public function getKafedralar()
    {
        $locale = app()->getLocale(); // Get the current locale

        $faculted = Department::where('structure_type_id',4)->get();
        return $faculted;
    }

    public function showKafedralar($slug)
    {
        $locale = app()->getLocale(); // Get the current locale

        $employeeBoss = Employ::whereHas('employMeta', function ($query) use ($slug) {
            $query->whereHas('department', function ($subQuery) use ($slug) {
                $subQuery->where('slug', $slug);
            });
        })->where('leader', 1)->whereHas('employMeta.position', function ($query) {
            $query->where('active', 1);
        })->with(['employMeta' => function ($query) use ($slug) {
            // Xodim bir nechta bo'limda bo'lishi mumkin — shu sahifaga
            // tegishli tayinlov olinadi, birinchisi emas.
            $query->whereHas('department', fn ($q) => $q->where('slug', $slug))
                ->with(['department.structureType', 'department.parent', 'position','employ_type']);
        }])->first();

        $simpleEmployees = Employ::whereHas('employMeta', function ($query) use ($slug) {
            $query->whereHas('department', function ($subQuery) use ($slug) {
                $subQuery->where('slug', $slug);
            });
        })->where('leader', '!=', 1)->whereHas('employMeta.position', function ($query) {
            $query->where('active', 1);
        })->with(['employMeta' => function ($query) {
            $query->with(['department.structureType', 'department.parent', 'position','employ_type']);
        }])->get();

        $employees = [
            'department_boss' => $employeeBoss,
            'simple_employee' => $simpleEmployees,
        ];

        return response()->json($employees);
    }
    public function showkafedralaruser($slug)
    {
        // EmployMeta modelini slug orqali olish va bog'liq ma'lumotlarni yuklash
        $simpleEmployee = EmployMeta::with([
            'employ',
            'department',
            'position',
            'employ_form',
            'employ_staff',
            'employ_type'
        ])->where('slug', $slug)->first();

        // Agar ma'lumot topilmasa, 404 xato qaytarish
        if (!$simpleEmployee) {
            return response()->json(['message' => 'Employ meta topilmadi'], 404);
        }

        // Topilgan ma'lumotlarni JSON formatida qaytarish
        return response()->json([
            'id' => $simpleEmployee->id,
            'first_name' => $simpleEmployee->employ->first_name ?? null,
            'last_name' => $simpleEmployee->employ->last_name ?? null,
            'surname' => $simpleEmployee->employ->surname ?? null,
            'email' => $simpleEmployee->employ->email ?? null,
            'phone' => $simpleEmployee->employ->phone ?? null,
            'birthday' => $simpleEmployee->employ->birthday ?? null,
            'gender' => $simpleEmployee->employ->gender ?? null,
            'status' => $simpleEmployee->employ->status ?? null,
            'photo' => $simpleEmployee->employ->photo ?? null,
            'department' => $simpleEmployee->department->name ?? null,
            'position' => $simpleEmployee->position->name ?? null,
            'employ_form' => $simpleEmployee->employ_form->name ?? null,
            'employ_staff' => $simpleEmployee->employ_staff->name ?? null,
            'employ_type' => $simpleEmployee->employ_type->name ?? null,
            'employ_meta' => $simpleEmployee
        ]);
    }
}
