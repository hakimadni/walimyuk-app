<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestBulkActionsTest extends TestCase
{
    use RefreshDatabase;

    private function createWedding(User $user, string $slug = 'our-wedding'): Wedding
    {
        return Wedding::create([
            'user_id' => $user->id,
            'slug' => $slug,
            'cover_title' => 'The Wedding',
            'cover_subtitle' => 'Romeo & Juliet',
            'wedding_date' => now()->addMonth(),
            'status' => 'published',
        ]);
    }

    public function test_tenant_can_bulk_delete_own_guests(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $wedding = $this->createWedding($tenant);

        $guest1 = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Tamu A',
            'token' => 'token-a',
        ]);
        $guest2 = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Tamu B',
            'token' => 'token-b',
        ]);
        $guest3 = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Tamu C',
            'token' => 'token-c',
        ]);

        $response = $this->actingAs($tenant)
            ->post("/weddings/{$wedding->id}/guests/bulk-delete", [
                'guest_ids' => [$guest1->id, $guest2->id],
            ]);

        $response->assertSessionHas('success');
        $this->assertSoftDeleted('guests', ['id' => $guest1->id]);
        $this->assertSoftDeleted('guests', ['id' => $guest2->id]);
        $this->assertNotSoftDeleted('guests', ['id' => $guest3->id]);
    }

    public function test_tenant_cannot_bulk_delete_other_weddings_guests(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $otherTenant = User::factory()->create(['role' => 'tenant']);

        $ownWedding = $this->createWedding($tenant, 'own-wedding');
        $otherWedding = $this->createWedding($otherTenant, 'other-wedding');

        $otherGuest = Guest::create([
            'wedding_id' => $otherWedding->id,
            'name' => 'Tamu Milik Orang Lain',
            'token' => 'token-other',
        ]);

        // Attempting to delete other wedding's guest via own wedding route
        $response = $this->actingAs($tenant)
            ->post("/weddings/{$ownWedding->id}/guests/bulk-delete", [
                'guest_ids' => [$otherGuest->id],
            ]);

        // Should not delete the other wedding's guest
        $this->assertNotSoftDeleted('guests', ['id' => $otherGuest->id]);

        // Attempting to hit other wedding's route directly
        $forbiddenResponse = $this->actingAs($tenant)
            ->post("/weddings/{$otherWedding->id}/guests/bulk-delete", [
                'guest_ids' => [$otherGuest->id],
            ]);

        $forbiddenResponse->assertForbidden();
    }

    public function test_tenant_can_bulk_edit_guests(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $wedding = $this->createWedding($tenant);

        $guest1 = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Tamu 1',
            'group_name' => 'Lama',
            'max_pax' => 1,
            'token' => 'tok-1',
        ]);
        $guest2 = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Tamu 2',
            'group_name' => 'Lama',
            'max_pax' => 1,
            'token' => 'tok-2',
        ]);

        $response = $this->actingAs($tenant)
            ->post("/weddings/{$wedding->id}/guests/bulk-edit", [
                'guest_ids' => [$guest1->id, $guest2->id],
                'apply_group_name' => true,
                'group_name' => 'VIP Khusus',
                'apply_max_pax' => true,
                'max_pax' => 3,
                'apply_is_invitation_sent' => true,
                'is_invitation_sent' => true,
            ]);

        $response->assertSessionHas('success');

        $guest1->refresh();
        $guest2->refresh();

        $this->assertEquals('VIP Khusus', $guest1->group_name);
        $this->assertEquals(3, $guest1->max_pax);
        $this->assertTrue($guest1->is_invitation_sent);

        $this->assertEquals('VIP Khusus', $guest2->group_name);
        $this->assertEquals(3, $guest2->max_pax);
        $this->assertTrue($guest2->is_invitation_sent);
    }

    public function test_tenant_can_bulk_mark_sent(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $wedding = $this->createWedding($tenant);

        $guest1 = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Tamu Sent 1',
            'is_invitation_sent' => false,
            'token' => 'tok-s1',
        ]);
        $guest2 = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Tamu Sent 2',
            'is_invitation_sent' => false,
            'token' => 'tok-s2',
        ]);

        $response = $this->actingAs($tenant)
            ->post("/weddings/{$wedding->id}/guests/bulk-mark-sent", [
                'guest_ids' => [$guest1->id, $guest2->id],
            ]);

        $response->assertSessionHas('success');

        $this->assertTrue($guest1->fresh()->is_invitation_sent);
        $this->assertTrue($guest2->fresh()->is_invitation_sent);
    }

    public function test_guest_index_includes_stats_and_available_groups(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $wedding = $this->createWedding($tenant);

        Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Tamu Keluarga',
            'group_name' => 'Keluarga',
            'token' => 't-1',
        ]);
        Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Tamu Kantor',
            'group_name' => 'Kantor',
            'token' => 't-2',
        ]);

        $response = $this->actingAs($tenant)->get("/weddings/{$wedding->id}/guests");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Guests/Index')
            ->has('wedding')
            ->has('guests')
            ->has('availableGroups')
            ->has('stats')
            ->where('stats.total', 2)
        );
    }

    public function test_tenant_can_download_excel_template(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $wedding = $this->createWedding($tenant);

        $response = $this->actingAs($tenant)->get("/weddings/{$wedding->id}/guests/template");

        $response->assertOk();
        $this->assertStringContainsString('template-tamu-walimyuk.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_tenant_can_download_csv_template(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $wedding = $this->createWedding($tenant);

        $response = $this->actingAs($tenant)->get("/weddings/{$wedding->id}/guests/template?format=csv");

        $response->assertOk();
        $this->assertStringContainsString('template-tamu-walimyuk.csv', $response->headers->get('content-disposition'));
    }

    public function test_tenant_can_bulk_import_from_excel(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $wedding = $this->createWedding($tenant);

        // Generate a real XLSX file in memory
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Nama Tamu');
        $sheet->setCellValue('B1', 'Nomor WhatsApp');
        $sheet->setCellValue('C1', 'Kategori / Grup');
        $sheet->setCellValue('D1', 'Maks Pax');
        $sheet->setCellValue('E1', 'Catatan');

        $sheet->setCellValue('A2', 'Bpk. H. Rahmat');
        $sheet->setCellValue('B2', '08123456789');
        $sheet->setCellValue('C2', 'Keluarga');
        $sheet->setCellValue('D2', 3);
        $sheet->setCellValue('E2', 'VIP Depan');

        $sheet->setCellValue('A3', 'Siti Rahma');
        $sheet->setCellValue('B3', '08987654321');
        $sheet->setCellValue('C3', 'Sahabat');
        $sheet->setCellValue('D3', 2);
        $sheet->setCellValue('E3', 'Teman SMA');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_excel_') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempPath,
            'template-tamu.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($tenant)->post("/weddings/{$wedding->id}/guests/import", [
            'file' => $uploadedFile,
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('guests', [
            'wedding_id' => $wedding->id,
            'name' => 'Bpk. H. Rahmat',
            'phone_number' => '08123456789',
            'group_name' => 'Keluarga',
            'max_pax' => 3,
            'notes' => 'VIP Depan',
        ]);

        $this->assertDatabaseHas('guests', [
            'wedding_id' => $wedding->id,
            'name' => 'Siti Rahma',
            'phone_number' => '08987654321',
            'group_name' => 'Sahabat',
            'max_pax' => 2,
            'notes' => 'Teman SMA',
        ]);

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }

    public function test_tenant_can_bulk_edit_guest_sessions(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $wedding = $this->createWedding($tenant);

        $guest1 = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Tamu S1',
            'session_name' => 'Sesi Akad',
            'token' => 'tok-s1',
        ]);
        $guest2 = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Tamu S2',
            'session_name' => null,
            'token' => 'tok-s2',
        ]);

        $response = $this->actingAs($tenant)
            ->post("/weddings/{$wedding->id}/guests/bulk-edit", [
                'guest_ids' => [$guest1->id, $guest2->id],
                'apply_session_name' => true,
                'session_name' => 'Sesi Resepsi (11.00-13.00)',
            ]);

        $response->assertSessionHas('success');

        $this->assertEquals('Sesi Resepsi (11.00-13.00)', $guest1->fresh()->session_name);
        $this->assertEquals('Sesi Resepsi (11.00-13.00)', $guest2->fresh()->session_name);
    }

    public function test_tenant_can_bulk_import_from_excel_with_sessions(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $wedding = $this->createWedding($tenant);

        // Generate a 6-column XLSX matching user template
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Nama Tamu (Wajib)');
        $sheet->setCellValue('B1', 'Nomor WhatsApp');
        $sheet->setCellValue('C1', 'Kategori / Grup');
        $sheet->setCellValue('D1', 'Maks Pax');
        $sheet->setCellValue('E1', 'Sesi');
        $sheet->setCellValue('F1', 'Catatan');

        $sheet->setCellValue('A2', 'Keluarga');
        $sheet->setCellValue('B2', '');
        $sheet->setCellValue('C2', 'K. Inti (Hakim)');
        $sheet->setCellValue('D2', 6);
        $sheet->setCellValue('E2', 'Sesi Akad (08.00-10.00)');
        $sheet->setCellValue('F2', '');

        $sheet->setCellValue('A3', 'Nenek, Om amien n keluarga');
        $sheet->setCellValue('B3', '08123456789');
        $sheet->setCellValue('C3', 'Keluarga Nenek Klender (Hakim)');
        $sheet->setCellValue('D3', 5);
        $sheet->setCellValue('E3', 'Sesi Resepsi (11.00-13.00)');
        $sheet->setCellValue('F3', 'VIP');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_excel_sessions_') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempPath,
            'template-tamu-walimyuk(1).xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($tenant)->post("/weddings/{$wedding->id}/guests/import", [
            'file' => $uploadedFile,
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('guests', [
            'wedding_id' => $wedding->id,
            'name' => 'Keluarga',
            'group_name' => 'K. Inti (Hakim)',
            'max_pax' => 6,
            'session_name' => 'Sesi Akad (08.00-10.00)',
        ]);

        $this->assertDatabaseHas('guests', [
            'wedding_id' => $wedding->id,
            'name' => 'Nenek, Om amien n keluarga',
            'phone_number' => '08123456789',
            'group_name' => 'Keluarga Nenek Klender (Hakim)',
            'max_pax' => 5,
            'session_name' => 'Sesi Resepsi (11.00-13.00)',
            'notes' => 'VIP',
        ]);

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }
}
