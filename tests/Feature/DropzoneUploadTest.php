<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Admin paneldagi rasm yuklash (dropzone).
 *
 * Bu yo'l butun panel bo'ylab ishlatiladi: galereya albomlari, modal
 * xabarlar, bannerlar, sahifa rasmlari. Ilgari u `orientate()` chaqirgani
 * uchun `exif` kengaytmasi yo'q serverda butunlay ishlamasdi.
 */
class DropzoneUploadTest extends TestCase
{
    private function admin(): User
    {
        return User::where('role', 'admin')->orderBy('id')->firstOrFail();
    }

    private function cleanup(string $fileName): void
    {
        foreach (['', '200/', '600/'] as $size) {
            @unlink(public_path('upload/images/' . $size . $fileName));
        }
    }

    public function test_image_upload_returns_three_sizes(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/upload_from_dropzone', [
            'file' => UploadedFile::fake()->image('rasm.jpg', 1200, 800),
        ]);

        $response->assertOk()->assertJsonStructure([
            'file_name', 'original_url', 'small_url', 'large_url',
        ]);

        $fileName = $response->json('file_name');

        $this->assertStringEndsWith('.webp', $fileName, 'Fayl webp formatda saqlanishi kerak');

        foreach (['', '200/', '600/'] as $size) {
            $this->assertFileExists(public_path('upload/images/' . $size . $fileName));
        }

        $this->cleanup($fileName);
    }

    /**
     * `exif` kengaytmasi yo'q muhitda ham yuklash ishlashi kerak —
     * aynan shu holat admin panelda "Reading Exif data is not supported"
     * xatosini berardi.
     */
    public function test_upload_works_without_the_exif_extension(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/upload_from_dropzone', [
            'file' => UploadedFile::fake()->image('telefon-rasmi.jpg', 800, 600),
        ]);

        $response->assertOk();

        $body = $response->getContent();

        $this->assertStringNotContainsString('Exif', $body);
        $this->assertNull($response->json('error'));

        $this->cleanup($response->json('file_name'));
    }

    public function test_png_is_accepted(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/upload_from_dropzone', [
            'file' => UploadedFile::fake()->image('shaffof.png', 600, 600),
        ]);

        $response->assertOk();
        $this->assertNotNull($response->json('file_name'));

        $this->cleanup($response->json('file_name'));
    }

    public function test_missing_file_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/upload_from_dropzone', [])
            ->assertStatus(400);
    }
}
