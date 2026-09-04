<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employ;
use App\Models\EmployMeta;
use App\Models\Lang;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\PostsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmployController extends Controller
{
    public $title = 'Xodimlar';
    public $route_name = 'employs';
    public $route_parameter = 'employs';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Query yaratamiz
        $employ_typesQuery = Employ::query();

        // Agar "search" parametri bo'lsa, qidiruv sharti qo'shamiz
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = trim($_GET['search']);
            $employ_typesQuery->where('first_name', 'like', '%' . $search . '%'); // Menyu sarlavhasida qidirish

        }

        // Pagination va tartib
        $employs = $employ_typesQuery->latest()
            ->paginate(12);

        // Mavjud tillar
        $languages = Lang::all();

        // View qaytariladi
        return view('admin.employs.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'employs' => $employs,
            'languages' => $languages,
            'search' => isset($_GET['search']) ? $_GET['search'] : '', // Qidiruv qiymati
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $langs = Lang::all();
        $all_categories = PostsCategory::all();

        return view('admin.employs.create', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'all_categories' => $all_categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'first_name.' . $this->main_lang->code => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Validation of friend'
            ]);
        }

        if (isset($data['dropzone_images'])) {
            $data['photo'] = $data['dropzone_images'];
        }
        $full_name = $data['first_name'][$this->main_lang->code] . ' ' .
            $data['last_name'][$this->main_lang->code] . ' ' .
            $data['surname'][$this->main_lang->code];

        $data['slug'] = Str::slug($full_name, '-');

        // Agar slug mavjud bo‘lsa, uni noyob qilish
        if (Employ::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $data['slug'] . '-' . time();
        }

        Employ::create($data);

        return redirect()->route('employs.index')->with([
            'success' => true,
            'message' => 'Saved successfullynnnn             mm'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $langs = Lang::all();
        $employ = Employ::find($id);


        return view('admin.employs.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'employ' => $employ
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'first_name.' . $this->main_lang->code => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Validation of friend'
            ]);
        }
        $employ = Employ::find($id);

        if (isset($data['dropzone_images'])) {
            $data['photo'] = $data['dropzone_images'];
        } else {
            $data['photo'] = null;
        }
        $employ->update($data);

        return redirect()->route('employs.index')->with([
            'success' => true,
            'message' => 'Successfully saved'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Sertifikatni id orqali topish
        $employ = Employ::find($id);

        // Agar sertifikat mavjud bo'lmasa, xatolik xabarini ko'rsatish
        if (!$employ) {
            return back()->with([
                'success' => false,
                'message' => 'what is found'
            ]);
        }

        // Sertifikatni o'chirish
        $employ->delete();

        // Qayta yo'naltirish va muvaffaqiyat xabarini ko'rsatish
        return back()->with([
            'success' => true,
            'message' => 'Successfully deleted'
        ]);
    }
}
