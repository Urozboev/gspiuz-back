<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostImage;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * Galereya — fotoalbomlar (works) va yangiliklar rasmlari (post_images).
 */
class GalleryController extends Controller
{
    /** Fotoalbomlar ro'yxati. */
    public function albums(Request $request)
    {
        $locale = App::getLocale();

        $perPage = (int) $request->query('per_page', 12);
        $perPage = $perPage > 0 && $perPage <= 60 ? $perPage : 12;

        $albums = Work::with('workImages')->latest()->paginate($perPage);

        $data = collect($albums->items())->map(function ($album) use ($locale) {
            return [
                'id'           => $album->id,
                'title'        => $album->title[$locale] ?? null,
                'desc'         => $album->desc[$locale] ?? null,
                'youtube_link' => $album->youtube_link,
                'video'        => $album->video,
                'images_count' => $album->workImages->count(),
                'cover'        => [
                    'lg' => $album->lg_main_img,
                    'md' => $album->md_main_img,
                    'sm' => $album->sm_main_img,
                ],
                'date'         => optional($album->created_at)->toDateString(),
            ];
        });

        return $this->paginated($data, $albums);
    }

    /** Bitta albom va uning barcha rasmlari. */
    public function album($id)
    {
        $locale = App::getLocale();

        $album = Work::with('workImages')->find($id);

        if (!$album) {
            return response()->json(['message' => 'Albom topilmadi'], 404);
        }

        return response()->json([
            'data' => [
                'id'           => $album->id,
                'title'        => $album->title[$locale] ?? null,
                'desc'         => $album->desc[$locale] ?? null,
                'youtube_link' => $album->youtube_link,
                'video'        => $album->video,
                'cover'        => [
                    'lg' => $album->lg_main_img,
                    'md' => $album->md_main_img,
                    'sm' => $album->sm_main_img,
                ],
                'images' => $album->workImages->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'lg' => $image->lg_img,
                        'md' => $image->md_img,
                        'sm' => $image->sm_img,
                    ];
                })->values(),
                'date' => optional($album->created_at)->toDateString(),
            ],
        ]);
    }

    /**
     * Yangiliklarga biriktirilgan rasmlarning yagona lentasi.
     * Albomlar hali to'ldirilmagan bo'lsa ham galereya sahifasi bo'sh qolmaydi.
     */
    public function photos(Request $request)
    {
        $locale = App::getLocale();

        $perPage = (int) $request->query('per_page', 24);
        $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 24;

        $images = PostImage::with('post')->latest()->paginate($perPage);

        $data = collect($images->items())->map(function ($image) use ($locale) {
            return [
                'id'         => $image->id,
                'lg'         => $image->lg_img,
                'md'         => $image->md_img,
                'sm'         => $image->sm_img,
                'post_slug'  => optional($image->post)->slug,
                'post_title' => $image->post ? ($image->post->title[$locale] ?? null) : null,
                'date'       => optional($image->post)->date ?? optional($image->created_at)->toDateString(),
            ];
        });

        return $this->paginated($data, $images);
    }

    /** Boshqa ro'yxat endpointlari bilan bir xil sahifalash shakli. */
    private function paginated($data, $paginator)
    {
        return response()->json([
            'data'          => $data,
            'total'         => $paginator->total(),
            'per_page'      => $paginator->perPage(),
            'current_page'  => $paginator->currentPage(),
            'last_page'     => $paginator->lastPage(),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
        ]);
    }
}
