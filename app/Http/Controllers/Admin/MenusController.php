<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lang;
use App\Models\Menu;
use App\Models\Post;
use App\Models\ProductsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MenusController extends Controller
{
    public $title = 'Menyular';
    public $route_name = 'menus';
    public $route_parameter = 'menu';
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $languages = Lang::all();
        $search = $request->input('search'); // Qidiruv uchun so'rovdan kelayotgan ma'lumot

        // Faqat asosiy menyularni paginate qilish
        $paginatedMenus = Menu::whereNull('parent_id')
            ->when($search, function ($query, $search) {
                return $query->where('title', 'LIKE', '%' . $search . '%');
            })
            ->orderBy('order')
            ->paginate(2);

        // Hierarxik daraxtni sahifalangan menyular asosida tuzish
        $menuTree = $this->buildMenuTree($paginatedMenus);

        return view('admin.menus.index', [
            'title' => 'Menus',
            'route_name' => 'menus',
            'route_parameter' => 'menu',
            'menus' => $menuTree, // Hierarxik menyular daraxti
            'languages' => $languages,
            'count' => $paginatedMenus, // Sahifalash obyektini blade-ga yuborish
            'search' => $search, // Qidiruvni blade-ga yuborish
        ]);
    }
    private function buildMenuTree($paginatedMenus)
    {
        // Sahifalangan asosiy menyular ID-lari
        $menuIds = $paginatedMenus->pluck('id')->toArray();

        // Ushbu asosiy menyularga tegishli bolalarni olish
        $childMenus = Menu::whereIn('parent_id', $menuIds)
            ->orderBy('order')
            ->get();

        // Hierarxik daraxtni tuzish
        $menuTree = [];
        foreach ($paginatedMenus as $menu) {
            $menuTree[] = [
                'menu' => $menu, // Asosiy menyu
                'children' => $childMenus->where('parent_id', $menu->id), // Faqat tegishli bolalar
            ];
        }

        return $menuTree;
    }





    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $langs = Lang::all();
        $menus = Menu::all();

        return view('admin.menus.create', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'all_categories' => $menus
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Menyu bandining slug'i.
     *
     * Sayt sahifani aynan shu qiymat bo'yicha topadi, shuning uchun u
     * admin kiritgan manzilga mos bo'lishi kerak: "/citizen_appeal"
     * uchun slug "citizen_appeal". Manzil berilmagan (dropdown
     * sarlavhasi) bo'lsa — sarlavhadan yasaladi.
     */
    private function slugFor(array $data): string
    {
        $path = trim((string) ($data['path'] ?? ''));

        if ($path !== '' && !preg_match('~^https?://~i', $path)) {
            // So'rov parametrlarisiz oxirgi bo'lak: /a/b?x=1 -> b
            $clean = strtok(ltrim($path, '/'), '?');
            $last = trim((string) $clean) === '' ? '' : basename($clean);

            if ($last !== '') {
                return $last;
            }
        }

        return Str::slug($data['title'][$this->main_lang->code] ?? '', '-') ?: 'sahifa';
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'title.'.$this->main_lang->code => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Validation error'
            ]);
        }
        // Slug — sahifa qaysi manzil bo'yicha topilishini belgilaydi.
        // Ilgari u sarlavhadan yasalardi: admin "/citizen_appeal" manzilini
        // yozsa ham, slug uzun sarlavhadan chiqardi va sayt o'sha sahifani
        // topa olmay 404 ko'rsatardi. Endi manzil berilgan bo'lsa, slug
        // o'shandan olinadi.
        $data['slug'] = $this->slugFor($data);

        if (Menu::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $data['slug'].'-'.time();
        }

        // Formada tanlanmagan bo'lsa — yuqori band yo'q.
        $data['parent_id'] = ($data['parent_id'] ?? null) ?: null;

        Menu::create($data);

        return redirect()->route('menus.index')->with([
            'success' => true,
            'message' => 'Saved successfully'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function updateOrder(Request $request)
    {
        $orderData = $request->order;

        foreach ($orderData as $menu) {
            Menu::where('id', $menu['id'])->update(['order' => $menu['order']]);
        }

        return response()->json(['message' => 'Order updated successfully!']);
    }

//    public function updateOrder(Request $request)
//    {
//        $orderData = $request->input('order');
//
//        foreach ($orderData as $item) {
//            Menu::where('id', $item['id'])->update(['order' => $item['order']]);
//        }
//
//        return response()->json(['message' => 'Order updated successfully!']);
//    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Menu $menu)
    {
        $langs = Lang::all();
        $menus = Menu::all();

        return view('admin.menus.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'menus' => $menus,
            'menu' => $menu
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'title.'.$this->main_lang->code => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Validation error'
            ]);
        }

        $menu = Menu::findOrFail($id);

        // Manzil yoki sarlavha o'zgargan bo'lsa, slug qayta hisoblanadi —
        // sayt sahifani aynan slug bo'yicha topadi.
        $titleChanged = ($menu->title[$this->main_lang->code] ?? null)
            !== ($data['title'][$this->main_lang->code] ?? null);
        $pathChanged = ($menu->path ?? null) !== ($data['path'] ?? null);

        if ($titleChanged || $pathChanged) {
            $data['slug'] = $this->slugFor($data);

            if (Menu::where('slug', $data['slug'])->where('id', '!=', $id)->exists()) {
                $data['slug'] = $data['slug'].'-'.time();
            }
        }

        // Band o'ziga yoki o'z avlodiga ota-ona bo'lib qolmasin.
        $data['parent_id'] = ($data['parent_id'] ?? null) ?: null;

        if ((int) $data['parent_id'] === (int) $id) {
            $data['parent_id'] = $menu->parent_id;
        }

        $menu->update($data);

        return redirect()->route('menus.index')->with([
            'success' => true,
            'message' => 'Updated successfully'
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return redirect()->route('menus.index')->with([
            'success' => true,
            'message' => 'Deleted successfully'
        ]);
    }
    public function detele_file()
    {

        $menus = Menu::onlyTrashed()->latest()
            ->paginate(12);
        $languages = Lang::all();

        return view('admin.menus.delete', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'menus' => $menus,
            'languages' => $languages
        ]);
    }
    public function restore($id)
    {
        $menu = Menu::withTrashed()->findOrFail($id);
        $menu->restore();

        return redirect()->route('menus.index')->with([
            'success' => true,
            'message' => 'Restored successfully'
        ]);
    }
    public function forceDestroy($id)
    {
        $menu = Menu::withTrashed()->findOrFail($id);
        $menu->forceDelete();

        return redirect()->route('menus.index')->with([
            'success' => true,
            'message' => 'Permanently deleted successfully'
        ]);
    }
}
