<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\EducationalProgram;
use App\Models\Employ;
use App\Models\ERskill;
use App\Models\Lang;
use App\Models\PostsCategory;
use Illuminate\Http\Request;
use App\Models\EntranceRequirement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EntranceRequirementController extends Controller
{
    public $title = 'Kirish talablari';
    public $route_name = 'entrance-requirements';
    public $route_parameter = 'entrance-requirement';
    /**
     * Display a listing of the resource.
     */
//    public function index()
//    {
//        // Query yaratamiz
//        $positionQuery = EducationalProgramseed::query();
//
//        // Agar "search" parametri bo'lsa, qidiruv sharti qo'shamiz
//        if (isset($_GET['search']) && !empty($_GET['search'])) {
//            $search = trim($_GET['search']);
//            $positionQuery->where('name', 'like', '%' . $search . '%'); // Menyu sarlavhasida qidirish
//
//        }
//
//        // Pagination va tartib
//        $educational_programs = $positionQuery->latest()
//            ->paginate(12);
//
//        // Mavjud tillar
//        $languages = Lang::all();
//
//        // View qaytariladi
//        return view('admin.educational-programs.index', [
//            'title' => $this->title,
//            'route_name' => $this->route_name,
//            'route_parameter' => $this->route_parameter,
//            'educational_programs' => $educational_programs,
//            'languages' => $languages,
//            'search' => isset($_GET['search']) ? $_GET['search'] : '', // Qidiruv qiymati
//        ]);
//    }

    public function index()
    {
        // Query yaratamiz
        $entranceRequirements_typesQuery = EntranceRequirement::query();

        // Agar "search" parametri bo'lsa, qidiruv sharti qo'shamiz
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = trim($_GET['search']);
            $entranceRequirements_typesQuery->where('name', 'like', '%' . $search . '%'); // Menyu sarlavhasida qidirish

        }

        // Pagination va tartib
        $entranceRequirements = $entranceRequirements_typesQuery->latest()
            ->paginate(12);

        // Mavjud tillar
        $languages = Lang::all();

        // View qaytariladi
        return view('admin.entrance-requirements.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'entranceRequirements' => $entranceRequirements,
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
        $skill = ERskill::query()->get();

        return view('admin.entrance-requirements.create', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'educationalprogram' => $educationalprogram,
            'langs' => $langs,
            'skills' => $skill,
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'name.' . $this->main_lang->code => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan'
            ]);
        }

        DB::beginTransaction();
        try {
            // Ma'lumotlarni saqlash
            $entrancerequirement = EntranceRequirement::create($data);

            // Agar dropzone_images mavjud bo'lsa, uni photo maydoniga yozish
            if (isset($data['dropzone_images'])) {
                $data['photo'] = $data['dropzone_images'];  // Fayl nomini saqlash
                $entrancerequirement->update(['photo' => $data['photo']]);  // photo maydonini yangilash
            }

            // Ko'nikmalarni bog'lash
            if (isset($data['skills'])) {
                $entrancerequirement->skills()->sync($data['skills']);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return back()->withInput()->with([
                'success' => false,
                'message' => 'Transaction error'
            ]);
        }

        return redirect()->route('entrance-requirements.index')->with([
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
        $entranceRequirement = EntranceRequirement::find($id);
        $educationalprogram = EducationalProgram::whereNotNull('parent_id')->get();
        $skills = ERskill::query()->get();

        return view('admin.entrance-requirements.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'entranceRequirement' => $entranceRequirement,
            'educationalprogram' => $educationalprogram,
            'skills' => $skills,
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
        public function update(Request $request, $id)
        {
            $data = $request->all();

            $validator = Validator::make($data, [
                'name.' . $this->main_lang->code => 'required'
            ]);
            if ($validator->fails()) {
                return back()->withInput()->with([
                    'success' => false,
                    'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan'
                ]);
            }
            if (isset($data['dropzone_images'])) {
                $data['photo'] = $data['dropzone_images'];
            } else {
                $data['photo'] = null;
            }


            DB::beginTransaction();
            try {
                $post = EntranceRequirement::findOrFail($id);
                $post->update($data);



                if (isset($data['skills'])) {
                    $post->skills()->sync($data['skills']);
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();

                return back()->withInput()->with([
                    'success' => false,
                    'message' => 'Transaction error'
                ]);
            }

            return redirect()->route('entrance-requirements.index')->with([
                'success' => true,
                'message' => 'Updated successfully'
            ]);
        }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $departament = EntranceRequirement::findOrFail($id);
        $departament->delete();

        return redirect()->route('entrance-requirements.index')->with([
            'success' => true,
            'message' => 'Deleted successfully'
        ]);
    }
}
