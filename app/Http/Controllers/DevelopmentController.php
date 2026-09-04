<?php

namespace App\Http\Controllers;

use App\Models\Development;
use App\Models\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class DevelopmentController extends Controller
{
    public $title = 'Ishlanmalar';
    public $route_name = 'developments';
    public $route_parameter = 'development';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $developments = Development::latest()
            ->paginate(12);
        $languages = Lang::all();

        return view('app.developments.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'developments' => $developments,
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

        return view('app.developments.create', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs
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

        if (isset($data['dropzone_images'])) {
            $data['img'] = $data['dropzone_images'];
        }

        Development::create($data);

        return redirect()->route('developments.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Development  $development
     * @return \Illuminate\Http\Response
     */
    public function show(Development $development)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Development  $development
     * @return \Illuminate\Http\Response
     */
    public function edit(Development $development)
    {
        $langs = Lang::all();

        return view('app.developments.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'development' => $development
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Development  $development
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Development $development)
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

        if (isset($data['dropzone_images'])) {
            $data['img'] = $data['dropzone_images'];
        } else {
            $data['img'] = null;
        }

        $development->update($data);

        return redirect()->route('developments.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Development  $development
     * @return \Illuminate\Http\Response
     */
    public function destroy(Development $development)
    {
        $development->delete();

        return back()->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli oʻchirildi'
        ]);
    }
}
