<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Admin paneldagi matn muharriri CKEditor 5 ekanini va rasm yuklash
 * CKEditor 5 kutgan formatda javob berishini tekshiradi.
 *
 * CKEditor 4 ga qaytilsa, uning CDN dagi nusxasi muharrir ustida
 * "This CKEditor 4 version is not secure" qizil ogohlantirishini chiqaradi.
 */
class AdminEditorTest extends TestCase
{
    private function admin(): User
    {
        return User::where('role', 'admin')->orderBy('id')->firstOrFail();
    }

    /** @dataProvider editorPages */
    public function test_pages_load_only_ckeditor5_in_uzbek(string $url): void
    {
        $response = $this->actingAs($this->admin())->get($url);

        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString('ckeditor5/41.4.2/classic/ckeditor.js', $html);
        $this->assertStringContainsString('translations/uz.js', $html);
        $this->assertStringContainsString("language: 'uz'", $html);

        // Eskirgan va ikkilangan versiyalar qolmasligi kerak.
        foreach (['4.16.2/standard', '4.20.2/standard', 'ckeditor5/36.0.1'] as $stale) {
            $this->assertStringNotContainsString($stale . '/ckeditor.js', $html);
        }
    }

    public static function editorPages(): array
    {
        return [
            'albom qo\'shish'         => ['/admin/works/create'],
            'dinamik menyu qo\'shish' => ['/admin/dynamic-menus/create'],
        ];
    }

    public function test_pasted_image_is_stored_and_returned_in_ckeditor5_format(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/upload-image', [
            'upload' => UploadedFile::fake()->image('word-dan-nusxa.png', 800, 600),
        ]);

        $response->assertOk()
            ->assertJson(['uploaded' => 1])
            ->assertJsonStructure(['uploaded', 'fileName', 'url']);

        $fileName = $response->json('fileName');

        $this->assertFileExists(public_path('upload/content/' . $fileName));

        // Asl nom emas, tasodifiy nom — fayllar bir-birini o'chirmasligi uchun.
        $this->assertStringNotContainsString('word-dan-nusxa', $fileName);
        $this->assertStringEndsWith('/upload/content/' . $fileName, $response->json('url'));

        @unlink(public_path('upload/content/' . $fileName));
    }

    public function test_non_image_upload_is_rejected_with_a_message(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/upload-image', [
            'upload' => UploadedFile::fake()->create('hujjat.pdf', 10, 'application/pdf'),
        ]);

        $response->assertOk()->assertJson(['uploaded' => 0]);
        $this->assertNotEmpty($response->json('error.message'));
    }
}
