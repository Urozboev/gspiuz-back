<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Lang;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\PostsCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class PostController extends Controller
{
    public $title = 'Yangiliklar';
    public $route_name = 'posts';
    public $route_parameter = 'post';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::latest()
            ->paginate(12);
        $languages = Lang::all();

        return view('app.posts.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'posts' => $posts,
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
        $all_categories = PostsCategory::all();

        return view('app.posts.create', [
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
        $data['date'] = isset($data['date']) ? date('Y-m-d', strtotime($data['date'])) : date('Y-m-d');

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
        if(Post::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $data['slug'].'-'.time();
        }

        DB::beginTransaction();
        try {
            $post = Post::create($data);

            if (isset($data['dropzone_images'])) {
                foreach ($data['dropzone_images'] as $img) {
                    PostImage::create([
                        'post_id' => $post->id,
                        'img' => $img
                    ]);
                }
            }


            if (isset($data['categories'])) {
                $post->postsCategories()->sync($data['categories']);
            }

            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $icon = $request->file('file');
                $iconPath = $icon->store('tander/file', 'public');
                $data['file'] = $iconPath; // Fayl yuklansa, saqlanadi
            } else {
                $data['file'] = null; // Fayl kelmasa, null saqlanadi
            }



            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return back()->withInput()->with([
                'success' => false,
                'message' => 'Saqlashda xatolik yuz berdi'
            ]);
        }

        return redirect()->route('posts.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
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
    public function edit(Post $post)
    {
        $langs = Lang::all();
        $all_categories = PostsCategory::all();

        return view('app.posts.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'all_categories' => $all_categories,
            'post' => $post
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $data = $request->all();

        // Sanani formatlash (agar kelmasa hozirgi sanani oladi)
        $data['date'] = isset($data['date']) ? date('Y-m-d', strtotime($data['date'])) : date('Y-m-d');

        // Validatsiya
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
            // Postni yangilash
            $post->update($data);

            // Rasm fayllarini yangilash
            $post->postImages()->delete();
            if (!empty($data['dropzone_images'])) {
                foreach ($data['dropzone_images'] as $img) {
                    PostImage::create([
                        'post_id' => $post->id,
                        'img' => $img
                    ]);
                }
            }

            // Faylni yangilash
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                // Eski faylni o'chirish
                if ($post->file) {
                    Storage::disk('public')->delete($post->file);
                }

                // Yangi faylni saqlash
                $icon = $request->file('file');
                $iconPath = $icon->store('tander/file', 'public');
                $post->file = $iconPath;
            } else {
                // Fayl yuklanmagan bo'lsa, null saqlash
                if (!$post->file) {
                    $post->file = null;
                }
            }
            $post->save();

            // Kategoriyalarni yangilash
            if (!empty($data['categories'])) {
                $post->postsCategories()->sync($data['categories']);
            } else {
                $post->postsCategories()->detach();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with([
                'success' => false,
                'message' => 'Saqlashda xatolik yuz berdi: ' . $e->getMessage()
            ]);
        }

        return redirect()->route('posts.index')->with([
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
    public function destroy(Post $post)
    {
        DB::beginTransaction();
        try {
            $post->postsCategories()->detach();
            $post->postImages()->delete();
            $post->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with([
                'success' => false,
                'message' => 'Saqlashda xatolik yuz berdi'
            ]);
        }

        return redirect()->route('posts.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli oʻchirildi'
        ]);
    }
}
