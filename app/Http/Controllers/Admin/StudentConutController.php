<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployStaff;
use App\Models\Lang;
use App\Models\StudentCount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentConutController extends Controller
{
    public $title = 'Talabalar soni';
    public $route_name = 'student_counts';
    public $route_parameter = 'student_count';
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Query yaratamiz
        $employ_staffQuery = StudentCount::query();

        // Agar "search" parametri bo'lsa, qidiruv sharti qo'shamiz
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = trim($_GET['search']);
            $employ_staffQuery->where('years', 'like', '%' . $search . '%'); // Menyu sarlavhasida qidirish

        }

        // Pagination va tartib
        $employ_staff = $employ_staffQuery->latest()
            ->paginate(12);

        // Mavjud tillar
        $languages = Lang::all();

        // View qaytariladi
        return view('admin.student_counts.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'employ_staff' => $employ_staff,
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

        return view('admin.student_counts.create', [
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
            'years' => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Validation error'
            ]);
        }
        StudentCount::create($data);

        return redirect()->route('student_counts.index')->with([
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
        $employStaff = StudentCount::find($id);


        return view('admin.student_counts.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'employStaff' => $employStaff
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'years' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Validation error'
            ]);
        }

        $employ_staff_one = StudentCount::findOrFail($id);



        $employ_staff_one->update($data);

        return redirect()->route('student_counts.index')->with([
            'success' => true,
            'message' => 'Updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $employ_staff_one = StudentCount::findOrFail($id);
        $employ_staff_one->delete();

        return redirect()->route('student_counts.index')->with([
            'success' => true,
            'message' => 'Deleted successfully'
        ]);
    }
}
