<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeddingBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_builder_page(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $wedding = Wedding::create($this->weddingPayload($tenant->id, 'builder-open'));

        $response = $this->actingAs($superAdmin)->get("/weddings/{$wedding->id}/builder");

        $response->assertOk();
    }

    public function test_super_admin_can_update_builder_permissions_and_content(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $wedding = Wedding::create($this->weddingPayload($tenant->id, 'builder-update'));

        $response = $this->actingAs($superAdmin)->put("/weddings/{$wedding->id}/builder", [
            'builder' => [
                'permissions' => [
                    'music' => true,
                    'custom_font' => true,
                    'custom_decorations' => true,
                ],
                'content' => [
                    'font_family' => 'font-serif',
                    'music_url' => 'https://example.com/music.mp3',
                    'palette' => [
                        'primary' => '#065f46',
                        'secondary' => '#d4af37',
                    ],
                    'blocks' => [
                        ['id' => 'ayat', 'label' => 'Ayat', 'enabled' => true],
                        ['id' => 'countdown', 'label' => 'Countdown', 'enabled' => true],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('weddings', [
            'id' => $wedding->id,
        ]);
    }

    public function test_tenant_cannot_open_builder_for_other_users_wedding(): void
    {
        $tenantOwner = User::factory()->create(['role' => 'tenant']);
        $otherTenant = User::factory()->create(['role' => 'tenant']);
        $wedding = Wedding::create($this->weddingPayload($tenantOwner->id, 'builder-forbidden'));

        $response = $this->actingAs($otherTenant)->get("/my-weddings/{$wedding->id}/builder");

        $response->assertForbidden();
    }

    public function test_builder_supports_4_corner_content_decorations(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $wedding = Wedding::create($this->weddingPayload($tenant->id, 'builder-4-corners'));

        $response = $this->actingAs($superAdmin)->put("/weddings/{$wedding->id}/builder", [
            'builder' => [
                'permissions' => [
                    'custom_decorations' => true,
                ],
                'content' => [
                    'content_decorations' => [
                        'top_left' => [
                            'type' => 'premium-kiri-atas',
                            'x' => 0,
                            'y' => 0,
                            'size' => 160,
                            'opacity' => 90,
                            'animation' => 'none',
                        ],
                        'top_right' => [
                            'type' => 'premium-kanan-atas',
                            'x' => 100,
                            'y' => 0,
                            'size' => 160,
                            'opacity' => 90,
                            'animation' => 'none',
                        ],
                        'bottom_left' => [
                            'type' => 'premium-kiri-bawah',
                            'x' => 0,
                            'y' => 100,
                            'size' => 160,
                            'opacity' => 90,
                            'animation' => 'none',
                        ],
                        'bottom_right' => [
                            'type' => 'premium-kanan-bawah',
                            'x' => 100,
                            'y' => 100,
                            'size' => 160,
                            'opacity' => 90,
                            'animation' => 'none',
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect();
        $wedding->refresh();
        $this->assertEquals('premium-kiri-atas', $wedding->theme_config['builder']['content']['content_decorations']['top_left']['type']);
        $this->assertEquals(0, $wedding->theme_config['builder']['content']['content_decorations']['top_left']['x']);
        $this->assertEquals('premium-kanan-atas', $wedding->theme_config['builder']['content']['content_decorations']['top_right']['type']);
        $this->assertEquals(100, $wedding->theme_config['builder']['content']['content_decorations']['top_right']['x']);
    }

    private function weddingPayload(int $userId, string $slug): array
    {
        return [
            'user_id' => $userId,
            'slug' => $slug,
            'status' => 'draft',
            'wedding_date' => '2026-12-20 08:00:00',
            'timezone' => 'Asia/Jakarta',
            'cover_title' => 'Wedding '.$slug,
            'cover_subtitle' => 'Subtitle '.$slug,
            'welcome_text' => 'Welcome',
            'closing_text' => 'Closing',
            'rsvp_required' => true,
            'comments_need_approval' => false,
            'pax_buffer_percentage' => 10,
        ];
    }
}
