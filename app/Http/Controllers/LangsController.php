<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lang;
use Illuminate\Support\Facades\Validator;

class LangsController extends Controller
{
	public $title = 'Tillar';
	public $route_name = 'langs';
	public $route_parameter = 'lang';
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$langs = Lang::latest()
			->paginate(12);

		return view('app.langs.index', [
			'title' => $this->title,
			'route_name' => $this->route_name,
			'route_parameter' => $this->route_parameter,
			'langs' => $langs
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		return view('app.langs.create', [
			'title' => $this->title,
			'route_name' => $this->route_name,
			'route_parameter' => $this->route_parameter
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
            'title' => 'required|max:255',
            'code' => 'required|max:2|unique:langs',
			'icon' => 'nullable|image|max:2048'
        ]);
        if($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan'
            ]);
        }

		if (isset($data['dropzone_images'])) {
            $data['icon'] = $data['dropzone_images'];
        }

        Lang::create($data);

        return redirect()->route($this->route_name.'.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli qoʻshildi'
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
	public function edit(Lang $lang)
	{
		return view('app.langs.edit', [
			'title' => $this->title,
			'route_name' => $this->route_name,
			'route_parameter' => $this->route_parameter,
			'lang' => $lang
		]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, Lang $lang)
	{
		$data = $request->only('title', 'code', 'dropzone_images');

        $validator = Validator::make($data, [
            'title' => 'required|max:255',
            'code' => 'required|max:2|unique:langs,code,'.$lang->id,
        ]);
        if($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan'
            ]);
        }
		
		if (isset($data['dropzone_images'])) {
            $data['icon'] = $data['dropzone_images'];
        } else {
			$data['icon'] = null;
		}

        $lang->update($data);

        return redirect()->route($this->route_name.'.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Lang $lang)
	{
		if($lang->is_main) {
            return back()->with([
                'success' => false,
                'message' => 'Asosiy tilni oʻchirib boʻlmaydi'
            ]);
        }
        
        $lang->delete();

        return back()->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli oʻchirildi'
        ]);
	}
}
