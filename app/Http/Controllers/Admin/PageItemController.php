<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DinamikMenu;
use App\Models\FormMenu;
use App\Models\Lang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Sahifa yozuvlari — kartochkalar va fayllar.
 *
 * Sahifaning ko'rinishiga qarab bir xil jadval (`form_menus`) uch xil
 * ma'noga ega bo'ladi:
 *
 *   cards  — kartochka: bosilganda alohida sahifa ochiladi
 *   files  — yuklab olinadigan fayl: nomi, izohi, sanasi, muqovasi
 *   single — matn ichidagi ikonli bo'lim
 *
 * Forma shu ko'rinishga moslashadi, ya'ni admin faqat kerakli maydonlarni
 * ko'radi. Dinamik menyu formasi katta va murakkab bo'lgani uchun bu
 * ekran alohida qilingan.
 */
class PageItemController extends Controller
{
    public $route_name = 'page-items';

    public $route_parameter = 'item';

    /** Fayl uchun ruxsat etilgan turlar. */
    private const FILE_TYPES = 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt,rtf,zip,rar,jpg,jpeg,png,webp';

    /** Fayl hajmi chegarasi, kilobaytda (20 MB). */
    private const FILE_MAX_KB = 20480;

    public function index(DinamikMenu $page)
    {
        $items = FormMenu::where('dinamik_menu_id', $page->id)
            ->orderBy('order')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.page-items.index', $this->shared($page, [
            'items' => $items,
        ]));
    }

    public function create(DinamikMenu $page)
    {
        return view('admin.page-items.create', $this->shared($page));
    }

    public function store(Request $request, DinamikMenu $page)
    {
        $data = $this->validated($request, $page);

        if ($data === null) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan',
            ]);
        }

        $data['dinamik_menu_id'] = $page->id;
        $data['slug'] = $this->uniqueSlug($page, $data['title'][$this->main_lang->code] ?? 'yozuv');

        if ($file = $this->storeFile($request)) {
            $data['file'] = $file;
        }

        FormMenu::create($data);

        return redirect()->route('page-items.index', $page->id)->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi',
        ]);
    }

    public function edit(DinamikMenu $page, FormMenu $item)
    {
        abort_unless($item->dinamik_menu_id === $page->id, 404);

        return view('admin.page-items.edit', $this->shared($page, ['item' => $item]));
    }

    public function update(Request $request, DinamikMenu $page, FormMenu $item)
    {
        abort_unless($item->dinamik_menu_id === $page->id, 404);

        $data = $this->validated($request, $page);

        if ($data === null) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan',
            ]);
        }

        // Yangi fayl yuklanmasa, eskisi qoladi.
        if ($file = $this->storeFile($request)) {
            $data['file'] = $file;
        }

        $item->update($data);

        return redirect()->route('page-items.index', $page->id)->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli yangilandi',
        ]);
    }

    public function destroy(DinamikMenu $page, FormMenu $item)
    {
        abort_unless($item->dinamik_menu_id === $page->id, 404);

        $item->delete();

        return redirect()->route('page-items.index', $page->id)->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli oʻchirildi',
        ]);
    }

    /** Har bir ko'rinish uchun umumiy view ma'lumotlari. */
    private function shared(DinamikMenu $page, array $extra = []): array
    {
        $page->loadMissing('menu');

        return array_merge([
            'title'           => $this->titleFor($page),
            'route_name'      => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'page'            => $page,
            'langs'           => Lang::all(),
            'fileTypes'       => str_replace(',', ', ', self::FILE_TYPES),
            'fileMaxMb'       => (int) (self::FILE_MAX_KB / 1024),
        ], $extra);
    }

    private function titleFor(DinamikMenu $page): string
    {
        $name = data_get($page->title, $this->main_lang->code)
            ?? data_get($page->menu?->title, $this->main_lang->code)
            ?? 'Sahifa';

        return match ($page->layout) {
            'files' => $name . ' — fayllar',
            'cards' => $name . ' — kartochkalar',
            default => $name . ' — boʻlimlar',
        };
    }

    /** Sahifa ichida takrorlanmaydigan manzil. */
    private function uniqueSlug(DinamikMenu $page, string $title): string
    {
        $base = Str::slug($title) ?: 'yozuv';
        $slug = $base;
        $counter = 1;

        while (FormMenu::where('dinamik_menu_id', $page->id)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$counter);
        }

        return $slug;
    }

    /** Yuklangan faylni saqlaydi va nomini qaytaradi. */
    private function storeFile(Request $request): ?string
    {
        if (!$request->hasFile('document')) {
            return null;
        }

        $file = $request->file('document');

        // Asl nom ishlatilsa fayllar bir-birini o'chirib yuboradi.
        $name = Str::random(16) . '.' . strtolower($file->getClientOriginalExtension());
        $directory = public_path('upload/files');

        File::ensureDirectoryExists($directory, 0755, true);
        $file->move($directory, $name);

        return $name;
    }

    private function validated(Request $request, DinamikMenu $page): ?array
    {
        $rules = [
            'title.' . $this->main_lang->code => 'required|string',
            'order'                           => 'nullable|integer|min:0',
            'link'                            => 'nullable|string|max:255',
            'video'                           => 'nullable|string|max:255',
            'date'                            => 'nullable|date',
            'document'                        => 'nullable|file|mimes:' . self::FILE_TYPES
                . '|max:' . self::FILE_MAX_KB,
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return null;
        }

        $data = $request->all();

        // Forma rasm nomlarini vergul bilan bitta maydonda yuboradi.
        $raw = $data['dropzone_images'] ?? null;
        $images = is_array($raw) ? $raw : explode(',', (string) $raw);
        $images = array_values(array_filter(array_map('trim', $images)));

        return [
            'title'    => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'text'     => $data['desc'] ?? null,
            'body'     => $data['body'] ?? null,
            'icon'     => ($data['icon'] ?? null) ?: null,
            'link'     => ($data['link'] ?? null) ?: null,
            'video'    => ($data['video'] ?? null) ?: null,
            'group'    => ($data['group'] ?? null) ?: null,
            // Birinchi rasm — muqova, qolganlari galereya.
            'image'    => $images[0] ?? null,
            'photo'    => array_slice($images, 1),
            'date'     => ($data['date'] ?? null) ?: null,
            'order'    => (int) ($data['order'] ?? 0),
            'active'   => $request->boolean('active'),
            'type'     => $page->layout,
        ];
    }
}
