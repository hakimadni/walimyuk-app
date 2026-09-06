<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGuestRequest;
use App\Models\Guest;
use App\Models\Wedding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GuestController extends Controller
{
    /**
     * Display a listing of guests for the wedding.
     */
    public function index(Request $request, Wedding $wedding): Response
    {
        $this->authorizeWedding($request, $wedding);

        $guests = $wedding->guests()
            ->with('rsvp')
            ->orderBy('name')
            ->paginate($request->input('per_page', 25));

        return Inertia::render('Admin/Guests/Index', [
            'wedding' => $wedding,
            'guests' => $guests,
        ]);
    }

    /**
     * Show the form for creating a new guest.
     */
    public function create(Request $request, Wedding $wedding): Response
    {
        $this->authorizeWedding($request, $wedding);

        return Inertia::render('Admin/Guests/Create', [
            'wedding' => $wedding,
        ]);
    }

    /**
     * Store a newly created guest.
     */
    public function store(CreateGuestRequest $request, Wedding $wedding): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);

        $validated = $request->validated();

        $wedding->guests()->create([
            ...$validated,
            'token' => Str::random(64),
            'slug' => Str::slug($validated['name']) . '-' . Str::random(6),
        ]);

        return redirect()->route('dashboard.weddings.guests.index', $wedding)
            ->with('success', 'Tamu berhasil ditambahkan.');
    }

    /**
     * Display the specified guest.
     */
    public function show(Request $request, Wedding $wedding, Guest $guest): Response
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeGuest($wedding, $guest);

        $guest->load('rsvp', 'wishes');

        return Inertia::render('Admin/Guests/Show', [
            'wedding' => $wedding,
            'guest' => $guest,
        ]);
    }

    /**
     * Show the form for editing the specified guest.
     */
    public function edit(Request $request, Wedding $wedding, Guest $guest): Response
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeGuest($wedding, $guest);

        return Inertia::render('Admin/Guests/Edit', [
            'wedding' => $wedding,
            'guest' => $guest,
        ]);
    }

    /**
     * Update the specified guest.
     */
    public function update(Request $request, Wedding $wedding, Guest $guest): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeGuest($wedding, $guest);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'group_name' => ['nullable', 'string', 'max:100'],
            'max_pax' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $guest->update($validated);

        return redirect()->route('dashboard.weddings.guests.index', $wedding)
            ->with('success', 'Tamu berhasil diperbarui.');
    }

    /**
     * Remove the specified guest.
     */
    public function destroy(Request $request, Wedding $wedding, Guest $guest): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeGuest($wedding, $guest);

        $guest->delete();

        return redirect()->route('dashboard.weddings.guests.index', $wedding)
            ->with('success', 'Tamu berhasil dihapus.');
    }

    /**
     * Mark a guest's invitation as sent.
     */
    public function markSent(Request $request, Wedding $wedding, Guest $guest): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeGuest($wedding, $guest);

        $guest->update([
            'is_invitation_sent' => true,
            'sent_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Undangan ditandai sudah dikirim.');
    }

    /**
     * Import guests from a CSV/JSON file.
     */
    public function import(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,json', 'max:2048'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $importedCount = 0;

        if ($extension === 'json') {
            $content = file_get_contents($file->getRealPath());
            $rows = json_decode($content, true) ?: [];

            foreach ($rows as $row) {
                if (empty($row['name'])) continue;

                $wedding->guests()->create([
                    'name' => (string) $row['name'],
                    'phone_number' => isset($row['phone_number']) ? (string) $row['phone_number'] : null,
                    'group_name' => isset($row['group_name']) ? (string) $row['group_name'] : null,
                    'max_pax' => isset($row['max_pax']) ? max(1, (int) $row['max_pax']) : 2,
                    'notes' => isset($row['notes']) ? (string) $row['notes'] : null,
                    'token' => Str::random(64),
                    'slug' => Str::slug($row['name']) . '-' . Str::random(6),
                ]);
                $importedCount++;
            }
        } else {
            // CSV parsing
            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                $header = fgetcsv($handle); // Skip header row
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if (empty($data[0])) continue;

                    $name = trim($data[0]);
                    $phone = !empty($data[1]) ? trim($data[1]) : null;
                    $group = !empty($data[2]) ? trim($data[2]) : null;
                    $maxPax = !empty($data[3]) && is_numeric($data[3]) ? max(1, (int) $data[3]) : 2;
                    $notes = !empty($data[4]) ? trim($data[4]) : null;

                    $wedding->guests()->create([
                        'name' => $name,
                        'phone_number' => $phone,
                        'group_name' => $group,
                        'max_pax' => $maxPax,
                        'notes' => $notes,
                        'token' => Str::random(64),
                        'slug' => Str::slug($name) . '-' . Str::random(6),
                    ]);
                    $importedCount++;
                }
                fclose($handle);
            }
        }

        return redirect()->route('dashboard.weddings.guests.index', $wedding)
            ->with('success', "Berhasil mengimpor {$importedCount} data tamu undangan.");
    }

    /**
     * Export guests to CSV.
     */
    public function export(Request $request, Wedding $wedding)
    {
        $this->authorizeWedding($request, $wedding);

        $guests = $wedding->guests()
            ->with('rsvp')
            ->orderBy('name')
            ->get();

        $filename = 'daftar-tamu-' . ($wedding->slug ?: 'wedding-' . $wedding->id) . '-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($guests, $wedding) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID',
                'Nama Tamu',
                'Nomor WhatsApp',
                'Kategori / Grup',
                'Maksimal Pax',
                'Status Kirim Undangan',
                'Status RSVP',
                'Jumlah Pax Hadir',
                'Tautan Personal Undangan',
                'Catatan Internal',
            ]);

            $baseUrl = url('/');

            foreach ($guests as $guest) {
                $statusRsvp = match ($guest->rsvp?->attendance_status) {
                    'attending' => 'Hadir',
                    'declined' => 'Tidak Hadir',
                    default => 'Belum Konfirmasi',
                };

                $personalLink = $guest->short_code
                    ? "{$baseUrl}/s/{$guest->short_code}"
                    : "{$baseUrl}/w/{$wedding->slug}?token={$guest->token}";

                fputcsv($file, [
                    $guest->id,
                    $guest->name,
                    $guest->phone_number ?? '-',
                    $guest->group_name ?? '-',
                    $guest->max_pax,
                    $guest->is_invitation_sent ? 'Terkirim' : 'Belum Dikirim',
                    $statusRsvp,
                    $guest->rsvp?->attendance_status === 'attending' ? ($guest->rsvp?->pax_count ?? 1) : 0,
                    $personalLink,
                    $guest->notes ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    /**
     * Ensure the wedding belongs to the authenticated user.
     */
    private function authorizeWedding(Request $request, Wedding $wedding): void
    {
        $this->authorize('view', $wedding);
    }

    /**
     * Ensure the guest belongs to the wedding.
     */
    private function authorizeGuest(Wedding $wedding, Guest $guest): void
    {
        abort_unless($guest->wedding_id === $wedding->id, 404, 'Tamu tidak ditemukan.');
    }
}
