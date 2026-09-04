<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostsCategory;
use App\Models\Lang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostsCategoryController extends Controller
{
    public $title = 'Yangilik turkumlari';
    public $route_name = 'posts_categories';
    public $route_parameter = 'posts_category';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = PostsCategory::latest()
            ->paginate(12);
        $all_categories = PostsCategory::all();

        return view('app.posts_categories.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'categories' => $categories,
            'all_categories' => $all_categories
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
        $all_categories = PostsCategory::all();

        return view('app.posts_categories.create', [
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

        $data['slug'] = Str::slug($data['title'][$this->main_lang->code], '-');
        if(PostsCategory::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $data['slug'].'-'.time();
        }

        if(isset($data['dropzone_images'])) {
            $data['img'] = $data['dropzone_images'];
        }

        PostsCategory::create($data);

        return redirect()->route('posts_categories.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PostsCategory  $postsCategory
     * @return \Illuminate\Http\Response
     */
    public function show(PostsCategory $postsCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PostsCategory  $postsCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(PostsCategory $postsCategory)
    {
        $langs = Lang::all();
        $all_categories = PostsCategory::all()->except($postsCategory->id);

        return view('app.posts_categories.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'all_categories' => $all_categories,
            'postsCategory' => $postsCategory
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PostsCategory  $postsCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PostsCategory $postsCategory)
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

        $postsCategory->update($data);

        return redirect()->route('posts_categories.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PostsCategory  $postsCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(PostsCategory $postsCategory)
    {
        if(isset($postsCategory->children[0])) {
            return back()->with([
                'success' => false,
                'message' => 'Ichki turkumlari bor. Avval ularni oʻchirish kerak.'
            ]);
        }
        $postsCategory->delete();

        return back()->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli oʻchirildi'
        ]);
    }
}
