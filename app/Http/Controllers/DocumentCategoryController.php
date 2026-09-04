<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Lang;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;

class DocumentCategoryController extends Controller
{
    public $title = 'Hujjat turkumlari';
    public $route_name = 'document_categories';
    public $route_parameter = 'document_category';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = DocumentCategory::latest()
            ->paginate(12);
        $languages = Lang::all();

        return view('app.document_categories.index', [
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
        $all_categories = DocumentCategory::all();

        return view('app.document_categories.create', [
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

        if(isset($data['dropzone_images'])) {
            $data['img'] = $data['dropzone_images'];
        }

        DocumentCategory::create($data);

        return redirect()->route('document_categories.index')->with([
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
    public function show(DocumentCategory $documentCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DocumentCategory  $documentCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(DocumentCategory $documentCategory)
    {
        $langs = Lang::all();
        $all_categories = DocumentCategory::all()->except($documentCategory->id);

        return view('app.document_categories.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'all_categories' => $all_categories,
            'documentCategory' => $documentCategory
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DocumentCategory  $documentCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DocumentCategory $documentCategory)
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

        if (isset($data['dropzone_images'])) {
            $data['img'] = $data['dropzone_images'] ?? null;
        } else {
            $data['img'] = null;
        }

        $documentCategory->update($data);

        return redirect()->route('document_categories.index')->with([
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
    public function destroy(DocumentCategory $documentCategory)
    {
        if(isset($documentCategory->children[0])) {
            return back()->with([
                'success' => false,
                'message' => 'Ichki turkumlari bor. Avval ularni oʻchirish kerak.'
            ]);
        }
        $documentCategory->delete();

        return back()->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli oʻchirildi'
        ]);
    }
}
