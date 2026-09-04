<?php

namespace Tests\Feature;

use App\Models\Post;
use Tests\TestCase;

/**
 * Yangilikka biriktirilgan video.
 *
 * Ilgari video havolasi qo'shilgan zahoti yangilik `/news` ro'yxatidan ham,
 * `/news/{slug}` sahifasidan ham butunlay yo'qolib qolardi — chunki ikkala
 * so'rovda ham `whereNull('video_link')` sharti turgan edi. Foydalanuvchi
 * uchun bu "video ko'rinmayapti" bo'lib bilinardi.
 */
class NewsVideoTest extends TestCase
{
    private array $trash = [];

    protected function tearDown(): void
    {
        foreach ($this->trash as $model) {
            $model->forceDelete();
        }

        parent::tearDown();
    }

    private function api(string $path, string $locale = 'uz')
    {
        $prefix = trim((string) config('api.prefix'), '/');

        return $this->withHeaders(['Accept-Language' => $locale])->getJson('/' . $prefix . $path);
    }

    private function makePost(?string $video): Post
    {
        $title = 'Videoli yangilik ' . uniqid();

        $post = Post::create([
            'title'      => ['uz' => $title, 'ru' => $title, 'en' => $title],
            'desc'       => ['uz' => 'Matn', 'ru' => 'Matn', 'en' => 'Matn'],
            'slug'       => 'videoli-yangilik-' . uniqid(),
            'date'       => now()->toDateString(),
            'video_link' => $video,
        ]);

        $this->trash[] = $post;

        return $post;
    }

    public function test_news_with_a_video_stays_in_the_news_list(): void
    {
        $post = $this->makePost('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        $titles = collect($this->api('/news?per_page=50')->assertOk()->json('data'))
            ->pluck('title');

        $this->assertContains(
            $post->title['uz'],
            $titles->all(),
            'Video havolasi qoʻshilgan yangilik roʻyxatdan yoʻqolmasligi kerak'
        );
    }

    public function test_news_detail_opens_and_returns_the_video_link(): void
    {
        $video = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
        $post = $this->makePost($video);

        $this->api('/news/' . $post->slug)
            ->assertOk()
            ->assertJsonPath('video_link', $video);
    }

    public function test_news_without_a_video_returns_null(): void
    {
        $post = $this->makePost(null);

        $this->api('/news/' . $post->slug)
            ->assertOk()
            ->assertJsonPath('video_link', null);
    }

    public function test_video_section_still_shows_only_videos(): void
    {
        $withVideo = $this->makePost('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        $withoutVideo = $this->makePost(null);

        $titles = collect($this->api('/video_news')->assertOk()->json('data'))
            ->pluck('title');

        $this->assertContains($withVideo->title['uz'], $titles->all());
        $this->assertNotContains($withoutVideo->title['uz'], $titles->all());
    }

    /**
     * Muharrir ichiga qoʻyilgan video haqiqiy <iframe> boʻlib saqlanishi
     * kerak — <oembed> tegini brauzer chizmaydi.
     */
    public function test_editor_saves_embedded_media_as_an_iframe(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->orderBy('id')->firstOrFail();

        $html = $this->actingAs($admin)->get('/admin/posts/create')->getContent();

        $this->assertStringContainsString('previewsInData: true', $html);
    }
}
