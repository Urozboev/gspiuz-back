<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\EmployFor;
use App\Models\EmployType;
use App\Models\Lang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmployTypeController extends Controller
{
    public $title = 'Bandlik turi';
    public $route_name = 'employ_types';
    public $route_parameter = 'employ_type';
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Query yaratamiz
        $employ_typesQuery = EmployType::query();

        // Agar "search" parametri bo'lsa, qidiruv sharti qo'shamiz
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = trim($_GET['search']);
            $employ_typesQuery->where('name', 'like', '%' . $search . '%'); // Menyu sarlavhasida qidirish

        }

        // Pagination va tartib
        $employ_types = $employ_typesQuery->latest()
            ->paginate(12);

        // Mavjud tillar
        $languages = Lang::all();

        // View qaytariladi
        return view('admin.employ_types.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'employ_types' => $employ_types,
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

        return view('admin.employ_types.create', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
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
//        $data['slug'] = Str::slug($data['name'][$this->main_lang->code], '-');
//        if(EmployType::where('slug', $data['slug'])->exists()) {
//            $data['slug'] = $data['slug'].'-'.time();
//        }
        EmployType::create($data);

        return redirect()->route('employ_types.index')->with([
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
    public function edit($id)
    {
        $langs = Lang::all();
        $employform = EmployType::find($id);


        return view('admin.employ_types.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'employform' => $employform
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

        $employform = EmployType::findOrFail($id);



        $employform->update($data);

        return redirect()->route('employ_types.index')->with([
            'success' => true,
            'message' => 'Updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $employ_type = EmployType::findOrFail($id);
        $employ_type->delete();

        return redirect()->route('employ_types.index')->with([
            'success' => true,
            'message' => 'Deleted successfully'
        ]);
    }
}
