<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\ProductsCategory;
use App\Models\ProductImage;
use App\Models\Brand;
use App\Models\Lang;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public $title = 'Mahsulotlar';
    public $route_name = 'products';
    public $route_parameter = 'product';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::latest()
            ->paginate(12);
        $languages = Lang::all();

        return view('app.products.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'products' => $products,
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
        $brands = Brand::all();

        return view('app.products.create', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'all_categories' => $all_categories,
            'brands' => $brands
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
        $data['slug'] = Str::slug($data['title'][$this->main_lang->code], '-');
        if(Product::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $data['slug'].'-'.time();
        }

        DB::beginTransaction();
        try {
            $product = Product::create($data);

            if (isset($data['dropzone_images'])) {
                foreach ($data['dropzone_images'] as $img) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'img' => $img
                    ]);
                }
            }

            if (isset($data['categories'])) {
                $product->productsCategories()->sync($data['categories']);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return back()->withInput()->with([
                'success' => false,
                'message' => 'Saqlashda xatolik yuz berdi'
            ]);
        }

        return redirect()->route('products.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $langs = Lang::all();
        $all_categories = ProductsCategory::all();
        $brands = Brand::all();

        return view('app.products.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'all_categories' => $all_categories,
            'product' => $product,
            'brands' => $brands
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
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

        DB::beginTransaction();
        try {
            $product->update($data);

            $product->productImages()->delete();
            if (isset($data['dropzone_images'])) {
                foreach ($data['dropzone_images'] as $img) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'img' => $img
                    ]);
                }
            }

            if (isset($data['categories'])) {
                $product->productsCategories()->sync($data['categories']);
            } else {
                $product->productsCategories()->detach();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return back()->withInput()->with([
                'success' => false,
                'message' => 'Saqlashda xatolik yuz berdi'
            ]);
        }

        return redirect()->route('products.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            $product->productsCategories()->detach();
            $product->productImages()->delete();
            $product->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with([
                'success' => false,
                'message' => 'Saqlashda xatolik yuz berdi'
            ]);
        }

        return redirect()->route('products.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli oʻchirildi'
        ]);
    }
}
