<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Lang;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public $title = 'Bannerlar';
    public $route_name = 'banners';
    public $route_parameter = 'banner';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $brands = Brand::latest()
            ->paginate(12);
        $languages = Lang::all();

        return view('app.brands.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'brands' => $brands,
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

        return view('app.brands.create', [
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



        if (isset($data['dropzone_images'])) {
            $data['img'] = $data['dropzone_images'];
        }

        Brand::create($data);

        return redirect()->route('banners.index')->with([
            'success' => true,
            'message' => 'Successfully saved'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function show(Brand $brand)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $langs = Lang::all();
        $brand = Brand::find($id);


        return view('app.brands.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'brand' => $brand
        ]);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();


        $brand = Brand::find($id);

        if (isset($data['dropzone_images'])) {
            $data['img'] = $data['dropzone_images'];
        } else {
            $data['img'] = null;
        }


        $brand->update($data);

        return redirect()->route('banners.index')->with([
            'success' => true,
            'message' => 'Successfully saved'
        ]);
    }




    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        // Sertifikatni id orqali topish
        $brand = Brand::find($id);

        // Agar sertifikat mavjud bo'lmasa, xatolik xabarini ko'rsatish
        if (!$brand) {
            return back()->with([
                'success' => false,
                'message' => 'what is found'
            ]);
        }

        // Sertifikatni o'chirish
        $brand->delete();

        // Qayta yo'naltirish va muvaffaqiyat xabarini ko'rsatish
        return back()->with([
            'success' => true,
            'message' => 'Successfully deleted'
        ]);
    }
}
