<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployFor;
use App\Models\EmployStaff;
use App\Models\Lang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployFormController extends Controller
{
    public $title = 'Ish shakli';
    public $route_name = 'employ_forms';
    public $route_parameter = 'employ_form';
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Query yaratamiz
        $employ_formsQuery = EmployFor::query();

        // Agar "search" parametri bo'lsa, qidiruv sharti qo'shamiz
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = trim($_GET['search']);
            $employ_formsQuery->where('name', 'like', '%' . $search . '%'); // Menyu sarlavhasida qidirish

        }

        // Pagination va tartib
        $employ_forms = $employ_formsQuery->latest()
            ->paginate(12);

        // Mavjud tillar
        $languages = Lang::all();

        // View qaytariladi
        return view('admin.employ_form.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'employ_forms' => $employ_forms,
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

        return view('admin.employ_form.create', [
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
        EmployFor::create($data);

        return redirect()->route('employ_forms.index')->with([
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
        $employform = EmployFor::find($id);


        return view('admin.employ_form.edit', [
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

        $employform = EmployFor::findOrFail($id);



        $employform->update($data);

        return redirect()->route('employ_forms.index')->with([
            'success' => true,
            'message' => 'Updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $employ_form = EmployFor::findOrFail($id);
        $employ_form->delete();

        return redirect()->route('employ_forms.index')->with([
            'success' => true,
            'message' => 'Deleted successfully'
        ]);
    }
}
