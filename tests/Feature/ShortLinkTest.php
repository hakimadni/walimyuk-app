<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShortLinkTest extends TestCase
{
    use RefreshDatabase;

    private function createWedding(): Wedding
    {
        $user = User::factory()->create(['role' => 'tenant']);

        return Wedding::create([
            'user_id' => $user->id,
            'slug' => 'ali-fatimah',
            'status' => 'published',
            'wedding_date' => '2026-12-20 08:00:00',
            'timezone' => 'Asia/Jakarta',
            'cover_title' => 'The Wedding of Ali & Fatimah',
            'pax_buffer_percentage' => 10,
            'rsvp_required' => true,
        ]);
    }

    public function test_guest_automatically_gets_unique_short_code_and_token_on_creation(): void
    {
        $wedding = $this->createWedding();

        $guest = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Budi Santoso',
            'max_pax' => 2,
        ]);

        $this->assertNotEmpty($guest->token);
        $this->assertEquals(64, strlen($guest->token));
        $this->assertNotEmpty($guest->short_code);
        $this->assertEquals(6, strlen($guest->short_code));
        $this->assertEquals(strtolower($guest->short_code), $guest->short_code);
    }

    public function test_short_link_redirects_to_public_invitation_with_token(): void
    {
        $wedding = $this->createWedding();

        $guest = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Budi Santoso',
            'max_pax' => 2,
        ]);

        $response = $this->get("/s/{$guest->short_code}");

        $response->assertStatus(302);
        $response->assertRedirect(route('public.invitation', [
            'wedding' => $wedding->slug,
            'token' => $guest->token,
        ]));
    }

    public function test_short_link_is_case_insensitive(): void
    {
        $wedding = $this->createWedding();

        $guest = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Siti Nurhaliza',
            'max_pax' => 1,
        ]);

        $uppercaseCode = strtoupper($guest->short_code);
        $response = $this->get("/s/{$uppercaseCode}");

        $response->assertStatus(302);
        $response->assertRedirect(route('public.invitation', [
            'wedding' => $wedding->slug,
            'token' => $guest->token,
        ]));
    }

    public function test_invalid_short_link_returns_404(): void
    {
        $response = $this->get('/s/nonexistent');

        $response->assertStatus(404);
    }

    public function test_soft_deleted_guest_returns_404(): void
    {
        $wedding = $this->createWedding();

        $guest = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Tamu Terhapus',
            'max_pax' => 1,
        ]);

        $shortCode = $guest->short_code;
        $guest->delete();

        $response = $this->get("/s/{$shortCode}");

        $response->assertStatus(404);
    }

    public function test_public_invitation_renders_proper_wedding_title_and_not_laravel(): void
    {
        $wedding = $this->createWedding();

        $guest = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Budi Santoso',
            'max_pax' => 2,
        ]);

        $response = $this->get("/w/{$wedding->slug}?token={$guest->token}");
        $response->assertOk();

        // The HTML title should not be '- Laravel'
        $response->assertDontSee('<title> - Laravel</title>', false);
        $response->assertDontSee('<title> - WalimYuk</title>', false);
    }
}

