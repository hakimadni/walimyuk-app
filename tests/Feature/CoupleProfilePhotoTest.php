<?php

namespace Tests\Feature;

use App\Models\CoupleProfile;
use App\Models\Guest;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CoupleProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    private function weddingPayload(int $userId, string $slug = 'couple-photo-test'): array
    {
        return [
            'user_id' => $userId,
            'title' => 'The Wedding of Groom & Bride',
            'slug' => $slug,
            'cover_title' => 'The Wedding of Groom & Bride',
            'status' => 'published',
            'wedding_date' => '2026-10-10',
            'theme_config' => [
                'builder' => [
                    'content' => [
                        'palette' => [
                            'primary' => '#065f46',
                            'secondary' => '#d4af37',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function test_user_can_upload_groom_and_bride_profile_photos(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'tenant']);
        $wedding = Wedding::create($this->weddingPayload($user->id));

        $groomPhoto = UploadedFile::fake()->image('groom.jpg', 600, 600);
        $bridePhoto = UploadedFile::fake()->image('bride.png', 600, 600);

        $response = $this->actingAs($user)->put("/weddings/{$wedding->id}/couple-profiles", [
            'profiles' => [
                [
                    'role' => 'groom',
                    'full_name' => 'Farhan Ramadhan, S.Kom.',
                    'nickname' => 'Farhan',
                    'photo_file' => $groomPhoto,
                    'sort_order' => 1,
                ],
                [
                    'role' => 'bride',
                    'full_name' => 'Aisyah Salsabila, S.Ked.',
                    'nickname' => 'Aisyah',
                    'photo_file' => $bridePhoto,
                    'sort_order' => 2,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $groom = CoupleProfile::where('wedding_id', $wedding->id)->where('role', 'groom')->first();
        $bride = CoupleProfile::where('wedding_id', $wedding->id)->where('role', 'bride')->first();

        $this->assertNotNull($groom);
        $this->assertNotNull($groom->photo_path);
        $this->assertStringContainsString("/storage/weddings/{$wedding->id}/couples/", $groom->photo_path);

        $this->assertNotNull($bride);
        $this->assertNotNull($bride->photo_path);
        $this->assertStringContainsString("/storage/weddings/{$wedding->id}/couples/", $bride->photo_path);

        // Verify files exist in storage
        $groomRelPath = ltrim(parse_url($groom->photo_path, PHP_URL_PATH), '/storage/');
        Storage::disk('public')->assertExists($groomRelPath);

        $brideRelPath = ltrim(parse_url($bride->photo_path, PHP_URL_PATH), '/storage/');
        Storage::disk('public')->assertExists($brideRelPath);
    }

    public function test_user_can_remove_couple_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'tenant']);
        $wedding = Wedding::create($this->weddingPayload($user->id));

        // Create an initial photo file in fake storage
        $filePath = "weddings/{$wedding->id}/couples/initial_groom.jpg";
        Storage::disk('public')->put($filePath, 'fake-content');

        $profile = CoupleProfile::create([
            'wedding_id' => $wedding->id,
            'role' => 'groom',
            'full_name' => 'Farhan Ramadhan',
            'photo_path' => Storage::disk('public')->url($filePath),
            'sort_order' => 1,
        ]);

        Storage::disk('public')->assertExists($filePath);

        // Update with photo removed (photo_path = null / empty string)
        $response = $this->actingAs($user)->put("/weddings/{$wedding->id}/couple-profiles", [
            'profiles' => [
                [
                    'id' => $profile->id,
                    'role' => 'groom',
                    'full_name' => 'Farhan Ramadhan',
                    'photo_path' => '',
                    'sort_order' => 1,
                ],
            ],
        ]);

        $response->assertRedirect();

        $profile->refresh();
        $this->assertNull($profile->photo_path);

        // Verify the old file was deleted from disk
        Storage::disk('public')->assertMissing($filePath);
    }

    public function test_couple_profile_photo_url_accessor(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'tenant']);
        $wedding = Wedding::create($this->weddingPayload($user->id));

        // 1. With storage path
        $profile1 = CoupleProfile::create([
            'wedding_id' => $wedding->id,
            'role' => 'groom',
            'full_name' => 'Farhan',
            'photo_path' => "weddings/{$wedding->id}/couples/groom.jpg",
            'sort_order' => 1,
        ]);

        $this->assertEquals(Storage::disk('public')->url("weddings/{$wedding->id}/couples/groom.jpg"), $profile1->photo_url);

        // 2. With external URL
        $profile2 = CoupleProfile::create([
            'wedding_id' => $wedding->id,
            'role' => 'bride',
            'full_name' => 'Aisyah',
            'photo_path' => 'https://example.com/photos/bride.jpg',
            'sort_order' => 2,
        ]);

        $this->assertEquals('https://example.com/photos/bride.jpg', $profile2->photo_url);

        // 3. With null
        $profile3 = CoupleProfile::create([
            'wedding_id' => $wedding->id,
            'role' => 'groom',
            'full_name' => 'Empty',
            'photo_path' => null,
            'sort_order' => 3,
        ]);

        $this->assertNull($profile3->photo_url);
    }

    public function test_public_invitation_renders_couple_photos(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'tenant']);
        $wedding = Wedding::create($this->weddingPayload($user->id, 'wedding-invitation-photos'));

        $guest = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Tamu Kehormatan',
            'phone' => '081234567890',
            'token' => 'guest-secret-token-123',
            'max_pax' => 2,
        ]);

        CoupleProfile::create([
            'wedding_id' => $wedding->id,
            'role' => 'groom',
            'full_name' => 'Farhan Ramadhan',
            'photo_path' => '/storage/weddings/1/couples/groom.jpg',
            'sort_order' => 1,
        ]);

        CoupleProfile::create([
            'wedding_id' => $wedding->id,
            'role' => 'bride',
            'full_name' => 'Aisyah Salsabila',
            'photo_path' => '/storage/weddings/1/couples/bride.jpg',
            'sort_order' => 2,
        ]);

        $response = $this->get("/w/{$wedding->slug}?token={$guest->token}");

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Public/InvitationShell')
            ->has('coupleProfiles', 2)
            ->where('coupleProfiles.0.photo_path', '/storage/weddings/1/couples/groom.jpg')
            ->where('coupleProfiles.1.photo_path', '/storage/weddings/1/couples/bride.jpg')
            ->where('coupleProfiles.0.photo_url', '/storage/weddings/1/couples/groom.jpg')
            ->where('coupleProfiles.1.photo_url', '/storage/weddings/1/couples/bride.jpg')
        );
    }

    public function test_user_can_customize_couple_photo_frame_style(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'tenant']);
        $wedding = Wedding::create($this->weddingPayload($user->id, 'wedding-frame-test'));

        $response = $this->actingAs($user)->put("/weddings/{$wedding->id}/couple-profiles", [
            'couple_photo_frame' => 'portrait',
            'profiles' => [
                [
                    'role' => 'groom',
                    'full_name' => 'Farhan',
                    'sort_order' => 1,
                ],
                [
                    'role' => 'bride',
                    'full_name' => 'Aisyah',
                    'sort_order' => 2,
                ],
            ],
        ]);

        $response->assertRedirect();
        $wedding->refresh();

        $this->assertEquals('portrait', data_get($wedding->theme_config, 'builder.content.couple_photo_frame'));

        // Verify public invitation receives the themeConfig with portrait frame
        $guest = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Tamu',
            'token' => 'guest-frame-token',
            'max_pax' => 1,
        ]);

        $publicResponse = $this->get("/w/{$wedding->slug}?token={$guest->token}");
        $publicResponse->assertOk();
        $publicResponse->assertInertia(fn (Assert $page) => $page
            ->where('themeConfig.builder.content.couple_photo_frame', 'portrait')
        );
    }
}
