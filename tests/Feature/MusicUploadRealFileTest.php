<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Test menggunakan file MP3 asli dari Downloads:
 * "Dj Masih Mencintainya - Papinka Terbaru 2020 (Dj MNR Remix Slow).mp3" (4.42 MB)
 */
class MusicUploadRealFileTest extends TestCase
{
    use RefreshDatabase;

    private string $realMp3Path = '/Users/user/Downloads/Dj Masih Mencintainya - Papinka Terbaru 2020 (Dj MNR Remix Slow).mp3';

    private function weddingPayload(int $userId, string $slug): array
    {
        return [
            'user_id'                => $userId,
            'slug'                   => $slug,
            'status'                 => 'draft',
            'wedding_date'           => '2026-12-20 08:00:00',
            'timezone'               => 'Asia/Jakarta',
            'cover_title'            => 'Wedding ' . $slug,
            'cover_subtitle'         => 'Subtitle ' . $slug,
            'welcome_text'           => 'Welcome',
            'closing_text'           => 'Closing',
            'rsvp_required'          => true,
            'comments_need_approval' => false,
            'pax_buffer_percentage'  => 10,
        ];
    }

    public function test_real_mp3_file_4mb_can_be_uploaded_by_admin(): void
    {
        if (! file_exists($this->realMp3Path)) {
            $this->markTestSkipped('File MP3 asli tidak ditemukan: ' . $this->realMp3Path);
        }

        Storage::fake('public');

        $admin   = User::factory()->create(['role' => 'super_admin']);
        $tenant  = User::factory()->create(['role' => 'tenant']);
        $wedding = Wedding::create($this->weddingPayload($tenant->id, 'real-mp3-admin'));

        // Buat UploadedFile dari file MP3 asli
        $tmpPath = sys_get_temp_dir() . '/test_real_music.mp3';
        copy($this->realMp3Path, $tmpPath);

        $mp3 = new UploadedFile(
            $tmpPath,
            'Dj Masih Mencintainya.mp3',
            'audio/mpeg',
            null,
            true // test mode: skip PHP error check
        );

        $fileSizeMB = round(filesize($this->realMp3Path) / 1024 / 1024, 2);

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
        $autoplay    = data_get($wedding->theme_config, 'builder.content.music_autoplay');

        $this->assertNotNull($uploadedUrl,
            "File MP3 {$fileSizeMB}MB harus tersimpan. music_uploaded_url tidak boleh null."
        );
        $this->assertTrue((bool) $autoplay, 'music_autoplay harus tersimpan sebagai true');

        // Verifikasi file benar-benar ada di storage
        // Storage::fake menghasilkan URL relatif (/storage/...) di test environment
        $storagePath = preg_replace('#^.*?/storage/#', '', $uploadedUrl);
        Storage::disk('public')->assertExists($storagePath);

        @unlink($tmpPath);
    }

    public function test_real_mp3_file_uploaded_url_is_accessible(): void
    {
        if (! file_exists($this->realMp3Path)) {
            $this->markTestSkipped('File MP3 asli tidak ditemukan: ' . $this->realMp3Path);
        }

        Storage::fake('public');

        $admin   = User::factory()->create(['role' => 'super_admin']);
        $tenant  = User::factory()->create(['role' => 'tenant']);
        $wedding = Wedding::create($this->weddingPayload($tenant->id, 'real-mp3-url'));

        $tmpPath = sys_get_temp_dir() . '/test_real_music2.mp3';
        copy($this->realMp3Path, $tmpPath);

        $mp3 = new UploadedFile($tmpPath, 'Dj Masih Mencintainya.mp3', 'audio/mpeg', null, true);

        $this->actingAs($admin)->put("/weddings/{$wedding->id}/builder", [
            'builder' => [
                'permissions' => ['music' => true],
                'content' => ['music_file' => $mp3],
            ],
        ]);

        $wedding->refresh();
        $uploadedUrl = data_get($wedding->theme_config, 'builder.content.music_uploaded_url');

        $this->assertNotNull($uploadedUrl);
        // URL berisi path /storage/weddings/ (bisa relatif di test, absolut di production)
        $this->assertStringContainsString('/storage/weddings/', $uploadedUrl,
            'URL harus mengarah ke storage weddings (APP_URL benar jika ada http://localhost:8000)'
        );

        @unlink($tmpPath);
    }

    public function test_real_mp3_file_uploaded_by_tenant_with_music_permission(): void
    {
        if (! file_exists($this->realMp3Path)) {
            $this->markTestSkipped('File MP3 asli tidak ditemukan: ' . $this->realMp3Path);
        }

        Storage::fake('public');

        $tenant  = User::factory()->create(['role' => 'tenant']);
        $wedding = Wedding::create($this->weddingPayload($tenant->id, 'real-mp3-tenant'));

        $wedding->update([
            'theme_config' => [
                'builder' => [
                    'permissions' => ['music' => true, 'tenant_builder_enabled' => true],
                    'content'     => [],
                ],
            ],
        ]);

        $tmpPath = sys_get_temp_dir() . '/test_real_music3.mp3';
        copy($this->realMp3Path, $tmpPath);

        $mp3 = new UploadedFile($tmpPath, 'Dj Masih Mencintainya.mp3', 'audio/mpeg', null, true);

        $response = $this->actingAs($tenant)->put("/my-weddings/{$wedding->id}/builder", [
            'builder' => [
                'content' => [
                    'music_file'     => $mp3,
                    'music_autoplay' => true,
                ],
            ],
        ]);

        $response->assertRedirect();

        $wedding->refresh();

        $uploadedUrl = data_get($wedding->theme_config, 'builder.content.music_uploaded_url');

        $this->assertNotNull($uploadedUrl,
            'Tenant dengan permission music=true harus bisa upload MP3 4.42MB'
        );

        @unlink($tmpPath);
    }
}
