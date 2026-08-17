<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeddingBuilderTenantAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_builder_update_is_forbidden_when_admin_has_not_enabled_self_service(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $wedding = Wedding::create($this->weddingPayload($tenant->id, 'tenant-builder-off'));

        $response = $this->actingAs($tenant)->put("/dashboard/my-weddings/{$wedding->id}/builder", [
            'builder' => [
                'content' => [
                    'custom_text' => [
                        'cover_button_label' => 'Tenant Edit',
                    ],
                ],
            ],
        ]);

        $response->assertForbidden();
    }

    public function test_tenant_can_update_only_fields_admin_unlocked(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $wedding = Wedding::create($this->weddingPayload($tenant->id, 'tenant-builder-on'));

        $wedding->update([
            'theme_config' => [
                'builder' => [
                    'permissions' => [
                        'tenant_builder_enabled' => true,
                        'custom_text' => true,
                        'music' => true,
                        'custom_font' => false,
                        'custom_decorations' => false,
                        'character_image' => false,
                        'decoration_animation' => false,
                        'palette' => false,
                        'block_builder' => false,
                        'block_visibility' => false,
                        'block_order' => false,
                    ],
                    'content' => [
                        'font_family' => 'font-sans',
                        'music_url' => null,
                        'custom_text' => [
                            'cover_intro' => null,
                            'cover_button_label' => 'Buka Undangan',
                            'closing_note' => null,
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->actingAs($tenant)->put("/dashboard/my-weddings/{$wedding->id}/builder", [
            'builder' => [
                'permissions' => [
                    'tenant_builder_enabled' => false,
                    'custom_font' => true,
                ],
                'content' => [
                    'font_family' => 'font-serif',
                    'music_url' => 'https://example.com/music.mp3',
                    'custom_text' => [
                        'cover_intro' => 'Tenant intro',
                        'cover_button_label' => 'Masuk Yuk',
                        'closing_note' => 'Closing from tenant',
                    ],
                ],
            ],
        ]);

        $response->assertRedirect("/dashboard/weddings/{$wedding->id}/builder");
    }

    public function test_tenant_allowed_update_persists_only_unlocked_values(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $wedding = Wedding::create($this->weddingPayload($tenant->id, 'tenant-builder-save'));

        $wedding->update([
            'theme_config' => [
                'builder' => [
                    'permissions' => [
                        'tenant_builder_enabled' => true,
                        'custom_text' => true,
                        'music' => true,
                        'custom_font' => false,
                        'custom_decorations' => false,
                        'character_image' => false,
                        'decoration_animation' => false,
                        'palette' => false,
                        'block_builder' => false,
                        'block_visibility' => false,
                        'block_order' => false,
                    ],
                ],
            ],
        ]);

        $this->actingAs($tenant)->put("/dashboard/my-weddings/{$wedding->id}/builder", [
            'builder' => [
                'permissions' => [
                    'tenant_builder_enabled' => false,
                ],
                'content' => [
                    'font_family' => 'font-serif',
                    'music_url' => 'https://example.com/music.mp3',
                    'custom_text' => [
                        'cover_intro' => 'Tenant intro',
                        'cover_button_label' => 'Masuk Yuk',
                        'closing_note' => 'Closing from tenant',
                    ],
                ],
            ],
        ])->assertRedirect("/dashboard/weddings/{$wedding->id}/builder");

        $wedding->refresh();

        $this->assertTrue(data_get($wedding->theme_config, 'builder.permissions.tenant_builder_enabled'));
        $this->assertSame('font-sans', data_get($wedding->theme_config, 'builder.content.font_family'));
        $this->assertSame('https://example.com/music.mp3', data_get($wedding->theme_config, 'builder.content.music_url'));
        $this->assertSame('Masuk Yuk', data_get($wedding->theme_config, 'builder.content.custom_text.cover_button_label'));
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
