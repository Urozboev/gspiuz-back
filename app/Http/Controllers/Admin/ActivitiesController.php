<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Brand;
use App\Models\EducationalProgram;
use App\Models\ERskill;
use App\Models\Lang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ActivitiesController extends Controller
{
    public $title = 'Faoliyat';
    public $route_name = 'activities';
    public $route_parameter = 'activitie';
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Query yaratamiz
        $activitie_typesQuery = Activity::query();

        // Agar "search" parametri bo'lsa, qidiruv sharti qo'shamiz
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = trim($_GET['search']);
            $activitie_typesQuery->where('name', 'like', '%' . $search . '%'); // Menyu sarlavhasida qidirish

        }

        // Pagination va tartib
        $activities = $activitie_typesQuery->latest()
            ->paginate(12);

        // Mavjud tillar
        $languages = Lang::all();

        // View qaytariladi
        return view('admin.activities.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'activities' => $activities,
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
        $educationalprogram = EducationalProgram::whereNotNull('parent_id')->get();

        return view('admin.activities.create', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'educationalprogram' => $educationalprogram,
            'langs' => $langs,
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // Kelgan ma'lumotlarni olish
        $data = $request->all();

        // Validatsiya
        $validator = Validator::make($data, [
            'title.' . $this->main_lang->code => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Validation error',
            ]);
        }

        if (isset($data['dropzone_images'])) {
            $data['photo'] = $data['dropzone_images'];
        }


        // Ma'lumotlarni saqlash
        Activity::create($data);

        // Yozuv muvaffaqiyatli saqlangani haqida javob qaytarish
        return redirect()->route('activities.index')->with([
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
       $activitie = Activity::find($id);
        $educationalprogram = EducationalProgram::whereNotNull('parent_id')->get();

        return view('admin.activities.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'activitie' => $activitie,
            'educationalprogram' => $educationalprogram,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'title.' . $this->main_lang->code => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Validation of friend'
            ]);
        }
        $activitie = Activity::find($id);

        if (isset($data['dropzone_images'])) {
            $data['photo'] = $data['dropzone_images'];
        } else {
            $data['photo'] = null;
        }

        $activitie->update($data);

        return redirect()->route('activities.index')->with([
            'success' => true,
            'message' => 'Successfully saved'
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $skill = Activity::findOrFail($id);
        $skill->delete();

        return redirect()->route('activities.index')->with([
            'success' => true,
            'message' => 'Deleted successfully'
        ]);
    }
}
