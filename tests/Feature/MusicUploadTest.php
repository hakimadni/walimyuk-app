<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MusicUploadTest extends TestCase
{
    use RefreshDatabase;

    private function weddingPayload(int $userId, string $slug): array
    {
        return [
            'user_id'                  => $userId,
            'slug'                     => $slug,
            'status'                   => 'draft',
            'wedding_date'             => '2026-12-20 08:00:00',
            'timezone'                 => 'Asia/Jakarta',
            'cover_title'              => 'Wedding ' . $slug,
            'cover_subtitle'           => 'Subtitle ' . $slug,
            'welcome_text'             => 'Welcome',
            'closing_text'             => 'Closing',
            'rsvp_required'            => true,
            'comments_need_approval'   => false,
            'pax_buffer_percentage'    => 10,
        ];
    }

    public function test_admin_can_upload_music_file_and_it_saves(): void
    {
        Storage::fake('public');

        $admin   = User::factory()->create(['role' => 'super_admin']);
        $tenant  = User::factory()->create(['role' => 'tenant']);
        $wedding = Wedding::create($this->weddingPayload($tenant->id, 'music-upload-admin'));

        $mp3 = UploadedFile::fake()->create('song.mp3', 100, 'audio/mpeg');

        $response = $this->actingAs($admin)->put("/weddings/{$wedding->id}/builder", [
            'builder' => [
                'permissions' => ['music' => true],
                'content' => [
                    'music_file'     => $mp3,
                    'music_url'      => null,
                    'music_autoplay' => true,
                ],
            ],
        ]);

        $response->assertRedirect();

        $wedding->refresh();

        $uploadedUrl = data_get($wedding->theme_config, 'builder.content.music_uploaded_url');

        $this->assertNotNull($uploadedUrl, 'music_uploaded_url harus tersimpan setelah upload');
        $this->assertStringContainsString('/storage/weddings/', $uploadedUrl);

        // File should exist on disk
        Storage::disk('public')->assertExists(
            ltrim(parse_url($uploadedUrl, PHP_URL_PATH), '/storage/')
        );
    }

    public function test_tenant_with_music_permission_can_upload_music_file(): void
    {
        Storage::fake('public');

        $tenant  = User::factory()->create(['role' => 'tenant']);
        $wedding = Wedding::create($this->weddingPayload($tenant->id, 'music-upload-tenant'));

        // Pre-set permission music = true
        $wedding->update([
            'theme_config' => [
                'builder' => [
                    'permissions' => ['music' => true, 'tenant_builder_enabled' => true],
                    'content' => [],
                ],
            ],
        ]);

        $mp3 = UploadedFile::fake()->create('song.mp3', 100, 'audio/mpeg');

        $response = $this->actingAs($tenant)->put("/my-weddings/{$wedding->id}/builder", [
            'builder' => [
                'content' => [
                    'music_file'     => $mp3,
                    'music_url'      => null,
                    'music_autoplay' => true,
                ],
            ],
        ]);

        $response->assertRedirect();

        $wedding->refresh();

        $uploadedUrl = data_get($wedding->theme_config, 'builder.content.music_uploaded_url');

        $this->assertNotNull($uploadedUrl, 'music_uploaded_url harus tersimpan setelah upload oleh tenant');
        $this->assertStringContainsString('/storage/weddings/', $uploadedUrl);
    }

    public function test_music_url_saves_correctly_without_file_upload(): void
    {
        $admin   = User::factory()->create(['role' => 'super_admin']);
        $tenant  = User::factory()->create(['role' => 'tenant']);
        $wedding = Wedding::create($this->weddingPayload($tenant->id, 'music-url-only'));

        $response = $this->actingAs($admin)->put("/weddings/{$wedding->id}/builder", [
            'builder' => [
                'permissions' => ['music' => true],
                'content' => [
                    'music_url'      => 'https://example.com/test.mp3',
                    'music_autoplay' => true,
                ],
            ],
        ]);

        $response->assertRedirect();

        $wedding->refresh();

        $this->assertSame(
            'https://example.com/test.mp3',
            data_get($wedding->theme_config, 'builder.content.music_url')
        );
    }
}
