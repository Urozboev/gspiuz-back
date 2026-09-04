<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Lang;
use App\Models\AdvantageCategory;
use Illuminate\Http\Request;

class AdvantageCategoryController extends Controller
{
    public $title = 'Afzallik turkumlari';
    public $route_name = 'advantage_categories';
    public $route_parameter = 'advantage_category';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = AdvantageCategory::latest()
            ->paginate(12);
        $languages = Lang::all();

        return view('app.advantage_categories.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'categories' => $categories,
            'languages' => $languages
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
        $all_categories = AdvantageCategory::all();

        return view('app.advantage_categories.create', [
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
            'title.'.$this->main_lang->code => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan'
            ]);
        }

        AdvantageCategory::create($data);

        return redirect()->route('advantage_categories.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DocumentCategory  $documentCategory
     * @return \Illuminate\Http\Response
     */
    public function show(AdvantageCategory $documentCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DocumentCategory  $documentCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(AdvantageCategory $advantageCategory)
    {
        $langs = Lang::all();

        return view('app.advantage_categories.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'advantageCategory' => $advantageCategory
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DocumentCategory  $documentCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AdvantageCategory $advantageCategory)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'title.'.$this->main_lang->code => 'required'
        ]);
        if ($validator->fails()) {
            return back()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan'
            ]);
        }

        $advantageCategory->update($data);

        return redirect()->route('advantage_categories.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DocumentCategory  $documentCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(AdvantageCategory $advantageCategory)
    {
        $advantageCategory->delete();

        return back()->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli oʻchirildi'
        ]);
    }
}
