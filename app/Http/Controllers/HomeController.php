<?php

namespace App\Http\Controllers;

use App\Models\Appeal;
use App\Models\Config;
use App\Models\ConfigGroup;
use App\Models\DinamikMenu;
use App\Models\Employ;
use App\Models\Post;
use App\Models\Work;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    /**
     * Boshqaruv paneli.
     *
     * Avval bu sahifa butunlay bo'sh edi. Endi eng ko'p ishlatiladigan
     * amallar va sayt to'ldirilganligi haqidagi qisqa hisob ko'rsatiladi —
     * foydalanuvchi yon menyudan qidirmasdan ishni boshlashi uchun.
     */
    public function index()
    {
        return view('home', [
            'stats' => [
                [
                    'label' => 'Yangiliklar',
                    'count' => Post::count(),
                    'route' => 'posts.index',
                    'icon'  => 'fe-file-text',
                ],
                [
                    'label' => 'Xodimlar',
                    'count' => Employ::count(),
                    'route' => 'employs.index',
                    'icon'  => 'fe-users',
                ],
                [
                    'label' => 'Fotoalbomlar',
                    'count' => Work::count(),
                    'route' => 'works.index',
                    'icon'  => 'fe-image',
                ],
                [
                    'label' => 'Sahifalar',
                    'count' => DinamikMenu::count(),
                    'route' => 'dynamic-menus.index',
                    'icon'  => 'fe-layers',
                ],
            ],
            'new_appeals' => Appeal::whereNull('answer')->count(),
        ]);
    }





    public function upload_from_dropzone(Request $request)
    {
        try {
            $img = $request->file('file');

            if (!$img || !$img->isValid()) {
                return response()->json(['error' => 'Noto‘g‘ri fayl'], 400);
            }

            // ✅ **Tasodifiy nom va webp formatda saqlash**
            $img_name = Str::random(12) . '.webp';
            $original_path = public_path('upload/images');
            $small_path = public_path('upload/images/200');
            $large_path = public_path('upload/images/600');

            // ✅ **Papkalarni yaratish (agar bo‘lmasa)**
            File::ensureDirectoryExists($original_path, 0755, true);
            File::ensureDirectoryExists($small_path, 0755, true);
            File::ensureDirectoryExists($large_path, 0755, true);

            // ✅ **Rasmni ochish**
            $image = Image::make($img->getRealPath());

            // Telefonda olingan rasm EXIF orqali burilgan bo'lishi mumkin.
            // orientate() shu ma'lumotni o'qiydi, lekin u PHP ning `exif`
            // kengaytmasini talab qiladi — kengaytma yo'q serverda butun
            // yuklash "Reading Exif data is not supported" xatosi bilan
            // to'xtardi. Endi kengaytma bo'lmasa, rasm shundayligicha
            // saqlanadi.
            if (extension_loaded('exif')) {
                $image->orientate();
            }

            // ✅ **Asl rasmni webp formatda saqlash**
            $image->encode('webp', 100)->save($original_path . '/' . $img_name);

            // ✅ **200px versiya**
            $image->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            })->encode('webp', 100)->save($small_path . '/' . $img_name);

            // ✅ **600px versiya**
            $image->resize(600, null, function ($constraint) {
                $constraint->aspectRatio();
            })->encode('webp', 100)->save($large_path . '/' . $img_name);

            return response()->json([
                'file_name' => $img_name,
                'original_url' => url('upload/images/' . $img_name),
                'small_url' => url('upload/images/200/' . $img_name),
                'large_url' => url('upload/images/600/' . $img_name),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Xatolik: ' . $e->getMessage()], 500);
        }
    }



    // upload image for CKEditor
//    public function uploadImage(Request $request)
////    {
////        if ($request->hasFile('upload')) {
////            $fileName = time() . '.' . $request->file('upload')->getClientOriginalExtension();
////
////            $request->file('upload')->move(public_path('upload'), $fileName);
////
////            $CKEditorFuncNum = $request->input('CKEditorFuncNum');
////            $url = asset('upload/' . $fileName);
////            $msg = 'Image upload successfully!';
////            $response = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>";
////
////            @header('Content-type: text/html; charset=utf-8');
////            echo $response;
////        }
////    }
///

    /**
     * Matn muharririga qo'yilgan rasmni saqlaydi.
     *
     * CKEditor 5 (CKFinder protokoli) faylni "upload" nomi bilan yuboradi va
     * javobda {uploaded: 1, url: "..."} kutadi. Word'dan nusxa ko'chirilgan
     * yoki bufer orqali qo'yilgan rasmlar ham shu yerga tushadi.
     */
    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'upload' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'uploaded' => 0,
                'error' => ['message' => $validator->errors()->first('upload')],
            ]);
        }

        $file = $request->file('upload');

        // Asl nom ishlatilsa fayllar bir-birini o'chirib yuboradi.
        $fileName = Str::random(16) . '.' . $file->extension();
        $directory = public_path('upload/content');

        File::ensureDirectoryExists($directory, 0755, true);
        $file->move($directory, $fileName);

        return response()->json([
            'uploaded' => 1,
            'fileName' => $fileName,
            'url' => url('/upload/content/' . $fileName),
        ]);
    }

    public function config()
    {
        $config = Config::all();
        $config_groups = ConfigGroup::all();

        return view('app.config.index', compact(
            'config',
            'config_groups'
        ));
    }

    public function config_update(Request $request)
    {
        $data = $request->all();

        foreach (ConfigGroup::all() as $item) {
            $item->update([
                'is_active' => 0
            ]);
        }
        if (isset($data['config_groups'])) {
            foreach ($data['config_groups'] as $key => $item) {
                $config = ConfigGroup::find($key);
                $config->update([
                    'is_active' => 1
                ]);
            }
        }

        foreach (Config::all() as $item) {
            $item->update([
                'is_active' => 0
            ]);
        }
        foreach ($data['config'] as $key => $item) {
            $config = Config::find($key);
            $config->update([
                'is_active' => 1
            ]);
        }

        return back()->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }
}