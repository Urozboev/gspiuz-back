<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Work;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class WorkUploadTest extends TestCase
{
    public function test_album_cover_lands_where_the_api_reads_it(): void
    {
        $admin = User::where('role', 'admin')->orderBy('id')->firstOrFail();

        $file = UploadedFile::fake()->image('cover.jpg', 1200, 675);

        $response = $this->actingAs($admin)->post('/admin/works', [
            'title' => ['uz' => 'Upload tekshiruvi', 'ru' => 'Upload tekshiruvi', 'en' => 'Upload tekshiruvi'],
            'desc'  => ['uz' => 'test', 'ru' => 'test', 'en' => 'test'],
            'main_img' => $file,
        ]);

        $work = Work::where('title->uz', 'Upload tekshiruvi')->firstOrFail();

        $this->assertNotNull($work->main_img, 'main_img saqlanmadi');

        foreach (['', '200/', '600/'] as $size) {
            $path = public_path('upload/images/' . $size . $work->main_img);
            $this->assertFileExists($path);
        }

        // Model shu manzilni qaytaradi — fayl aynan shu yerda turishi kerak.
        $this->assertStringContainsString('/upload/images/' . $work->main_img, $work->lg_main_img);

        // Tozalash.
        foreach (['', '200/', '600/'] as $size) {
            @unlink(public_path('upload/images/' . $size . $work->main_img));
        }
        $work->forceDelete();

        $response->assertRedirect(route('works.index'));
    }
}
