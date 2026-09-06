<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingDocumentChecklist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DocumentChecklistTest extends TestCase
{
    use RefreshDatabase;

    private function createWedding(int $userId, ?string $slug = null): Wedding
    {
        return Wedding::create([
            'user_id' => $userId,
            'title' => 'The Wedding of Farhan & Aisyah',
            'slug' => $slug ?? ('farhan-aisyah-doc-test-' . uniqid()),
            'cover_title' => 'Farhan & Aisyah',
            'status' => 'published',
            'wedding_date' => '2026-11-20',
        ]);
    }

    public function test_document_checklist_page_auto_seeds_default_requirements(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);
        $wedding = $this->createWedding($user->id);

        $this->assertEquals(0, WeddingDocumentChecklist::where('wedding_id', $wedding->id)->count());

        $response = $this->actingAs($user)->get("/weddings/{$wedding->id}/document-checklist");

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/DocumentChecklists/Index')
            ->has('checklists', 37)
            ->where('stats.total_items', 37)
            ->where('stats.groom_checked', 0)
            ->where('stats.bride_checked', 0)
            ->where('stats.overall_percentage', 0)
        );

        // Verify counts per stage in DB
        $this->assertEquals(2, WeddingDocumentChecklist::where('wedding_id', $wedding->id)->where('stage_key', 'rt_rw')->count());
        $this->assertEquals(3, WeddingDocumentChecklist::where('wedding_id', $wedding->id)->where('stage_key', 'puskesmas')->count());
        $this->assertEquals(8, WeddingDocumentChecklist::where('wedding_id', $wedding->id)->where('stage_key', 'kelurahan')->count());
        $this->assertEquals(12, WeddingDocumentChecklist::where('wedding_id', $wedding->id)->where('stage_key', 'kua_rekomendasi')->count());
        $this->assertEquals(12, WeddingDocumentChecklist::where('wedding_id', $wedding->id)->where('stage_key', 'kua_venue')->count());
    }

    public function test_user_can_toggle_groom_and_bride_checkmarks(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);
        $wedding = $this->createWedding($user->id);

        // Seed via visiting the page
        $this->actingAs($user)->get("/weddings/{$wedding->id}/document-checklist");

        $firstItem = WeddingDocumentChecklist::where('wedding_id', $wedding->id)->first();
        $this->assertFalse($firstItem->is_groom_checked);
        $this->assertFalse($firstItem->is_bride_checked);

        // Check Groom
        $response = $this->actingAs($user)->patch("/weddings/{$wedding->id}/document-checklist/{$firstItem->id}", [
            'is_groom_checked' => true,
        ]);
        $response->assertRedirect();

        $firstItem->refresh();
        $this->assertTrue($firstItem->is_groom_checked);
        $this->assertNotNull($firstItem->groom_checked_at);
        $this->assertFalse($firstItem->is_bride_checked);

        // Check Bride
        $response = $this->actingAs($user)->patch("/weddings/{$wedding->id}/document-checklist/{$firstItem->id}", [
            'is_bride_checked' => true,
        ]);
        $response->assertRedirect();

        $firstItem->refresh();
        $this->assertTrue($firstItem->is_groom_checked);
        $this->assertTrue($firstItem->is_bride_checked);
        $this->assertNotNull($firstItem->bride_checked_at);

        // Update notes
        $response = $this->actingAs($user)->patch("/weddings/{$wedding->id}/document-checklist/{$firstItem->id}", [
            'notes' => 'Fotocopy 3 rangkap siap',
        ]);
        $response->assertRedirect();

        $firstItem->refresh();
        $this->assertEquals('Fotocopy 3 rangkap siap', $firstItem->notes);
    }

    public function test_user_can_add_custom_document_and_delete(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);
        $wedding = $this->createWedding($user->id);

        // Add custom document
        $response = $this->actingAs($user)->post("/weddings/{$wedding->id}/document-checklist", [
            'stage_key' => 'rt_rw',
            'stage_title' => '1. RT/RW (Persyaratan yang Dibawa)',
            'document_name' => 'Surat Pengantar RT Tambahan',
            'notes' => 'Minta cap basah ketua RW',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $custom = WeddingDocumentChecklist::where('wedding_id', $wedding->id)
            ->where('document_name', 'Surat Pengantar RT Tambahan')
            ->first();

        $this->assertNotNull($custom);
        $this->assertEquals('Minta cap basah ketua RW', $custom->notes);

        // Delete custom document
        $delResponse = $this->actingAs($user)->delete("/weddings/{$wedding->id}/document-checklist/{$custom->id}");
        $delResponse->assertRedirect();

        $this->assertDatabaseMissing('wedding_document_checklists', [
            'id' => $custom->id,
        ]);
    }

    public function test_user_can_reset_checklist_to_default(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);
        $wedding = $this->createWedding($user->id);

        // Seed
        $this->actingAs($user)->get("/weddings/{$wedding->id}/document-checklist");

        // Mark all as groom checked
        WeddingDocumentChecklist::where('wedding_id', $wedding->id)->update(['is_groom_checked' => true]);
        $this->assertEquals(37, WeddingDocumentChecklist::where('wedding_id', $wedding->id)->where('is_groom_checked', true)->count());

        // Reset
        $response = $this->actingAs($user)->post("/weddings/{$wedding->id}/document-checklist/reset");
        $response->assertRedirect();

        $this->assertEquals(37, WeddingDocumentChecklist::where('wedding_id', $wedding->id)->count());
        $this->assertEquals(0, WeddingDocumentChecklist::where('wedding_id', $wedding->id)->where('is_groom_checked', true)->count());
    }

    public function test_tenant_cannot_access_other_users_document_checklist(): void
    {
        $userA = User::factory()->create(['role' => 'tenant']);
        $userB = User::factory()->create(['role' => 'tenant']);

        $weddingA = $this->createWedding($userA->id);
        $weddingB = $this->createWedding($userB->id);

        // Seed wedding B
        $this->actingAs($userB)->get("/weddings/{$weddingB->id}/document-checklist");
        $itemB = WeddingDocumentChecklist::where('wedding_id', $weddingB->id)->first();

        // User A tries to view wedding B checklist -> 403 Forbidden
        $response = $this->actingAs($userA)->get("/weddings/{$weddingB->id}/document-checklist");
        $response->assertForbidden();

        // User A tries to update item in wedding B -> 403 Forbidden
        $patchResponse = $this->actingAs($userA)->patch("/weddings/{$weddingB->id}/document-checklist/{$itemB->id}", [
            'is_groom_checked' => true,
        ]);
        $patchResponse->assertForbidden();
    }

    public function test_user_can_configure_kua_numpang_nikah_scenario(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);
        $wedding = $this->createWedding($user->id);

        $response = $this->actingAs($user)->post("/weddings/{$wedding->id}/document-checklist/config", [
            'scenario' => 'groom_only',
            'kua_groom' => 'KUA Kec. Pancoran',
            'kua_bride' => 'KUA Kec. Tebet',
            'kua_venue' => 'KUA Kec. Tebet',
        ]);

        $response->assertRedirect();
        $wedding->refresh();

        $this->assertEquals('groom_only', data_get($wedding->theme_config, 'checklist.scenario'));
        $this->assertEquals('KUA Kec. Pancoran', data_get($wedding->theme_config, 'checklist.kua_groom'));
        $this->assertEquals('KUA Kec. Tebet', data_get($wedding->theme_config, 'checklist.kua_bride'));
    }

    public function test_kua_scenario_affects_eligible_stats(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);
        $wedding = $this->createWedding($user->id);

        // 1. Initial default ('both'): 37 items, both need 37
        $response1 = $this->actingAs($user)->get("/weddings/{$wedding->id}/document-checklist");
        $response1->assertOk();
        $response1->assertInertia(fn (Assert $page) => $page
            ->where('stats.total_items', 37)
            ->where('stats.total_groom_eligible', 37)
            ->where('stats.total_bride_eligible', 37)
        );

        // 2. Set 'groom_only' (Menikah di KUA CPW Tebet -> CPW is exempt from stage 4 which has 12 items)
        $this->actingAs($user)->post("/weddings/{$wedding->id}/document-checklist/config", [
            'scenario' => 'groom_only',
        ]);

        $response2 = $this->actingAs($user)->get("/weddings/{$wedding->id}/document-checklist");
        $response2->assertOk();
        $response2->assertInertia(fn (Assert $page) => $page
            ->where('stats.total_items', 37)
            ->where('stats.total_groom_eligible', 37) // groom still needs 37
            ->where('stats.total_bride_eligible', 25) // bride only needs 37 - 12 = 25!
        );

        // 3. Set 'none' (Both from same KUA as Venue -> both exempt from stage 4)
        $this->actingAs($user)->post("/weddings/{$wedding->id}/document-checklist/config", [
            'scenario' => 'none',
        ]);

        $response3 = $this->actingAs($user)->get("/weddings/{$wedding->id}/document-checklist");
        $response3->assertOk();
        $response3->assertInertia(fn (Assert $page) => $page
            ->where('stats.total_items', 37)
            ->where('stats.total_groom_eligible', 25) // 37 - 12 = 25
            ->where('stats.total_bride_eligible', 25) // 37 - 12 = 25
        );
    }
}
