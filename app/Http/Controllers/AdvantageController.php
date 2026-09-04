<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\Lang;
use App\Models\Advantage;
use App\Models\AdvantageCategory;
use Illuminate\Http\Request;

class AdvantageController extends Controller
{
    public $title = 'Afzalliklar';
    public $route_name = 'advantages';
    public $route_parameter = 'advantage';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $advantages = Advantage::latest()
            ->paginate(12);
        $languages = Lang::all();

        return view('app.advantages.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'advantages' => $advantages,
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

        return view('app.advantages.create', [
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
            'title.' . $this->main_lang->code => 'required'
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

        Advantage::create($data);

        return redirect()->route($this->route_name . '.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Advantage  $advantage
     * @return \Illuminate\Http\Response
     */
    public function show(Advantage $advantage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Advantage  $advantage
     * @return \Illuminate\Http\Response
     */
    public function edit(Advantage $advantage)
    {
        $langs = Lang::all();
        $all_categories = AdvantageCategory::all();

        return view('app.advantages.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'all_categories' => $all_categories,
            'advantage' => $advantage
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Advantage  $advantage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Advantage $advantage)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'title.' . $this->main_lang->code => 'required'
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
        
        $advantage->update($data);

        return redirect()->route('advantages.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Advantage  $advantage
     * @return \Illuminate\Http\Response
     */
    public function destroy(Advantage $advantage)
    {
        $advantage->delete();

        return redirect()->route('advantages.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli oʻchirildi'
        ]);
    }
}
