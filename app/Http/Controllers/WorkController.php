<?php

namespace App\Http\Controllers;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Service;
use App\Models\Lang;
use App\Models\Work;
use App\Models\WorkImage;
use Exception;
use Illuminate\Http\Request;

class WorkController extends Controller
{
    public $title = 'Fotogalereya';
    public $route_name = 'works';
    public $route_parameter = 'work';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $works = Work::latest()
            ->paginate(12);
        $languages = Lang::all();
        $services = Service::all();

        return view('app.works.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'works' => $works,
            'languages' => $languages,
            'services' => $services
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
        $services = Service::all();

        return view('app.works.create', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'services' => $services
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

        DB::beginTransaction();
        try {
            if ($request->hasFile('main_img')) {
                $main_img = $request->file('main_img');
    
                $logo_name = Str::random(12) . '.' . $main_img->extension();
                $saved_img = $main_img->move(public_path('/upload/images'), $logo_name);

                Image::make($saved_img)->resize(200, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save(public_path() . '/upload/images/200/' . $logo_name, 60);
                Image::make($saved_img)->resize(600, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save(public_path() . '/upload/images/600/' . $logo_name, 80);
    
                $data['main_img'] = $logo_name;
            }
            if ($request->hasFile('video')) {
                $video = $request->file('video');
    
                $logo_name = Str::random(12) . '.' . $video->extension();
                $saved_img = $video->move(public_path('/upload/images'), $logo_name);
    
                $data['video'] = $logo_name;
            }

            $work = Work::create($data);

            if (isset($data['dropzone_images'])) {
                foreach ($data['dropzone_images'] as $img) {
                    WorkImage::create([
                        'work_id' => $work->id,
                        'img' => $img
                    ]);
                }
            }

            if (isset($data['categories'])) {
                $work->services()->sync($data['categories']);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return back()->withInput()->with([
                'success' => false,
                'message' => 'Saqlashda xatolik yuz berdi'
            ]);
        }

        return redirect()->route('works.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Work  $work
     * @return \Illuminate\Http\Response
     */
    public function show(Work $work)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Work  $work
     * @return \Illuminate\Http\Response
     */
    public function edit(Work $work)
    {
        $langs = Lang::all();
        $services = Service::all();

        return view('app.works.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'work' => $work,
            'services' => $services
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Work  $work
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Work $work)
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

        DB::beginTransaction();
        try {
            if ($request->hasFile('main_img')) {
                $main_img = $request->file('main_img');
    
                $logo_name = Str::random(12) . '.' . $main_img->extension();
                $saved_img = $main_img->move(public_path('/upload/images'), $logo_name);
    
                Image::make($saved_img)->resize(200, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save(public_path() . '/upload/images/200/' . $logo_name, 60);
                Image::make($saved_img)->resize(600, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save(public_path() . '/upload/images/600/' . $logo_name, 80);
    
                $data['main_img'] = $logo_name;
            }
            if ($request->hasFile('video')) {
                $video = $request->file('video');
    
                $logo_name = Str::random(12) . '.' . $video->extension();
                $saved_img = $video->move(public_path('/upload/images'), $logo_name);
    
                $data['video'] = $logo_name;
            }

            $work->update($data);

            $work->workImages()->delete();
            if (isset($data['dropzone_images'])) {
                foreach ($data['dropzone_images'] as $img) {
                    WorkImage::create([
                        'work_id' => $work->id,
                        'img' => $img
                    ]);
                }
            }

            if (isset($data['categories'])) {
                $work->services()->sync($data['categories']);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return back()->withInput()->with([
                'success' => false,
                'message' => 'Saqlashda xatolik yuz berdi'
            ]);
        }

        return redirect()->route('works.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Work  $work
     * @return \Illuminate\Http\Response
     */
    public function destroy(Work $work)
    {
        DB::beginTransaction();
        try {
            $work->services()->detach();
            $work->workImages()->delete();
            $work->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with([
                'success' => false,
                'message' => 'Saqlashda xatolik yuz berdi'
            ]);
        }

        return back()->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli oʻchirildi'
        ]);
    }
}
