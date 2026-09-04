<?php

namespace App\Http\Controllers;

use App\Models\AdvantageCategory;
use App\Models\Application;
use App\Models\Member;
use App\Models\Partner;
use App\Models\Product;
use App\Models\ProductsCategory;
use App\Models\Question;
use App\Models\Service;
use App\Models\Development;
use Illuminate\Http\Request;

class WebController extends Controller
{
    public function index()
    {
        $in_main_categories = ProductsCategory::where('in_main', 1)
            ->take(2)
            ->get();
        $developments = ProductsCategory::where('title', 'like', '%development%')
            ->first();
        $services = Service::all();
        $partners = Partner::latest()
            ->get();

        return view('index', compact(
            'in_main_categories',
            'developments',
            'services',
            'partners'
        ));
    }

    public function services()
    {
        $services = Service::all();

        return view('services', compact(
            'services'
        ));
    }

    public function subcategories($id)
    {
        $category = ProductsCategory::find($id);
        $categories = $category->children;

        if ($category->view == 1) {
            return view('subcategories', compact(
                'category',
                'categories'
            ));
        } else if($category->view == 2) {
            return view('isothermic-products', compact(
                'category',
                'categories'
            ));
        }
    }

    public function products($id)
    {
        $category = ProductsCategory::find($id);
        $products = $category->products;

        return view('products', compact(
            'category',
            'products'
        ));
    }

    public function product($id)
    {
        $product = Product::find($id);
        $product->increment('views_count');
        $other_products = Product::latest()
            ->where('id', '!=', $id)
            ->take(2)
            ->get();

        return view('product', compact(
            'product',
            'other_products'
        ));
    }

    public function about()
    {
        $questions = Question::latest()
            ->get();
        $members = Member::all();
        $partners = Partner::latest()
            ->get();

        return view('about', compact(
            'questions',
            'members',
            'partners'
        ));
    }

    public function advantages()
    {
        $categories = AdvantageCategory::latest()
            ->get();

        return view('advantages', compact(
            'categories'
        ));
    }

    public function contacts()
    {
        return view('contacts');
    }

    public function iso($id)
    {
        $category = ProductsCategory::find($id);
        $categories = $category->children;

        return view('isothermic-products', compact(
            'categories',
            'category'
        ));
    }

    public function developments()
    {
        $developments = Development::latest()
            ->get();

        return view('developments', compact(
            'developments'
        ));
    }

    public function application(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'page' => 'required|integer'
        ]);

        Application::create($request->all());

        return back()->with([
            'success' => true,
            'message' => 'Arizangiz qabul qilindi!'
        ]);
    }
}
