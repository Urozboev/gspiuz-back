<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DinamikMenu;
use App\Models\FormMenu;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * Dinamik sahifalar — menyudagi har bir band uchun kontent.
 *
 * Tuzilma: menus (manzil va sarlavha) → dinamik_menus (sahifa tanasi)
 *          → form_menus (bloklar / kartochkalar).
 *
 * Fayllar `dinamik_menus.file` da til kaliti bilan saqlanadi (file_menus
 * jadvali admin panelda hech qachon to'ldirilmaydi).
 *
 * Sahifaning ko'rinishi `layout` bilan belgilanadi:
 *   single — sarlavha + HTML matn + biriktirilgan fayllar
 *   cards  — kartochkalar to'plami; har biri o'z manzilida ochiladi
 *   files  — faqat yuklab olinadigan fayllar ro'yxati
 *
 * Matnlar Accept-Language sarlavhasiga qarab bitta tilda qaytadi —
 * bu API ning qolgan qismidagi bilan bir xil tartib.
 */
class PageController extends Controller
{
    /** Sahifalar ro'yxati — menyu va sitemap uchun. */
    public function index()
    {
        $locale = App::getLocale();

        $pages = DinamikMenu::with('menu')
            ->where('active', 1)
            ->get()
            ->filter(fn ($page) => $page->menu && $page->menu->slug)
            ->sortBy(fn ($page) => $page->menu->order ?? 0)
            ->map(fn ($page) => [
                'slug'     => $page->menu->slug,
                'path'     => $page->menu->path ?? '/' . $page->menu->slug,
                'layout'   => $page->layout,
                'title'    => $this->line($page->title, $locale, $page->menu->title),
                'subtitle' => $this->line($page->short_title, $locale),
            ])
            ->values();

        return response()->json(['data' => $pages]);
    }

    /** Bitta sahifa. */
    public function show(string $slug)
    {
        $locale = App::getLocale();
        $page = $this->findPage($slug);

        if (!$page) {
            return response()->json(['message' => 'Sahifa topilmadi'], 404);
        }

        $data = [
            'slug'             => $slug,
            'layout'           => $page->layout,
            'title'            => $this->line($page->title, $locale, $page->menu->title),
            'subtitle'         => $this->line($page->short_title, $locale),
            'body'             => $this->line($page->text, $locale),
            'background'       => $page->background ? url('/upload/images/' . $page->background) : null,
            // Matndan tashqari media: video matndan keyin, galereya undan keyin.
            'video'            => $page->video,
            'images'           => $this->imageSets($page->images),
            'meta_title'       => $this->line($page->meta_title, $locale),
            'meta_description' => $this->line($page->meta_description, $locale),
            'blocks'           => [],
            'files'            => [],
        ];

        if ($page->layout === 'cards') {
            $data['blocks'] = $this->blocks($page, $locale);
        } else {
            // single va files turlarida bloklar ikonli bo'limlar sifatida keladi.
            $data['blocks'] = $page->layout === 'single'
                ? $this->blocks($page, $locale)
                : [];
        }

        $data['files'] = $this->pageFiles($page, $locale);

        return response()->json(['data' => $data]);
    }

    /** layout=cards dagi bitta kartochkaning to'liq sahifasi. */
    public function item(string $slug, string $item)
    {
        $locale = App::getLocale();
        $page = $this->findPage($slug);

        if (!$page) {
            return response()->json(['message' => 'Sahifa topilmadi'], 404);
        }

        $block = FormMenu::where('dinamik_menu_id', $page->id)
            ->where('slug', $item)
            ->where('active', 1)
            ->first();

        if (!$block) {
            return response()->json(['message' => 'Yozuv topilmadi'], 404);
        }

        return response()->json([
            'data' => [
                'slug'     => $block->slug,
                'page'     => $slug,
                'title'    => $this->line($block->title, $locale),
                'subtitle' => $this->line($block->subtitle, $locale),
                'desc'     => $this->line($block->text, $locale),
                'body'     => $this->line($block->body, $locale),
                'image'    => $this->blockImage($block),
                'images'   => $this->blockImages($block),
                'video'    => $block->video,
                'date'     => optional($block->date)->toDateString(),
                'files'    => $this->pageFiles($page, $locale),
            ],
        ]);
    }

    /** Menyu slug'i bo'yicha faol sahifani topadi. */
    private function findPage(string $slug): ?DinamikMenu
    {
        // Bitta slug bir nechta menyu bandiga tegishli bo'lishi mumkin
        // (/admissions?tab=... ), sahifa esa ulardan bittasiga biriktirilgan.
        //
        // Manzil bo'yicha ham qidiramiz: admin panelda slug sarlavhadan,
        // manzil esa qo'lda yozilgan bo'lsa, ular mos kelmasligi mumkin —
        // sayt esa manzilni ishlatadi.
        $menuIds = Menu::where('slug', $slug)
            ->orWhere('path', '/' . $slug)
            ->orWhere('path', $slug)
            ->pluck('id');

        if ($menuIds->isEmpty()) {
            return null;
        }

        return DinamikMenu::with('menu')
            ->whereIn('menu_id', $menuIds)
            ->where('active', 1)
            ->orderBy('id')
            ->first();
    }

    /** Sahifa bloklari / kartochkalari. */
    private function blocks(DinamikMenu $page, string $locale): array
    {
        return FormMenu::where('dinamik_menu_id', $page->id)
            ->where('active', 1)
            ->orderBy('order')
            ->get()
            ->map(fn ($block) => [
                'slug'  => $block->slug,
                'group' => $block->group,
                'icon'  => $block->icon,
                'title' => $this->line($block->title, $locale),
                'desc'  => $this->line($block->text, $locale),
                'link'  => $block->link,
                'image' => $this->blockImage($block),
                'date'  => optional($block->date)->toDateString(),
                'order' => $block->order,
            ])
            ->values()
            ->all();
    }

    /**
     * Sahifaga biriktirilgan fayllar.
     *
     * Har bir fayl — `form_menus` dagi bitta yozuv: o'z nomi, izohi, sanasi
     * va muqova rasmi bilan. Sayt ularni sana bo'yicha yillarga guruhlaydi,
     * shuning uchun eng yangisi birinchi qaytariladi.
     *
     * Eski yo'l ham saqlangan: `dinamik_menus.file` maydonidagi til kalitli
     * fayl ({"uz": "uploads/…/reja.pdf"}) ro'yxat oxiriga qo'shiladi.
     */
    private function pageFiles(DinamikMenu $page, string $locale): array
    {
        $files = FormMenu::where('dinamik_menu_id', $page->id)
            ->where('active', 1)
            ->whereNotNull('file')
            ->where('file', '!=', '')
            ->orderByDesc('date')
            ->orderBy('order')
            ->get()
            ->map(function ($item) use ($locale) {
                $line = $this->fileLine('upload/files/' . $item->file, $item->date ?? $item->updated_at);

                return array_merge($line, [
                    'id'    => $item->id,
                    'title' => $this->line($item->title, $locale) ?? $line['title'],
                    'desc'  => $this->line($item->text, $locale),
                    'image' => $this->blockImageSet($item),
                    'date'  => optional($item->date)->toDateString(),
                ]);
            })
            ->values()
            ->all();

        // Eski maydondagi fayl (agar bo'lsa).
        $legacy = $page->file;

        if (is_array($legacy) && $legacy !== []) {
            $path = $legacy[$locale] ?? $legacy[$this->main_lang->code ?? 'uz'] ?? null;

            if (is_string($path) && $path !== '') {
                $files[] = array_merge(
                    $this->fileLine($path, $page->updated_at),
                    ['id' => null, 'desc' => null, 'image' => null]
                );
            }
        }

        return $files;
    }

    /**
     * Blokning asosiy rasmi.
     *
     * Yangi `image` ustuni birinchi navbatda; bo'lmasa admin panelning eski
     * dropzone maydoni (`photo`), u ham bo'lmasa biriktirilgan rasmlardan
     * birinchisi olinadi.
     */
    private function blockImage(FormMenu $block): ?string
    {
        $name = $block->image;

        if (!$name && is_array($block->photo)) {
            $name = $block->photo[0] ?? null;
        }

        if (!$name) {
            $name = optional($block->formImages->first())->img;
        }

        return $name ? url('/upload/images/' . $name) : null;
    }

    /** Fayl nomlari ro'yxatini uch o'lchamli rasm to'plamlariga aylantiradi. */
    private function imageSets($names): array
    {
        if (!is_array($names)) {
            return [];
        }

        return collect($names)
            ->filter()
            ->map(fn ($name) => [
                'lg' => url('/upload/images/' . $name),
                'md' => url('/upload/images/600/' . $name),
                'sm' => url('/upload/images/200/' . $name),
            ])
            ->values()
            ->all();
    }

    /** Blok rasmining uch o'lchami — frontenddagi ImageSet shakli. */
    private function blockImageSet(FormMenu $block): ?array
    {
        $name = $block->image;

        if (!$name && is_array($block->photo)) {
            $name = $block->photo[0] ?? null;
        }

        if (!$name) {
            return null;
        }

        return [
            'lg' => url('/upload/images/' . $name),
            'md' => url('/upload/images/600/' . $name),
            'sm' => url('/upload/images/200/' . $name),
        ];
    }

    /**
     * Kartochkaga biriktirilgan qo'shimcha rasmlar (galereya).
     *
     * Ikki manbadan yig'iladi: `photo` maydonidagi dropzone rasmlari va
     * eski `form_images` jadvali. Muqova rasmi ro'yxatga qo'shilmaydi —
     * u alohida `image` sifatida qaytadi.
     */
    private function blockImages(FormMenu $block): array
    {
        $names = collect(is_array($block->photo) ? $block->photo : [])
            ->merge($block->formImages->pluck('img'))
            ->filter()
            ->reject(fn ($name) => $name === $block->image)
            ->unique()
            ->values();

        return $names
            ->map(fn ($name) => [
                'lg' => url('/upload/images/' . $name),
                'md' => url('/upload/images/600/' . $name),
                'sm' => url('/upload/images/200/' . $name),
            ])
            ->all();
    }

    /** Fayl haqidagi ma'lumot: nomi, manzili, hajmi va turi. */
    private function fileLine(string $path, $date = null): array
    {
        $absolute = public_path('storage/' . ltrim($path, '/'));

        if (!is_file($absolute)) {
            // upload/ ostidagi fayllar public ildizida turadi.
            $absolute = public_path(ltrim($path, '/'));
            $url = url('/' . ltrim($path, '/'));
        } else {
            $url = url('/storage/' . ltrim($path, '/'));
        }

        $name = basename($path);

        return [
            'title' => preg_replace('/^\d+_/', '', $name),
            'url'   => $url,
            'size'  => is_file($absolute) ? filesize($absolute) : null,
            'mime'  => is_file($absolute) ? (mime_content_type($absolute) ?: null) : null,
            'date'  => optional($date)->toDateString(),
        ];
    }

    /**
     * Ko'p tilli maydondan joriy til qatorini oladi.
     * Til bo'sh bo'lsa asosiy tilga, u ham bo'lmasa zaxira qiymatga tushadi.
     */
    private function line($value, string $locale, $fallback = null): ?string
    {
        foreach ([$value, $fallback] as $candidate) {
            if (is_array($candidate)) {
                $line = $candidate[$locale] ?? null;

                if ($line === null || $line === '') {
                    $line = $candidate[$this->main_lang->code ?? 'uz'] ?? null;
                }

                if ($line !== null && $line !== '') {
                    return $line;
                }

                continue;
            }

            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }
}
