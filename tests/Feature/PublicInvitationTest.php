<?php

namespace Tests\Feature;

use App\Models\CoupleProfile;
use App\Models\Guest;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_invitation_renders_with_couple_and_guest_data(): void
    {
        $user = User::factory()->create();

        $wedding = Wedding::create([
            'user_id' => $user->id,
            'slug' => 'hakim-dhanya-test',
            'status' => 'published',
            'cover_title' => 'The Wedding of Hakim & Dhanya',
            'wedding_date' => '2026-11-14 08:30:00',
            'theme_config' => [
                'builder' => [
                    'content' => [
                        'custom_text' => [
                            'cover_hashtag' => '#selamANYAuntukHAKIM',
                        ],
                    ],
                ],
            ],
        ]);

        CoupleProfile::create([
            'wedding_id' => $wedding->id,
            'role' => 'groom',
            'full_name' => 'Muhammad Hakim Prasetya, S.T.',
            'nickname' => 'Hakim',
            'sort_order' => 1,
        ]);

        CoupleProfile::create([
            'wedding_id' => $wedding->id,
            'role' => 'bride',
            'full_name' => 'Dhanya Ramadhani, S.Ked.',
            'nickname' => 'Dhanya',
            'sort_order' => 2,
        ]);

        $guest = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Tamu Kehormatan',
            'max_pax' => 2,
        ]);

        $response = $this->get(route('public.invitation', [
            'wedding' => $wedding->slug,
            'token' => $guest->token,
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Public/InvitationShell')
            ->has('wedding')
            ->has('coupleProfiles', 2)
            ->where('coupleProfiles.0.nickname', 'Hakim')
            ->where('coupleProfiles.1.nickname', 'Dhanya')
            ->where('guest.name', 'Tamu Kehormatan')
            ->where('themeConfig.builder.content.custom_text.cover_hashtag', '#selamANYAuntukHAKIM')
        );
    }

    public function test_tenant_can_update_cover_hashtag_in_builder(): void
    {
        $user = User::factory()->create();

        $wedding = Wedding::create([
            'user_id' => $user->id,
            'slug' => 'test-wedding-builder',
            'status' => 'draft',
            'wedding_date' => '2026-11-14 08:30:00',
            'theme_config' => [
                'builder' => [
                    'permissions' => [
                        'tenant_builder_enabled' => true,
                        'custom_text' => true,
                    ],
                    'content' => [
                        'custom_text' => [
                            'cover_hashtag' => null,
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->actingAs($user)->put("/dashboard/my-weddings/{$wedding->id}/builder", [
            'builder' => [
                'content' => [
                    'custom_text' => [
                        'cover_hashtag' => '#selamANYAuntukHAKIM',
                    ],
                ],
            ],
        ]);

        $response->assertRedirect();

        $wedding->refresh();
        $this->assertEquals(
            '#selamANYAuntukHAKIM',
            data_get($wedding->theme_config, 'builder.content.custom_text.cover_hashtag')
        );
    }
}
