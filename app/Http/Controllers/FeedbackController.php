<?php

namespace App\Http\Controllers;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\Lang;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public $title = 'Fikrlar';
    public $route_name = 'feedbacks';
    public $route_parameter = 'feedback';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $feedbacks = Feedback::latest()
            ->paginate(12);
        $languages = Lang::all();

        return view('app.feedbacks.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'feedbacks' => $feedbacks,
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

        return view('app.feedbacks.create', [
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
            'feedback.' . $this->main_lang->code => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan'
            ]);
        }

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');

            $logo_name = Str::random(12) . '.' . $logo->extension();
            $saved_img = $logo->move(public_path('/upload/feedbacks'), $logo_name);

            Image::make($saved_img)->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save(public_path() . '/upload/feedbacks/200/' . $logo_name, 60);
            Image::make($saved_img)->resize(600, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save(public_path() . '/upload/feedbacks/600/' . $logo_name, 80);

            $data['logo'] = $logo_name;
        }
        if ($request->hasFile('img')) {
            $img = $request->file('img');

            $img_name = Str::random(12) . '.' . $img->extension();
            $saved_img = $img->move(public_path('/upload/feedbacks'), $img_name);

            Image::make($saved_img)->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save(public_path() . '/upload/feedbacks/200/' . $img_name, 60);
            Image::make($saved_img)->resize(600, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save(public_path() . '/upload/feedbacks/600/' . $img_name, 80);

            $data['img'] = $img_name;
        }

        Feedback::create($data);

        return redirect()->route('feedbacks.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function show(Feedback $feedback)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function edit(Feedback $feedback)
    {
        $langs = Lang::all();

        return view('app.feedbacks.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'feedback' => $feedback
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Feedback $feedback)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'feedback.' . $this->main_lang->code => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan'
            ]);
        }

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');

            $logo_name = Str::random(12) . '.' . $logo->extension();
            $saved_img = $logo->move(public_path('/upload/feedbacks'), $logo_name);

            Image::make($saved_img)->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save(public_path() . '/upload/feedbacks/200/' . $logo_name, 60);
            Image::make($saved_img)->resize(600, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save(public_path() . '/upload/feedbacks/600/' . $logo_name, 80);

            $data['logo'] = $logo_name;
        }
        if ($request->hasFile('img')) {
            $img = $request->file('img');

            $img_name = Str::random(12) . '.' . $img->extension();
            $saved_img = $img->move(public_path('/upload/feedbacks'), $img_name);

            Image::make($saved_img)->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save(public_path() . '/upload/feedbacks/200/' . $img_name, 60);
            Image::make($saved_img)->resize(600, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save(public_path() . '/upload/feedbacks/600/' . $img_name, 80);

            $data['img'] = $img_name;
        }

        $feedback->update($data);

        return redirect()->route('feedbacks.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function destroy(Feedback $feedback)
    {
        $feedback->delete();

        return redirect()->route('feedbacks.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli oʻchirildi'
        ]);
    }
}
