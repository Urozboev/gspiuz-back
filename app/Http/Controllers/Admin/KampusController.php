<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationalProgram;
use App\Models\Employ;
use App\Models\Kampus;
use App\Models\KampusImg;
use App\Models\Lang;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\PostsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class KampusController extends Controller
{
    public $title = 'Kampuslar';
    public $route_name = 'kampuses';
    public $route_parameter = 'kampus';
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
        $employ_typesQuery = Kampus::query();

        // Agar "search" parametri bo'lsa, qidiruv sharti qo'shamiz
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = trim($_GET['search']);
            $employ_typesQuery->where('name', 'like', '%' . $search . '%'); // Menyu sarlavhasida qidirish

        }

        // Pagination va tartib
        $employs = $employ_typesQuery->latest()
            ->paginate(12);

        // Mavjud tillar
        $languages = Lang::all();

        // View qaytariladi
        return view('admin.kampuses.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'kampuses' => $employs,
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

        return view('admin.kampuses.create', [
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
            'name.' . $this->main_lang->code => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan'
            ]);
        }

        $data['slug'] = Str::slug($data['name'][$this->main_lang->code], '-');

        if (Kampus::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $data['slug'] . '-' . time();
        }

        DB::beginTransaction();
        try {
            $post = Kampus::create([
                'name' => $data['name'] ?? [],
                'first_name' => $data['first_name'] ?? [],
                'second_name' => $data['second_name'] ?? [],
                'third_name' => $data['third_name'] ?? [],
                'first_description' => $data['first_description'] ?? [],
                'second_description' => $data['second_description'] ?? [],
                'third_description' => $data['third_description'] ?? [],
                'educational_programs' => $data['educational_programs'] ?? null,
                'audience_size' => $data['audience_size'] ?? null,
                'green_zone' => $data['green_zone'] ?? null,
                'slug' => $data['slug'] ?? null,
                'map' => $data['map'] ?? null,
                'active' => $data['active'] ?? 0
            ]);

            if (!empty($data['dropzone_images'])) {
                foreach ($data['dropzone_images'] as $img) {
                    KampusImg::create([
                        'kampus_id' => $post->id,
                        'img' => $img
                    ]);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return back()->withInput()->with([
                'success' => false,
                'message' => 'Saqlashda xatolik yuz berdi'
            ]);
        }

        return redirect()->route('kampuses.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
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
        $educationalProgram = Kampus::findOrFail($id);

        $educationalPrograms = EducationalProgram::whereNull('parent_id')->get();

        return view('admin.kampuses.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'kampus' => $educationalProgram,
            'langs' => $langs,
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

        $kampus = Kampus::findOrFail($id);

//        $data['slug'] = Str::slug($data['name'][$this->main_lang->code], '-');
//        if (Kampus::where('slug', $data['slug'])->where('id', '!=', $kampus->id)->exists()) {
//            $data['slug'] = $data['slug'] . '-' . time();
//        }

        DB::beginTransaction();
        try {
            // Ma'lumotlarni yangilash
            $kampus->update([
                'name' => $data['name'] ?? [],
                'first_name' => $data['first_name'] ?? [],
                'second_name' => $data['second_name'] ?? [],
                'third_name' => $data['third_name'] ?? [],
                'first_description' => $data['first_description'] ?? [],
                'second_description' => $data['second_description'] ?? [],
                'third_description' => $data['third_description'] ?? [],
                'educational_programs' => $data['educational_programs'] ?? null,
                'audience_size' => $data['audience_size'] ?? null,
                'green_zone' => $data['green_zone'] ?? null,
                'slug' => $data['slug'] ?? null,
                'map' => $data['map'] ?? null,
                'active' => $data['active'] ?? 0

            ]);

            // Eski rasmlarni o‘chirish va yangilarini qo‘shish
            if (!empty($data['dropzone_images'])) {
                // Eski rasmlarni o‘chirish
                $kampus->kampusImages()->delete();

                // Yangi rasmlarni qo‘shish
                foreach ($data['dropzone_images'] as $img) {
                    KampusImg::create([
                        'kampus_id' => $kampus->id,
                        'img' => $img
                    ]);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return back()->withInput()->with([
                'success' => false,
                'message' => 'Saqlashda xatolik yuz berdi'
            ]);
        }

        return redirect()->route('kampuses.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli yangilandi'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $departament = Kampus::findOrFail($id);
        $departament->delete();

        return redirect()->route('kampuses.index')->with([
            'success' => true,
            'message' => 'Deleted successfully'
        ]);
    }
}
