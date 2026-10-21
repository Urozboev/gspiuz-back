<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employ;
use App\Models\EmployMeta;
use App\Models\Lang;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmployController extends Controller
{
    public $title = 'Xodimlar';
    public $route_name = 'employs';
    public $route_parameter = 'employs';

    /** Uch tilli maydonlar — boʻsh kelsa `null` qilib yoziladi. */
    private const TRANSLATED = ['first_name', 'last_name', 'surname', 'address', 'work_time', 'position', 'dec'];

    /** Bitta qiymatli maydonlar — boʻsh kelsa `null`. */
    private const SCALAR = ['email', 'phone', 'birthday', 'start_time', 'status', 'gender'];

    public function index()
    {
        $query = Employ::query()->with(['employMetas' => fn ($q) => $q->with('department')]);

        // Ism, familiya yoki otasining ismi boʻyicha. Ilgari faqat ism
        // boʻyicha qidirilardi — familiya yozilsa hech narsa topilmasdi.
        if ($search = trim((string) request('search'))) {
            $query->where(function ($q) use ($search) {
                foreach (['first_name', 'last_name', 'surname'] as $field) {
                    $q->orWhere($field, 'like', '%' . $search . '%');
                }
            });
        }

        // Boʻlimsiz xodimlar — "hech qayerda koʻrinmayotganlar".
        if (request('unassigned')) {
            $query->whereDoesntHave('employMetas');
        }

        return view('admin.employs.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'employs' => $query->latest()->paginate(20)->withQueryString(),
            'languages' => Lang::all(),
            'search' => request('search', ''),
            'unassigned' => (bool) request('unassigned'),
        ]);
    }

    public function create()
    {
        return view('admin.employs.create', $this->formData() + [
            'assignments' => collect(),
        ]);
    }

    public function store(Request $request)
    {
        if ($failed = $this->validateRequest($request)) {
            return $failed;
        }

        $data = $this->payload($request);

        if ($request->filled('dropzone_images')) {
            $data['photo'] = $request->input('dropzone_images');
        }

        $data['slug'] = $this->uniqueSlug($this->fullName($data));

        $employ = DB::transaction(function () use ($data, $request) {
            $employ = Employ::create($data);
            $this->syncAssignments($employ, $request);

            return $employ;
        });

        return redirect()->route('employs.edit', $employ->id)->with([
            'success' => true,
            'message' => 'Xodim saqlandi',
        ]);
    }

    public function show($id)
    {
        return redirect()->route('employs.edit', $id);
    }

    public function edit($id)
    {
        $employ = Employ::findOrFail($id);

        return view('admin.employs.edit', $this->formData() + [
            'employ' => $employ,
            'assignments' => EmployMeta::where('employ_id', $employ->id)
                ->with(['department', 'position'])
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        if ($failed = $this->validateRequest($request)) {
            return $failed;
        }

        $employ = Employ::findOrFail($id);

        $data = $this->payload($request);

        // Rasm brauzerda JavaScript orqali formaga qoʻshiladi. Uni olib
        // tashlash ham shu maydon orqali boʻladi (maydon yoʻqoladi).
        $data['photo'] = $request->filled('dropzone_images') ? $request->input('dropzone_images') : null;

        $slug = trim((string) $request->input('slug'));
        if ($slug !== '' && $slug !== $employ->slug) {
            $data['slug'] = $this->uniqueSlug($slug, $employ->id);
        }

        DB::transaction(function () use ($employ, $data, $request) {
            $employ->update($data);
            $this->syncAssignments($employ, $request);
        });

        return redirect()->route('employs.edit', $employ->id)->with([
            'success' => true,
            'message' => 'Saqlandi',
        ]);
    }

    public function destroy($id)
    {
        $employ = Employ::find($id);

        if (!$employ) {
            return back()->with(['success' => false, 'message' => 'Xodim topilmadi']);
        }

        DB::transaction(function () use ($employ) {
            EmployMeta::where('employ_id', $employ->id)->delete();
            $employ->delete();
        });

        return back()->with(['success' => true, 'message' => 'Xodim oʻchirildi']);
    }

    // ------------------------------------------------------------------

    /**
     * Xodimning ish joylari: qaysi boʻlimda, qaysi lavozimda.
     *
     * Bitta xodim bir nechta boʻlimda ishlashi mumkin — bu yerda u
     * bitta yozuv boʻlib qoladi, faqat tayinlovlar koʻpayadi.
     *
     * Formada `assignments_present` belgisi boʻlmasa hech narsa
     * oʻzgartirilmaydi. Sahifa formasidagi xato shuni oʻrgatdi: formada
     * kelmagani "oʻchirilgan" deb hisoblansa, boshqa joydan saqlash
     * tayinlovlarni yoʻqotib yuboradi.
     */
    private function syncAssignments(Employ $employ, Request $request): void
    {
        if (!$request->boolean('assignments_present')) {
            return;
        }

        $rows = collect($request->input('assignments', []))
            ->filter(fn ($row) => !empty($row['department_id']) && !empty($row['position_id']))
            ->values();

        $kept = [];

        foreach ($rows as $row) {
            $attributes = [
                'department_id' => (int) $row['department_id'],
                'position_id' => (int) $row['position_id'],
                'order' => (int) ($row['order'] ?? 0),
                'active' => isset($row['active']) ? (int) (bool) $row['active'] : 1,
            ];

            $existing = !empty($row['id'])
                ? EmployMeta::where('employ_id', $employ->id)->find((int) $row['id'])
                : null;

            if ($existing) {
                $existing->update($attributes);
                $kept[] = $existing->id;

                continue;
            }

            // Bir boʻlimga ikki marta biriktirilmasin.
            $duplicate = EmployMeta::where('employ_id', $employ->id)
                ->where('department_id', $attributes['department_id'])
                ->whereNotIn('id', $kept)
                ->first();

            if ($duplicate) {
                $duplicate->update($attributes);
                $kept[] = $duplicate->id;

                continue;
            }

            $kept[] = DepartamentController::assign(
                $employ->id,
                $attributes['department_id'],
                $attributes['position_id'],
                $attributes['order']
            )->id;
        }

        EmployMeta::where('employ_id', $employ->id)->whereNotIn('id', $kept)->delete();
    }

    private function formData(): array
    {
        return [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => Lang::all(),
            'departments' => Department::with('structureType')
                ->orderBy('structure_type_id')
                ->orderBy('order')
                ->orderBy('id')
                ->get(),
            'positions' => Position::where('active', 1)->orderBy('id')->get(),
        ];
    }

    private function validateRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name.' . $this->main_lang->code => 'required|string|max:255',
            'email' => 'nullable|email|max:150',
            'assignments.*.department_id' => 'nullable|exists:departments,id',
            'assignments.*.position_id' => 'nullable|exists:positions,id',
        ], [], [
            'first_name.' . $this->main_lang->code => 'ismi',
            'email' => 'elektron pochta',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator)->with([
                'success' => false,
                'message' => 'Maydonlarni tekshiring: ' . $validator->errors()->first(),
            ]);
        }

        return null;
    }

    private function payload(Request $request): array
    {
        $fields = array_merge(self::TRANSLATED, self::SCALAR, ['special', 'professor', 'leader']);

        $data = $request->only($fields);

        return $this->blankToNull($data, array_merge(self::TRANSLATED, self::SCALAR));
    }

    private function fullName(array $data): string
    {
        $lang = $this->main_lang->code;

        return trim(implode(' ', array_filter([
            $data['last_name'][$lang] ?? null,
            $data['first_name'][$lang] ?? null,
            $data['surname'][$lang] ?? null,
        ])));
    }

    private function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source, '-') ?: 'xodim';
        $slug = $base;
        $i = 2;

        while (Employ::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
