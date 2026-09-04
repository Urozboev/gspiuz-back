<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employ;
use App\Models\EmployFor;
use App\Models\EmployMeta;
use App\Models\EmployStaff;
use App\Models\EmployType;
use App\Models\Lang;
use App\Models\Position;
use App\Models\StructureType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmployMetaController extends Controller
{
    public $title = 'Xodim lavozimlari';
    public $route_name = 'employ_meta';
    public $route_parameter = 'employ_meta';
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Query yaratamiz
        $positionQuery = EmployMeta::query()->with(['employ', 'department', 'position', 'employ_form', 'employ_staff', 'employ_type']);

        // Agar "search" parametri bo'lsa, qidiruv sharti qo'shamiz
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = trim($_GET['search']);

            $positionQuery->where(function ($query) use ($search) {
                $query->where('contrakt_number', 'like', '%' . $search . '%') // Asosiy maydonlarda qidirish
                ->orWhereHas('employ', function ($q) use ($search) {
                    $q->where('first_name', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('department', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('position', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('employ_form', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('employ_staff', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('employ_type', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Pagination va tartib
        $employ_meta = $positionQuery->latest()->paginate(12);

        // Mavjud tillar
        $languages = Lang::all();

        // View qaytariladi
        return view('admin.employ_meta.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'employ_meta' => $employ_meta,
            'languages' => $languages,
            'search' => isset($_GET['search']) ? $_GET['search'] : '', // Qidiruv qiymati
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $langs = Lang::all();
        $departaments = Department::select('id', 'name')->get();
        $employ = Employ::select('id', 'first_name')->get();
        $position = Position::select('id', 'name')->get();
        $employStaff = EmployStaff::select('id', 'name')->get();
        $employForm = EmployFor::select('id', 'name')->get();
        $employType = EmployType::select('id', 'name')->get();

        return view('admin.employ_meta.create', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'departaments' => $departaments,
            'employ' => $employ,
            'position' => $position,
            'employStaff' => $employStaff,
            'employForm' => $employForm,
            'employType' => $employType,
            'langs' => $langs,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
//    public function store(Request $request)
//    {
//        $validated = $request->validate([
//            'employ_id' => 'required|exists:employs,id',
//            'department_id' => 'required|exists:departments,id',
//            'position_id' => 'required|exists:positions,id',
//            'employ_staff_id' => 'required|exists:employ_staff,id',
//            'employ_form_id' => 'required|exists:employ_fors,id',
//            'contrakt_date' => 'nullable|date',
//            'contrakt_number' => 'nullable|string|max:255',
//            'employ_type_id' => 'required|exists:employ_types,id',
//            'active' => 'required|boolean',
//        ]);
//
//        EmployMeta::create($validated);
//
//        return redirect()->route('employ_meta.index')->with([
//            'success' => true,
//            'message' => 'Saved successfully',
//        ]);
//    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'employ_id' => 'required|exists:employs,id',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'employ_staff_id' => 'required|exists:employ_staff,id',
            'employ_form_id' => 'required|exists:employ_fors,id',
            'contrakt_date' => 'nullable|date',
            'contrakt_number' => 'nullable|string|max:255',
            'employ_type_id' => 'required|exists:employ_types,id',
            'active' => 'required|boolean',
            'order' => 'required|integer',  // Order maydonini integer qilib tekshiramiz
        ]);

        // Employ modelidan name ni olish
        $employ = Employ::find($validated['employ_id']);

        if ($employ) {
            // Slug yaratish
            $slug = Str::slug($employ->first_name['uz'] . ' ' . $employ->last_name['uz'], '-');
            $baseContraktNumber = $slug;

            // Agar contract_number bo‘lsa, uni ishlatamiz
            if (!empty($validated['slug'])) {
                $baseContraktNumber .= '-' . $validated['slug'];
            }

            // Unikal qilish uchun raqam qo‘shish
            $counter = 1;
            $contraktNumber = $baseContraktNumber;

            // Slugni unikal qilish
            while (EmployMeta::where('slug', $contraktNumber)->exists()) {
                $counter++;
                $contraktNumber = $baseContraktNumber . '-' . $counter;
            }

            $validated['slug'] = $contraktNumber;
        }

        // Ma'lumotni saqlash
        EmployMeta::create($validated);

        return redirect()->route('employ_meta.index')->with([
            'success' => true,
            'message' => 'Saved successfully',
        ]);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $langs = Lang::all();
        $departaments = Department::select('id', 'name')->get();
        $employ = Employ::select('id', 'first_name')->get();
        $position = Position::select('id', 'name')->get();
        $employStaff = EmployStaff::select('id', 'name')->get();
        $employForm = EmployFor::select('id', 'name')->get();
        $employType = EmployType::select('id', 'name')->get();
        $employMeta = EmployMeta::find($id);


        return view('admin.employ_meta.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'departaments' => $departaments,
            'employ' => $employ,
            'employMeta' => $employMeta,
            'position' => $position,
            'employStaff' => $employStaff,
            'employForm' => $employForm,
            'employType' => $employType,
            'langs' => $langs,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Ma'lumotlarni tekshirish
        $validated = $request->validate([
            'employ_id' => 'required|exists:employs,id',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'employ_staff_id' => 'required|exists:employ_staff,id',
            'employ_form_id' => 'required|exists:employ_fors,id',
            'contrakt_date' => 'nullable|date',
            'slug' => 'nullable|string',
            'contrakt_number' => 'nullable|string|max:255',
            'employ_type_id' => 'required|exists:employ_types,id',
            'active' => 'required|boolean',
            'order' => 'nullable|integer', // Order maydoni uchun nullable qilindi, agar bo'sh bo'lishi mumkin bo'lsa
        ]);

        // Ma'lumotni topamiz
        $employMeta = EmployMeta::findOrFail($id);

        // 'order' maydonini validatsiya qilingan ma'lumotlarga qo'shish
        if (isset($request->order)) {
            $employMeta->order = $request->order;
        }

        // Ma'lumotni yangilaymiz
        $employMeta->update($validated);

        // Foydalanuvchini qaytaramiz
        return redirect()->route('employ_meta.index')->with([
            'success' => true,
            'message' => 'Updated successfully',
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $departament = EmployMeta::findOrFail($id);
        $departament->delete();

        return redirect()->route('employ_meta.index')->with([
            'success' => true,
            'message' => 'Deleted successfully'
        ]);
    }
}
