<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Lang;
use App\Models\ProductsCategory;
use Illuminate\Http\Request;

class ProductsCategoryController extends Controller
{
    public $title = 'Mahsulot turkumlari';
    public $route_name = 'products_categories';
    public $route_parameter = 'products_category';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = ProductsCategory::latest()
            ->paginate(12);
        $languages = Lang::all();

        return view('app.products_categories.index', [
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
        $all_categories = ProductsCategory::all();

        return view('app.products_categories.create', [
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

        ProductsCategory::create($data);

        return redirect()->route('products_categories.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductsCategory  $productsCategory
     * @return \Illuminate\Http\Response
     */
    public function show(ProductsCategory $productsCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductsCategory  $productsCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductsCategory $productsCategory)
    {
        $langs = Lang::all();
        $all_categories = ProductsCategory::all()->except($productsCategory->id);

        return view('app.products_categories.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'all_categories' => $all_categories,
            'productsCategory' => $productsCategory
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductsCategory  $productsCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductsCategory $productsCategory)
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

        $productsCategory->update($data);

        return redirect()->route('products_categories.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductsCategory  $productsCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductsCategory $productsCategory)
    {
        if(isset($productsCategory->children[0])) {
            return back()->with([
                'success' => false,
                'message' => 'Ichki turkumlari bor. Avval ularni oʻchirish kerak.'
            ]);
        }
        $productsCategory->delete();

        return back()->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli oʻchirildi'
        ]);
    }
}
