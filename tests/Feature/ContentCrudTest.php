<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\Work;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Kundalik ishlatiladigan bo'limlarning to'liq aylanasi:
 * admin paneldan yaratish → saytdagi API'da ko'rinish → o'chirish.
 */
class ContentCrudTest extends TestCase
{
    private array $trash = [];

    protected function tearDown(): void
    {
        foreach ($this->trash as $model) {
            $model->forceDelete();
        }

        parent::tearDown();
    }

    private function admin(): User
    {
        return User::where('role', 'admin')->orderBy('id')->firstOrFail();
    }

    private function api(string $path, string $locale = 'uz')
    {
        $prefix = trim((string) config('api.prefix'), '/');

        return $this->withHeaders(['Accept-Language' => $locale])->getJson('/' . $prefix . $path);
    }

    /** Dropzone orqali rasm yuklab, fayl nomini qaytaradi. */
    private function uploadImage(): string
    {
        $response = $this->actingAs($this->admin())->post('/admin/upload_from_dropzone', [
            'file' => UploadedFile::fake()->image('rasm.jpg', 900, 600),
        ]);

        $response->assertOk();

        return $response->json('file_name');
    }

    private function removeImage(string $fileName): void
    {
        foreach (['', '200/', '600/'] as $size) {
            @unlink(public_path('upload/images/' . $size . $fileName));
        }
    }

    public function test_news_created_in_admin_appears_on_the_site(): void
    {
        $title = 'Sinov yangiligi ' . uniqid();
        $image = $this->uploadImage();

        $this->actingAs($this->admin())->post('/admin/posts', [
            'title'            => ['uz' => $title, 'ru' => $title, 'en' => $title],
            'desc'             => ['uz' => 'Sinov matni', 'ru' => 'Sinov matni', 'en' => 'Sinov matni'],
            'date'             => now()->toDateString(),
            'dropzone_images'  => [$image],
        ])->assertStatus(302);

        $post = Post::where('title->uz', $title)->first();

        $this->assertNotNull($post, 'Yangilik yaratilmadi');
        $this->trash[] = $post;

        // Saytdagi ro'yxatda ko'rinishi kerak.
        $titles = collect($this->api('/news')->assertOk()->json('data'))->pluck('title');
        $this->assertContains($title, $titles->all(), 'Yangilik saytda koʻrinmadi');

        // Detal sahifasi ochilishi kerak.
        $this->api('/news/' . $post->slug)->assertOk();

        // Rasm biriktirilgan bo'lishi kerak.
        $this->assertGreaterThan(0, $post->postImages()->count(), 'Rasm biriktirilmadi');

        $this->removeImage($image);
    }

    public function test_gallery_album_created_in_admin_appears_on_the_site(): void
    {
        $title = 'Sinov albomi ' . uniqid();
        $image = $this->uploadImage();

        $this->actingAs($this->admin())->post('/admin/works', [
            'title'           => ['uz' => $title, 'ru' => $title, 'en' => $title],
            'desc'            => ['uz' => 'Sinov albomi', 'ru' => 'Sinov', 'en' => 'Test'],
            'dropzone_images' => [$image],
        ])->assertStatus(302);

        $work = Work::where('title->uz', $title)->first();

        $this->assertNotNull($work, 'Albom yaratilmadi');
        $this->trash[] = $work;

        $titles = collect($this->api('/gallery')->assertOk()->json('data'))->pluck('title');
        $this->assertContains($title, $titles->all(), 'Albom galereyada koʻrinmadi');

        $this->api('/gallery/' . $work->id)->assertOk();

        $this->removeImage($image);
    }

    public function test_vacancy_created_in_admin_appears_on_the_site(): void
    {
        $title = 'Sinov vakansiyasi ' . uniqid();

        $this->actingAs($this->admin())->post('/admin/vacancies', [
            'title'    => ['uz' => $title, 'ru' => $title, 'en' => $title],
            'desc'     => ['uz' => 'Talablar', 'ru' => 'Talablar', 'en' => 'Talablar'],
            // `location` — oddiy matn ustuni, ko'p tilli emas.
            'location' => 'Pedagogika fakulteti',
        ])->assertStatus(302);

        $vacancy = Vacancy::where('title->uz', $title)->first();

        $this->assertNotNull($vacancy, 'Vakansiya yaratilmadi');
        $this->trash[] = $vacancy;

        $rows = collect($this->api('/vacancies')->assertOk()->json('data'));

        $this->assertContains($title, $rows->pluck('title')->all(), 'Vakansiya saytda koʻrinmadi');

        // Admin formasida "Ish joyi" maydoni bor — u saytga yetib borishi kerak.
        $this->assertSame(
            'Pedagogika fakulteti',
            $rows->firstWhere('title', $title)['location'] ?? null,
            'Ish joyi saytda koʻrinmadi'
        );
    }

    public function test_popup_created_in_admin_appears_in_the_popups_endpoint(): void
    {
        $title = 'Sinov xabari ' . uniqid();

        $this->actingAs($this->admin())->post('/admin/popups', [
            'title'     => ['uz' => $title, 'ru' => $title, 'en' => $title],
            'desc'      => ['uz' => '<p>Matn</p>', 'ru' => '<p>Matn</p>', 'en' => '<p>Matn</p>'],
            'active'    => 1,
            'order'     => 0,
            'starts_at' => now()->subDay()->toDateString(),
            'ends_at'   => now()->addDay()->toDateString(),
        ])->assertStatus(302);

        $popup = \App\Models\Rek::where('title->uz', $title)->first();

        $this->assertNotNull($popup, 'Modal xabar yaratilmadi');
        $this->trash[] = $popup;

        $titles = collect($this->api('/popups')->assertOk()->json('data'))->pluck('title');
        $this->assertContains($title, $titles->all(), 'Modal xabar endpointda koʻrinmadi');
    }

    public function test_popup_rejects_an_end_date_before_the_start(): void
    {
        $title = 'Notoʻgʻri sana ' . uniqid();

        $this->actingAs($this->admin())->post('/admin/popups', [
            'title'     => ['uz' => $title],
            'starts_at' => now()->addDays(5)->toDateString(),
            'ends_at'   => now()->toDateString(),
        ]);

        $this->assertNull(
            \App\Models\Rek::where('title->uz', $title)->first(),
            'Tugash sanasi boshlanishdan oldin boʻlsa yozuv yaratilmasligi kerak'
        );
    }
}
