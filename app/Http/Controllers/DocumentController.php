<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\Lang;
use App\Models\Document;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public $title = 'Hujjatlar';
    public $route_name = 'documents';
    public $route_parameter = 'document';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $documents = Document::latest()
            ->paginate(12);
        $languages = Lang::all();

        return view('app.documents.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'documents' => $documents,
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

        return view('app.documents.create', [
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
        $data['date'] = isset($data['date']) ? date('Y-m-d', strtotime($data['date'])) : date('Y-m-d');

        $validator = Validator::make($data, [
            'title.' . $this->main_lang->code => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan'
            ]);
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $file_name = Str::random(12) . '.' . $file->extension();
            $file->move(public_path('/upload/documents'), $file_name);

            $data['file'] = $file_name;
        }

        Document::create($data);

        return redirect()->route('documents.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function show(Document $document)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function edit(Document $document)
    {
        $langs = Lang::all();
        $all_categories = DocumentCategory::all();

        return view('app.documents.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'all_categories' => $all_categories,
            'document' => $document
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Document $document)
    {
        $data = $request->all();
        $data['date'] = isset($data['date']) ? date('Y-m-d', strtotime($data['date'])) : date('Y-m-d');

        $validator = Validator::make($data, [
            'title.' . $this->main_lang->code => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan'
            ]);
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $file_name = Str::random(12) . '.' . $file->extension();
            $file->move(public_path('/upload/documents'), $file_name);

            $data['file'] = $file_name;
        }

        $document->update($data);

        return redirect()->route('documents.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function destroy(Document $document)
    {
        $document->delete();

        return redirect()->route('documents.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli oʻchirildi'
        ]);
    }
}
