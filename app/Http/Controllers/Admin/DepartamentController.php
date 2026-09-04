<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Lang;
use App\Models\Position;
use App\Models\Post;
use App\Models\StructureType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DepartamentController extends Controller
{
    public $title = 'Fakultet va kafedralar';
    public $route_name = 'departaments';
    public $route_parameter = 'departament';
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Query yaratamiz
        $positionQuery = Department::query();

        // Agar "search" parametri bo'lsa, qidiruv sharti qo'shamiz
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = trim($_GET['search']);
            $positionQuery->where('name', 'like', '%' . $search . '%'); // Menyu sarlavhasida qidirish

        }

        // Pagination va tartib
        $departaments = $positionQuery->latest()
            ->paginate(12);

        // Mavjud tillar
        $languages = Lang::all();

        // View qaytariladi
        return view('admin.departaments.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'departaments' => $departaments,
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
        $departaments = Department::all();
        $StructureTypes = StructureType::where('active', '1')->get();


        return view('admin.departaments.create', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'departaments' => $departaments,
            'StructureTypes' => $StructureTypes,
            'langs' => $langs,
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'name.'.$this->main_lang->code => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Validation error'
            ]);
        }

        $data['slug'] = Str::slug($data['name'][$this->main_lang->code], '-');
        if(Department::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $data['slug'].'-'.time();
        }
        Department::create($data);

        return redirect()->route('departaments.index')->with([
            'success' => true,
            'message' => 'Saved successfully'
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
    public function edit(Department $departament)
    {
        $langs = Lang::all();
        $departaments = Department::all();
        $StructureTypes = StructureType::where('active', '1')->get();
        return view('admin.departaments.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'departament' => $departament,
            'departaments' => $departaments,
            'StructureTypes' => $StructureTypes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'name.'.$this->main_lang->code => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Validation error'
            ]);
        }

        $departament = Department::findOrFail($id);



        $departament->update($data);

        return redirect()->route('departaments.index')->with([
            'success' => true,
            'message' => 'Updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $departament = Department::findOrFail($id);
        $departament->delete();

        return redirect()->route('departaments.index')->with([
            'success' => true,
            'message' => 'Deleted successfully'
        ]);
    }
}
