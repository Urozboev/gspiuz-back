<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employ;
use App\Models\EmployMeta;
use App\Models\Lang;
use App\Models\Position;
use App\Models\StructureType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DepartamentController extends Controller
{
    public $title = 'Boʻlim, fakultet va kafedralar';
    public $route_name = 'departaments';
    public $route_parameter = 'departament';

    /** Formadan qabul qilinadigan maydonlar. */
    private const FIELDS = [
        'name', 'description', 'address', 'work_time',
        'structure_type_id', 'parent_id', 'active', 'code', 'slug',
        'phone', 'email', 'order',
    ];

    public function index()
    {
        $query = Department::query()->with('structureType')->withCount([
            'assignments as staff_count' => fn ($q) => $q->where('active', 1),
        ]);

        if ($search = trim((string) request('search'))) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Tur boʻyicha filtr: menyudagi "Boshqarma va boʻlimlar",
        // "Fakultetlar", "Kafedralar" alohida koʻrinishi uchun.
        if ($type = (int) request('type')) {
            $query->where('structure_type_id', $type);
        }

        $departaments = $query->orderBy('structure_type_id')->orderBy('order')->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.departaments.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'departaments' => $departaments,
            'StructureTypes' => StructureType::where('active', 1)->orderBy('order')->get(),
            'languages' => Lang::all(),
            'search' => request('search', ''),
            'type' => (int) request('type'),
        ]);
    }

    public function create()
    {
        return view('admin.departaments.create', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'departaments' => Department::orderBy('id')->get(),
            'StructureTypes' => StructureType::where('active', 1)->orderBy('order')->get(),
            'langs' => Lang::all(),
            'type' => (int) request('type'),
        ]);
    }

    public function store(Request $request)
    {
        if ($failed = $this->validateRequest($request)) {
            return $failed;
        }

        $data = $this->payload($request);
        $data['slug'] = $this->uniqueSlug($request->input('slug') ?: $data['name'][$this->main_lang->code]);
        $data['active'] = $data['active'] ?? 1;

        $department = Department::create($data);

        // Yangi boʻlim yaratilgach toʻgʻridan-toʻgʻri uning sahifasiga
        // qaytamiz — xodim qoʻshish shu yerda.
        return redirect()->route('departaments.edit', $department->id)->with([
            'success' => true,
            'message' => 'Saqlandi. Endi oʻng tomondan xodimlarni qoʻshishingiz mumkin.',
        ]);
    }

    public function show(string $id)
    {
        return redirect()->route('departaments.edit', $id);
    }

    public function edit(Department $departament)
    {
        $staff = EmployMeta::where('department_id', $departament->id)
            ->with(['employ', 'position'])
            ->orderBy('position_id')
            ->orderBy('order')
            ->get()
            ->filter(fn (EmployMeta $meta) => $meta->employ !== null)
            ->values();

        return view('admin.departaments.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => Lang::all(),
            'departament' => $departament,
            'departaments' => Department::where('id', '!=', $departament->id)->orderBy('id')->get(),
            'StructureTypes' => StructureType::where('active', 1)->orderBy('order')->get(),
            'staff' => $staff,
            'employees' => $this->employeeOptions(),
            'positions' => Position::where('active', 1)->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        if ($failed = $this->validateRequest($request)) {
            return $failed;
        }

        $departament = Department::findOrFail($id);

        $data = $this->payload($request);

        $slug = trim((string) $request->input('slug'));
        $data['slug'] = $slug !== '' && $slug !== $departament->slug
            ? $this->uniqueSlug($slug, $departament->id)
            : $departament->slug;

        $departament->update($data);

        return redirect()->route('departaments.edit', $departament->id)->with([
            'success' => true,
            'message' => 'Saqlandi',
        ]);
    }

    public function destroy($id)
    {
        Department::findOrFail($id)->delete();

        return redirect()->route('departaments.index')->with([
            'success' => true,
            'message' => 'Oʻchirildi',
        ]);
    }

    // ------------------------------------------------------------------
    // Boʻlim xodimlari
    // ------------------------------------------------------------------

    /**
     * Mavjud xodimni shu boʻlimga biriktiradi.
     *
     * Xodim bitta yozuv boʻlib qoladi — ikkinchi boʻlimga qoʻshish uni
     * nusxalamaydi, faqat yangi tayinlov yaratadi. Ism, rasm yoki
     * tarjimai hol bir joyda oʻzgartirilsa, hamma boʻlimda yangilanadi.
     */
    public function staffStore(Request $request, Department $departament)
    {
        $data = $request->validate([
            'employ_id' => 'required|exists:employs,id',
            'position_id' => 'required|exists:positions,id',
            'order' => 'nullable|integer|min:0',
        ], [], [
            'employ_id' => 'xodim',
            'position_id' => 'lavozim',
        ]);

        $exists = EmployMeta::where('department_id', $departament->id)
            ->where('employ_id', $data['employ_id'])
            ->exists();

        if ($exists) {
            return back()->with([
                'success' => false,
                'message' => 'Bu xodim shu boʻlimda allaqachon bor.',
            ]);
        }

        $this->assign((int) $data['employ_id'], $departament->id, (int) $data['position_id'], (int) ($data['order'] ?? 0));

        return back()->with(['success' => true, 'message' => 'Xodim qoʻshildi']);
    }

    /** Xodimni shu boʻlimdan chiqaradi. Xodimning oʻzi oʻchmaydi. */
    public function staffDestroy(Department $departament, EmployMeta $assignment)
    {
        abort_unless($assignment->department_id === $departament->id, 404);

        $assignment->delete();

        return back()->with(['success' => true, 'message' => 'Xodim boʻlimdan chiqarildi']);
    }

    /** Yangi tayinlov — boshqa talab qilinadigan maydonlar sukut boʻyicha. */
    public static function assign(int $employId, int $departmentId, int $positionId, int $order = 0, ?int $id = null): EmployMeta
    {
        $employ = Employ::find($employId);

        $base = Str::slug(($employ ? $employ->displayName() : 'xodim') . '-' . $departmentId) ?: 'tayinlov';
        $slug = $base;
        $i = 2;

        while (EmployMeta::withTrashed()->where('slug', $slug)->when($id, fn ($q) => $q->where('id', '!=', $id))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return EmployMeta::create([
            'employ_id' => $employId,
            'department_id' => $departmentId,
            'position_id' => $positionId,
            'employ_staff_id' => 1,
            'employ_form_id' => 1,
            'employ_type_id' => 1,
            'active' => 1,
            'order' => $order,
            'slug' => $slug,
        ]);
    }

    // ------------------------------------------------------------------

    private function validateRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name.' . $this->main_lang->code => 'required|string|max:255',
            'structure_type_id' => 'required|exists:structure_types,id',
            'email' => 'nullable|email|max:150',
            'order' => 'nullable|integer|min:0',
        ], [], [
            'name.' . $this->main_lang->code => 'nomi',
            'structure_type_id' => 'tuzilma turi',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator)->with([
                'success' => false,
                'message' => 'Maydonlarni tekshiring: ' . $validator->errors()->first(),
            ]);
        }

        return null;
    }

    /** Formadan kelgan qiymatlar — faqat ruxsat etilgan maydonlar. */
    private function payload(Request $request): array
    {
        $data = $request->only(self::FIELDS);

        $data['parent_id'] = $request->filled('parent_id') ? (int) $request->input('parent_id') : null;
        $data['order'] = (int) $request->input('order', 0);

        if ($request->has('active')) {
            $data['active'] = (int) $request->boolean('active');
        }

        // Rasm faqat yangisi yuklanganda yoki ataylab oʻchirilganda
        // oʻzgaradi. Maydon yuborilmasa eski rasm saqlanib qoladi.
        if ($request->boolean('delete_image')) {
            $data['image'] = null;
        } elseif ($request->filled('dropzone_images')) {
            $data['image'] = $request->input('dropzone_images');
        }

        unset($data['slug']);

        return $this->blankToNull($data, ['description', 'address', 'work_time', 'phone', 'email', 'code']);
    }

    private function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source, '-') ?: 'bolim';
        $slug = $base;
        $i = 2;

        while (Department::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /** Tanlov roʻyxati uchun xodimlar — toʻliq ism bilan, alifbo tartibida. */
    private function employeeOptions()
    {
        return Employ::orderBy('id')->get(['id', 'first_name', 'last_name', 'surname'])
            ->map(fn (Employ $employ) => ['id' => $employ->id, 'name' => $employ->displayName($this->main_lang->code)])
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }
}
