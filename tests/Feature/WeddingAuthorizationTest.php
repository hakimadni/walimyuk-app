<?php

namespace Tests\Feature;

use App\Models\CoupleProfile;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeddingAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_only_sees_own_weddings_in_index(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $otherTenant = User::factory()->create(['role' => 'tenant']);

        $ownWedding = Wedding::create($this->weddingPayload($tenant->id, 'own-wedding'));
        $otherWedding = Wedding::create($this->weddingPayload($otherTenant->id, 'other-wedding'));

        CoupleProfile::create($this->profilePayload($ownWedding->id, 'groom', 'Own Groom', 1));
        CoupleProfile::create($this->profilePayload($ownWedding->id, 'bride', 'Own Bride', 2));
        CoupleProfile::create($this->profilePayload($otherWedding->id, 'groom', 'Other Groom', 1));
        CoupleProfile::create($this->profilePayload($otherWedding->id, 'bride', 'Other Bride', 2));

        $response = $this->actingAs($tenant)->get('/dashboard/weddings');

        $response->assertOk();
        $response->assertSee('Own Groom');
        $response->assertDontSee('Other Groom');
    }

    public function test_tenant_cannot_edit_other_users_wedding(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $otherTenant = User::factory()->create(['role' => 'tenant']);

        $otherWedding = Wedding::create($this->weddingPayload($otherTenant->id, 'other-wedding'));

        $response = $this->actingAs($tenant)->get("/dashboard/weddings/{$otherWedding->id}/edit");

        $response->assertForbidden();
    }

    public function test_super_admin_sees_all_weddings_in_index(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $tenantA = User::factory()->create(['role' => 'tenant']);
        $tenantB = User::factory()->create(['role' => 'tenant']);

        $weddingA = Wedding::create($this->weddingPayload($tenantA->id, 'wedding-a'));
        $weddingB = Wedding::create($this->weddingPayload($tenantB->id, 'wedding-b'));

        CoupleProfile::create($this->profilePayload($weddingA->id, 'groom', 'Alpha Groom', 1));
        CoupleProfile::create($this->profilePayload($weddingA->id, 'bride', 'Alpha Bride', 2));
        CoupleProfile::create($this->profilePayload($weddingB->id, 'groom', 'Beta Groom', 1));
        CoupleProfile::create($this->profilePayload($weddingB->id, 'bride', 'Beta Bride', 2));

        $response = $this->actingAs($superAdmin)->get('/dashboard/weddings');

        $response->assertOk();
        $response->assertSee('Alpha Groom');
        $response->assertSee('Beta Groom');
    }

    public function test_super_admin_can_open_and_update_other_users_wedding(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $wedding = Wedding::create($this->weddingPayload($tenant->id, 'tenant-wedding'));
        CoupleProfile::create($this->profilePayload($wedding->id, 'groom', 'Tenant Groom', 1));
        CoupleProfile::create($this->profilePayload($wedding->id, 'bride', 'Tenant Bride', 2));

        $editResponse = $this->actingAs($superAdmin)->get("/dashboard/weddings/{$wedding->id}/edit");
        $editResponse->assertOk();

        $updateResponse = $this->actingAs($superAdmin)->put("/dashboard/weddings/{$wedding->id}", [
            'cover_title' => 'Updated By Superadmin',
            'cover_subtitle' => 'Tenant Groom & Tenant Bride',
            'wedding_date' => '2026-12-31T09:00',
            'timezone' => 'Asia/Jakarta',
            'welcome_text' => 'Welcome',
            'closing_text' => 'Thanks',
            'rsvp_required' => true,
            'comments_need_approval' => false,
            'pax_buffer_percentage' => 15,
        ]);

        $updateResponse->assertRedirect("/dashboard/weddings/{$wedding->id}");

        $this->assertDatabaseHas('weddings', [
            'id' => $wedding->id,
            'cover_title' => 'Updated By Superadmin',
            'user_id' => $tenant->id,
        ]);
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

    private function profilePayload(int $weddingId, string $role, string $fullName, int $sortOrder): array
    {
        return [
            'wedding_id' => $weddingId,
            'role' => $role,
            'full_name' => $fullName,
            'father_name' => 'Father '.$fullName,
            'mother_name' => 'Mother '.$fullName,
            'child_order_text' => 'Child order',
            'sort_order' => $sortOrder,
        ];
    }
}
