<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kampus;
use App\Models\PostsCategory;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\App;


class NewsController extends Controller
{

    public function get_posts(Request $request)
    {
        $locale = App::getLocale();

        // Video havolasi bor yangilik ham oddiy yangilik boʻlib qolaveradi:
        // u /news da ham, /news/video da ham koʻrinadi. Ilgari bu yerda
        // whereNull('video_link') turgani uchun havola qoʻshilgan zahoti
        // yangilik roʻyxatdan yoʻqolib qolardi.
        $query = Post::with('postImages', 'postsCategories');

        // ?category=slug yoki ?category=id — turkum bo'yicha filtr (e'lonlar va h.k.)
        if ($category = $request->query('category')) {
            $query->whereHas('postsCategories', function ($q) use ($category) {
                $q->where('posts_categories.slug', $category)
                  ->orWhere('posts_categories.id', $category);
            });
        }

        // ?search=matn — sarlavha bo'yicha qidiruv
        if ($search = $request->query('search')) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        $perPage = (int) $request->query('per_page', 10);
        $perPage = $perPage > 0 && $perPage <= 50 ? $perPage : 10;

        $posts = $query
            ->orderBy('date', 'desc') // Eng yangi yangilik birinchi bo'lib chiqadi
            ->paginate($perPage);


        $translatedPosts = collect($posts->items())->map(function ($post) use ($locale) {
            return [
                'id' => $post->id,
                'title' => $post->title[$locale] ?? null,
                'subtitle' => $post->subtitle[$locale] ?? null,
                'desc' => $post->desc[$locale] ?? null,
                'images' => $post->postImages->map(function ($image) {
                    return [
                        'lg' => $image->lg_img,
                        'md' => $image->md_img,
                        'sm' => $image->sm_img,
                    ];
                })->toArray(),
                'categories' => $post->postsCategories->map(function ($category) use ($locale) {
                    return [
                        'id' => $category->id,
                        'slug' => $category->slug,
                        'name' => $category->title[$locale] ?? null,
                        'title' => $category->title[$locale] ?? null,
                    ];
                })->toArray(),
                'date' => $post->date,
                'url' => $post->url,
                // Yangilikka biriktirilgan video (YouTube havolasi).
                'video_link' => $post->video_link,
                'views_count' => $post->views_count,
                'slug' => $post->slug,
                'file' => $post->file ? url($post->file) : null,
                'meta_keywords' => $post->meta_keywords,
            ];
        });

        return response()->json([
            'data' => $translatedPosts,
            'total' => $posts->total(),
            'per_page' => $posts->perPage(),
            'current_page' => $posts->currentPage(),
            'last_page' => $posts->lastPage(),
            'next_page_url' => $posts->nextPageUrl(),
            'prev_page_url' => $posts->previousPageUrl(),
        ]);
    }

    public function show_post($slug)
    {
        // Foydalanuvchi tilini olish
        $locale = App::getLocale();

        // Slug orqali postni olish
        $post = Post::with('postsCategories', 'postImages')->where('slug', $slug)->first();

        if (is_null($post)) {
            return response()->json(['message' => 'Post not found or URL is not null'], 404);
        }

        // Postni har safar ko'rilganda views_countni oshirish
        $post->increment('views_count');

        // Postni foydalanuvchi tiliga moslashtirish
        $translatedPost = [
            'id' => $post->id,
            'title' => $post->title[$locale] ?? null,
            'subtitle' => $post->subtitle[$locale] ?? null,

            'desc' => $post->desc[$locale] ?? null,
            'images' => $post->postImages->map(function ($image) {
                return [
                    'lg' => $image->lg_img, // Katta o'lchamdagi rasm URL
                    'md' => $image->md_img, // O'rta o'lchamdagi rasm URL
                    'sm' => $image->sm_img, // Kichik o'lchamdagi rasm URL
                ];
            })->toArray(),
            'categories' => $post->postsCategories->map(function ($category) use ($locale) {
                return [
                    'id' => $category->id,
                    'slug' => $category->slug,
                    'name' => $category->title[$locale] ?? null,
                    'title' => $category->title[$locale] ?? null, // Kategoriya nomini foydalanuvchi tilida chiqarish
                ];
            })->toArray(),
            'slug' => $post->slug,
            'url' => $post->url,
            'date' => $post->date,
            // Yangilikka biriktirilgan video (YouTube havolasi).
            'video_link' => $post->video_link,
            'file' => $post->file ? url( $post->file) : null,
            'views_count' => $post->views_count, // Yangilangan views_count
            'meta_keywords' => $post->meta_keywords,
        ];

        return response()->json($translatedPost);
    }

    public function get_kampus()
    {
        // Foydalanuvchi tilini olish
        $locale = App::getLocale();

        // Postlarni oxirgi qo'shilganidan boshlab olish va 10 tadan paginate qilish
        $posts = Kampus::where('active', 0)
            ->latest()
            ->with('kampusImages')
            ->paginate(10);



        // Postlarni foydalanuvchi tiliga moslashtirish
        $translatedPosts = collect($posts->items())->map(function ($post) use ($locale) {
            return [
                'id' => $post->id,
                'name' => $post->name[$locale] ?? null,
                'first_name' => $post->first_name[$locale] ?? null,
                'first_description' => $post->first_description[$locale] ?? null,
                'second_name' => $post->second_name[$locale] ?? null,
                'second_description' => $post->second_description[$locale] ?? null,
                'third_name' => $post->third_name[$locale] ?? null,
                'third_description' => $post->third_description[$locale] ?? null,
                'slug' => $post->slug?? null,
                'map' => $post->map ?? null,
                'audience_size' => $post->audience_size   ?? null,
                'educational_programs' => $post->educational_programs   ?? null,
                'green_zone' => $post->green_zone   ?? null,

                'images' => $post->kampusImages->map(function ($image) {
                    return [
                        'lg' => $image->lg_img, // Katta o'lchamdagi rasm URL
                        'md' => $image->md_img, // O'rta o'lchamdagi rasm URL
                        'sm' => $image->sm_img, // Kichik o'lchamdagi rasm URL
                    ];
                })->toArray(),
                'date' => $post->date,
                'views_count' => $post->views_count,
                'slug' => $post->slug,
                'meta_keywords' => $post->meta_keywords,
            ];
        });

        // Postlar va paginate ma'lumotlarini JSON formatida qaytarish
        return response()->json([
            'data' => $translatedPosts, // Tilga mos postlar
            'total' => $posts->total(), // Umumiy postlar soni
            'per_page' => $posts->perPage(), // Har bir sahifadagi postlar soni
            'current_page' => $posts->currentPage(), // Hozirgi sahifa raqami
            'last_page' => $posts->lastPage(), // Oxirgi sahifa raqami
            'next_page_url' => $posts->nextPageUrl(), // Keyingi sahifa URLi
            'prev_page_url' => $posts->previousPageUrl(), // Oldingi sahifa URLi
        ]);
    }

    public function show_kampus($slug)
    {
        // Foydalanuvchi tilini olish
        $locale = App::getLocale();

        // Slug orqali postni olish
        $post = Kampus::with( 'kampusImages')->where('slug', $slug)->first();

        if (is_null($post)) {
            return response()->json(['message' => 'Post not found or URL is not null'], 404);
        }
        // Postni foydalanuvchi tiliga moslashtirish
        $translatedPost = [
            'id' => $post->id,
            'name' => $post->name[$locale] ?? null,
            'first_name' => $post->first_name[$locale] ?? null,
            'first_description' => $post->first_description[$locale] ?? null,
            'second_name' => $post->second_name[$locale] ?? null,
            'second_description' => $post->second_description[$locale] ?? null,
            'third_name' => $post->third_name[$locale] ?? null,
            'third_description' => $post->third_description[$locale] ?? null,
            'slug' => $post->slug?? null,
            'map' => $post->map ?? null,
            'audience_size' => $post->audience_size   ?? null,
            'educational_programs' => $post->educational_programs   ?? null,
            'green_zone' => $post->green_zone   ?? null,

            'images' => $post->kampusImages->map(function ($image) {
                return [
                    'lg' => $image->lg_img, // Katta o'lchamdagi rasm URL
                    'md' => $image->md_img, // O'rta o'lchamdagi rasm URL
                    'sm' => $image->sm_img, // Kichik o'lchamdagi rasm URL
                ];
            })->toArray(),
            'date' => $post->date,
            'views_count' => $post->views_count,
            'slug' => $post->slug,
            'meta_keywords' => $post->meta_keywords,
        ];


        return response()->json($translatedPost);
    }

    public function get_test()
    {
        // Foydalanuvchi tilini olish
        $locale = App::getLocale();

        // Postlarni oxirgi qo'shilganidan boshlab olish va 10 tadan paginate qilish
        $posts = Kampus::where('active', 1)
            ->latest()
            ->with('kampusImages')
            ->paginate(10);



        // Postlarni foydalanuvchi tiliga moslashtirish
        $translatedPosts = collect($posts->items())->map(function ($post) use ($locale) {
            return [
                'id' => $post->id,
                'name' => $post->name[$locale] ?? null,
                'first_name' => $post->first_name[$locale] ?? null,
                'first_description' => $post->first_description[$locale] ?? null,
                'second_name' => $post->second_name[$locale] ?? null,
                'second_description' => $post->second_description[$locale] ?? null,
                'third_name' => $post->third_name[$locale] ?? null,
                'third_description' => $post->third_description[$locale] ?? null,
                'slug' => $post->slug?? null,
                'map' => $post->map ?? null,
                'audience_size' => $post->audience_size   ?? null,
                'educational_programs' => $post->educational_programs   ?? null,
                'green_zone' => $post->green_zone   ?? null,

                'images' => $post->kampusImages->map(function ($image) {
                    return [
                        'lg' => $image->lg_img, // Katta o'lchamdagi rasm URL
                        'md' => $image->md_img, // O'rta o'lchamdagi rasm URL
                        'sm' => $image->sm_img, // Kichik o'lchamdagi rasm URL
                    ];
                })->toArray(),
                'date' => $post->date,
                'views_count' => $post->views_count,
                'slug' => $post->slug,
                'meta_keywords' => $post->meta_keywords,
            ];
        });

        // Postlar va paginate ma'lumotlarini JSON formatida qaytarish
        return response()->json([
            'data' => $translatedPosts, // Tilga mos postlar
            'total' => $posts->total(), // Umumiy postlar soni
            'per_page' => $posts->perPage(), // Har bir sahifadagi postlar soni
            'current_page' => $posts->currentPage(), // Hozirgi sahifa raqami
            'last_page' => $posts->lastPage(), // Oxirgi sahifa raqami
            'next_page_url' => $posts->nextPageUrl(), // Keyingi sahifa URLi
            'prev_page_url' => $posts->previousPageUrl(), // Oldingi sahifa URLi
        ]);
    }

    public function show_test($slug)
    {
        // Foydalanuvchi tilini olish
        $locale = App::getLocale();

        // Slug orqali postni olish
        $post = Kampus::with( 'kampusImages')->where('slug', $slug)->first();

        if (is_null($post)) {
            return response()->json(['message' => 'Post not found or URL is not null'], 404);
        }
        // Postni foydalanuvchi tiliga moslashtirish
        $translatedPost = [
            'id' => $post->id,
            'name' => $post->name[$locale] ?? null,
            'first_name' => $post->first_name[$locale] ?? null,
            'first_description' => $post->first_description[$locale] ?? null,
            'second_name' => $post->second_name[$locale] ?? null,
            'second_description' => $post->second_description[$locale] ?? null,
            'third_name' => $post->third_name[$locale] ?? null,
            'third_description' => $post->third_description[$locale] ?? null,
            'slug' => $post->slug?? null,
            'map' => $post->map ?? null,
            'audience_size' => $post->audience_size   ?? null,
            'educational_programs' => $post->educational_programs   ?? null,
            'green_zone' => $post->green_zone   ?? null,

            'images' => $post->kampusImages->map(function ($image) {
                return [
                    'lg' => $image->lg_img, // Katta o'lchamdagi rasm URL
                    'md' => $image->md_img, // O'rta o'lchamdagi rasm URL
                    'sm' => $image->sm_img, // Kichik o'lchamdagi rasm URL
                ];
            })->toArray(),
            'date' => $post->date,
            'views_count' => $post->views_count,
            'slug' => $post->slug,
            'meta_keywords' => $post->meta_keywords,
        ];


        return response()->json($translatedPost);
    }


    public function get_video_post()
    {
        // Foydalanuvchi tilini olish
        $locale = App::getLocale();

        // Postlarni oxirgi qo'shilganidan boshlab olish va faqat video_link null bo'lmaganlarini 10 tadan paginate qilish
        $posts = Post::latest()
            ->whereNotNull('video_link') // Faqat video_link mavjud bo'lgan postlar
            ->with('postImages', 'postsCategories')
            ->paginate(10);


        // Postlarni foydalanuvchi tiliga moslashtirish
        $translatedPosts = collect($posts->items())->map(function ($post) use ($locale) {
            return [
                'id' => $post->id,
                'title' => $post->title[$locale] ?? null,
                'desc' => $post->desc[$locale] ?? null,
                'video_link' => $post->video_link, // Video link qo'shildi
                'images' => $post->postImages->map(function ($image) {
                    return [
                        'lg' => $image->lg_img, // Katta o'lchamdagi rasm URL
                        'md' => $image->md_img, // O'rta o'lchamdagi rasm URL
                        'sm' => $image->sm_img, // Kichik o'lchamdagi rasm URL
                    ];
                })->toArray(),
                'categories' => $post->postsCategories->map(function ($category) use ($locale) {
                    return [
                        'id' => $category->id,
                        'slug' => $category->slug,
                        'title' => $category->title[$locale] ?? null,
                        'name' => $category->title[$locale] ?? null, // Kategoriya nomini foydalanuvchi tilida chiqarish
                    ];
                })->toArray(),
                'date' => $post->date,
                'views_count' => $post->views_count,
                'slug' => $post->slug,
                'meta_keywords' => $post->meta_keywords,
            ];
        });

        // Postlar va paginate ma'lumotlarini JSON formatida qaytarish
        return response()->json([
            'data' => $translatedPosts,             // Tilga mos postlar
            'total' => $posts->total(),             // Umumiy postlar soni
            'per_page' => $posts->perPage(),        // Har bir sahifadagi postlar soni
            'current_page' => $posts->currentPage(), // Hozirgi sahifa raqami
            'last_page' => $posts->lastPage(),      // Oxirgi sahifa raqami
            'next_page_url' => $posts->nextPageUrl(), // Keyingi sahifa URLi
            'prev_page_url' => $posts->previousPageUrl(), // Oldingi sahifa URLi
        ]);
    }

    public function show_video_post($slug)
    {
        // Foydalanuvchi tilini olish
        $locale = App::getLocale();

        // Slug orqali video_link mavjud bo'lgan postni olish
        $post = Post::with('postsCategories', 'postImages')
            ->where('slug', $slug)
            ->whereNotNull('video_link') // Faqat video_link mavjud bo'lgan postlar
            ->first();

        // Agar post topilmasa, 404 xatolikni qaytaradi
        if (is_null($post)) {
            return response()->json(['message' => 'Post not found or does not have a video link'], 404);
        }

        // Postni har safar ko'rilganda views_countni oshirish
        $post->increment('views_count');

        // Postni foydalanuvchi tiliga moslashtirish
        $translatedPost = [
            'id' => $post->id,
            'title' => $post->title[$locale] ?? null,
            'desc' => $post->desc[$locale] ?? null,
            'video_link' => $post->video_link, // Video link qo'shildi
            'images' => $post->postImages->map(function ($image) {
                return [
                    'lg' => $image->lg_img, // Katta o'lchamdagi rasm URL
                    'md' => $image->md_img, // O'rta o'lchamdagi rasm URL
                    'sm' => $image->sm_img, // Kichik o'lchamdagi rasm URL
                ];
            })->toArray(),
            'categories' => $post->postsCategories->map(function ($category) use ($locale) {
                return [
                    'id' => $category->id,
                    'slug' => $category->slug,
                    'name' => $category->title[$locale] ?? null,
                    'title' => $category->title[$locale] ?? null, // Kategoriya nomini foydalanuvchi tilida chiqarish
                ];
            })->toArray(),
            'slug' => $post->slug,
            'date' => $post->date,
            'views_count' => $post->views_count, // Yangilangan views_count
            'meta_keywords' => $post->meta_keywords,
        ];

        // JSON javobni qaytarish
        return response()->json($translatedPost);
    }


    /**
     * Turkum ID bo'yicha yangiliklar — /categories/filter/{id}.
     * /news?category={id} bilan bir xil natija qaytaradi (eski URL saqlanadi).
     */
    public function show_categor_product(Request $request, $id)
    {
        $request->merge(['category' => $id]);

        return $this->get_posts($request);
    }

    public function get_categories()
    {
        $locale = App::getLocale();

        // Ildiz kategoriyalar (parent_id = 1 xizmatchi turkum, u chiqmaydi).
        // NULL parent_id ham ildiz hisoblanadi — SQLda "!= 1" NULLni tashlab ketadi.
        $categories = PostsCategory::where(function ($q) {
                $q->whereNull('parent_id')->orWhere('parent_id', '!=', 1);
            })
            ->with('children')
            ->latest()
            ->paginate(10);


        // Kategoriyalarni map qilish (asosiy va farzand kategoriyalarni tarjimalar bilan)
        $translatedPosts = $categories->map(function ($category) use ($locale) {
            return [
                'id' => $category->id,
                'title' => $category->title[$locale] ?? null,
                'desc' => $category->desc[$locale] ?? null,
                'children' => $category->children->map(function ($child) use ($locale) {
                    return [
                        'id' => $child->id,
                        'slug' => $child->slug,
                        'title' => $child->title[$locale] ?? null,
                        'desc' => $child->desc[$locale] ?? null,
                        'images' => [
                            'lg' => $child->lg_img,
                            'md' => $child->md_img,
                            'sm' => $child->sm_img,
                        ],
                    ];
                }),
                'images' => [
                    'lg' => $category->lg_img,
                    'md' => $category->md_img,
                    'sm' => $category->sm_img,
                ],
                'in_main' => $category->in_main,
                'view' => $category->view,
                'slug' => $category->slug,
            ];
        });

        return response()->json([
            'data' => $translatedPosts,
            'total' => $categories->total(),
            'per_page' => $categories->perPage(),
            'current_page' => $categories->currentPage(),
            'last_page' => $categories->lastPage(),
            'next_page_url' => $categories->nextPageUrl(),
            'prev_page_url' => $categories->previousPageUrl(),
        ]);
    }

    public function show_categories($slug)
    {
        $locale = App::getLocale();

        $category = PostsCategory::where('slug', $slug)->first();

        if (is_null($category)) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $posts = Post::whereHas('postsCategories', function ($query) use ($category) {
            $query->where('posts_category_id', $category->id);
        })
            ->whereNotNull('date') // NULL qiymatlarni chiqarib tashlash
            ->with(['postImages', 'postsCategories'])
//            ->latest()
            ->orderByRaw('STR_TO_DATE(date, "%Y-%m-%d") DESC') // Agar sana VARCHAR bo'lsa, bu ishlaydi
            ->paginate(10);



        // Turkum mavjud, lekin unda yangilik yo'q — bu "topilmadi" emas.
        // 404 qaytarilsa frontend xatolik sahifasini ko'rsatardi; to'g'risi —
        // 200 va bo'sh ro'yxat, sahifa "yangilik yo'q" holatini chizadi.

        $translatedPosts = $posts->map(function ($post) use ($locale) {
            return [
                'id' => $post->id,
                'title' => $post->title[$locale] ?? null,
                'subtitle' => $post->subtitle[$locale] ?? null,
                'desc' => $post->desc[$locale] ?? null,
                'images' => $post->postImages->map(function ($image) {
                    return [
                        'lg' => $image->lg_img,
                        'md' => $image->md_img,
                        'sm' => $image->sm_img,
                    ];
                })->toArray(),
                'date' => $post->date,
                'url' => $post->url,
                'file' => $post->file ? url($post->file) : null,
                'views_count' => $post->views_count,
                'slug' => $post->slug,
                'meta_keywords' => $post->meta_keywords,
            ];
        });

        return response()->json([
            'category' => [
                'id' => $category->id,
                'title' => $category->title[$locale] ?? null,
                'desc' => $category->desc[$locale] ?? null,
                'images' => [
                    'lg' => $category->lg_img,
                    'md' => $category->md_img,
                    'sm' => $category->sm_img,
                ],
                'in_main' => $category->in_main,
                'view' => $category->view,
                'slug' => $category->slug,
            ],
            'posts' => [
                'data' => $translatedPosts,
                'total' => $posts->total(),
                'per_page' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'next_page_url' => $posts->nextPageUrl(),
                'prev_page_url' => $posts->previousPageUrl(),
            ]
        ]);
    }
}
