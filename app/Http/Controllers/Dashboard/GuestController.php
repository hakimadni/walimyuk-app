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

        $query = $wedding->guests()->with('rsvp');

        if ($request->filled('search')) {
            $q = $request->input('search');
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('phone_number', 'like', "%{$q}%")
                    ->orWhere('group_name', 'like', "%{$q}%")
                    ->orWhere('session_name', 'like', "%{$q}%")
                    ->orWhere('notes', 'like', "%{$q}%");
            });
        }

        if ($request->filled('group') && $request->input('group') !== 'all') {
            if ($request->input('group') === '_none_') {
                $query->where(function ($sub) {
                    $sub->whereNull('group_name')->orWhere('group_name', '');
                });
            } else {
                $query->where('group_name', $request->input('group'));
            }
        }

        if ($request->filled('session') && $request->input('session') !== 'all') {
            if ($request->input('session') === '_none_') {
                $query->where(function ($sub) {
                    $sub->whereNull('session_name')->orWhere('session_name', '');
                });
            } else {
                $query->where('session_name', $request->input('session'));
            }
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $status = $request->input('status');
            if ($status === 'attending') {
                $query->whereHas('rsvp', fn ($q) => $q->where('attendance_status', 'attending'));
            } elseif ($status === 'declined') {
                $query->whereHas('rsvp', fn ($q) => $q->where('attendance_status', 'declined'));
            } elseif ($status === 'pending') {
                $query->whereDoesntHave('rsvp');
            }
        }

        if ($request->filled('sent') && $request->input('sent') !== 'all') {
            if ($request->input('sent') === 'sent') {
                $query->where('is_invitation_sent', true);
            } elseif ($request->input('sent') === 'unsent') {
                $query->where('is_invitation_sent', false);
            }
        }

        if ($request->filled('physical') && $request->input('physical') !== 'all') {
            if ($request->input('physical') === 'physical') {
                $query->where('is_physical_invitation', true);
            } elseif ($request->input('physical') === 'digital') {
                $query->where('is_physical_invitation', false);
            }
        }

        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        if (in_array($sort, ['name', 'group_name', 'session_name', 'max_pax', 'is_physical_invitation', 'created_at'])) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        $perPage = $request->input('per_page', 'all');
        $guests = $perPage === 'all' ? $query->get() : $query->paginate((int) $perPage)->withQueryString();

        $availableGroups = $wedding->guests()
            ->whereNotNull('group_name')
            ->where('group_name', '!=', '')
            ->distinct()
            ->pluck('group_name')
            ->sort()
            ->values();

        $availableSessions = $wedding->guests()
            ->whereNotNull('session_name')
            ->where('session_name', '!=', '')
            ->distinct()
            ->pluck('session_name')
            ->sort()
            ->values();

        $stats = [
            'total' => $wedding->guests()->count(),
            'total_pax' => (int) $wedding->guests()->sum('max_pax'),
            'total_physical' => $wedding->guests()->where('is_physical_invitation', true)->count(),
            'sent' => $wedding->guests()->where('is_invitation_sent', true)->count(),
            'attending' => $wedding->guests()->whereHas('rsvp', fn ($q) => $q->where('attendance_status', 'attending'))->count(),
            'declined' => $wedding->guests()->whereHas('rsvp', fn ($q) => $q->where('attendance_status', 'declined'))->count(),
            'confirmed_pax' => (int) \App\Models\Rsvp::whereIn('guest_id', $wedding->guests()->select('id'))
                ->where('attendance_status', 'attending')
                ->sum('pax_count'),
        ];

        return Inertia::render('Admin/Guests/Index', [
            'wedding' => $wedding,
            'guests' => $guests,
            'availableGroups' => $availableGroups,
            'availableSessions' => $availableSessions,
            'stats' => $stats,
            'filters' => [
                'search' => $request->input('search', ''),
                'group' => $request->input('group', 'all'),
                'session' => $request->input('session', 'all'),
                'status' => $request->input('status', 'all'),
                'sent' => $request->input('sent', 'all'),
                'physical' => $request->input('physical', 'all'),
                'sort' => $sort,
                'direction' => $direction,
                'per_page' => $perPage,
            ],
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
    public function update(Request $request, Wedding $wedding, Guest $guest): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeGuest($wedding, $guest);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'group_name' => ['nullable', 'string', 'max:100'],
            'session_name' => ['nullable', 'string', 'max:150'],
            'max_pax' => ['required', 'integer', 'min:1'],
            'is_physical_invitation' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $guest->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data tamu berhasil diperbarui.',
                'guest' => $guest->fresh(),
            ]);
        }

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
    public function markSent(Request $request, Wedding $wedding, Guest $guest): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeGuest($wedding, $guest);

        $guest->update([
            'is_invitation_sent' => true,
            'sent_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Undangan ditandai sudah dikirim.',
            ]);
        }

        return redirect()->back()
            ->with('success', 'Undangan ditandai sudah dikirim.');
    }

    /**
     * Update only the guest's session name.
     */
    public function updateSession(Request $request, Wedding $wedding, Guest $guest): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeGuest($wedding, $guest);

        $validated = $request->validate([
            'session_name' => ['nullable', 'string', 'max:150'],
        ]);

        $sessionName = !empty($validated['session_name']) ? trim($validated['session_name']) : null;
        $guest->update([
            'session_name' => $sessionName,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Sesi untuk {$guest->name} berhasil diperbarui.",
                'session_name' => $sessionName,
            ]);
        }

        return redirect()->back()
            ->with('success', "Sesi untuk {$guest->name} berhasil diperbarui.");
    }

    /**
     * Toggle or update only the guest's physical invitation flag.
     */
    public function updatePhysical(Request $request, Wedding $wedding, Guest $guest): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeGuest($wedding, $guest);

        $isPhysical = $request->has('is_physical_invitation')
            ? $request->boolean('is_physical_invitation')
            : !$guest->is_physical_invitation;

        $guest->update([
            'is_physical_invitation' => $isPhysical,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_physical_invitation' => $isPhysical,
                'message' => $isPhysical
                    ? "Undangan fisik untuk {$guest->name} diaktifkan."
                    : "Undangan fisik untuk {$guest->name} dinonaktifkan.",
            ]);
        }

        return redirect()->back()
            ->with('success', 'Status undangan fisik berhasil diperbarui.');
    }

    /**
     * Bulk delete selected guests.
     */
    public function bulkDelete(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);

        $validated = $request->validate([
            'guest_ids' => ['required', 'array', 'min:1'],
            'guest_ids.*' => ['required', 'integer'],
        ]);

        $deletedCount = $wedding->guests()
            ->whereIn('id', $validated['guest_ids'])
            ->delete();

        return redirect()->back()
            ->with('success', "{$deletedCount} data tamu berhasil dihapus.");
    }

    /**
     * Bulk edit selected guests.
     */
    public function bulkEdit(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);

        $validated = $request->validate([
            'guest_ids' => ['required', 'array', 'min:1'],
            'guest_ids.*' => ['required', 'integer'],
            'apply_group_name' => ['nullable', 'boolean'],
            'group_name' => ['nullable', 'string', 'max:100'],
            'apply_session_name' => ['nullable', 'boolean'],
            'session_name' => ['nullable', 'string', 'max:150'],
            'apply_max_pax' => ['nullable', 'boolean'],
            'max_pax' => ['nullable', 'integer', 'min:1', 'max:50'],
            'apply_is_invitation_sent' => ['nullable', 'boolean'],
            'is_invitation_sent' => ['nullable', 'boolean'],
            'apply_is_physical_invitation' => ['nullable', 'boolean'],
            'is_physical_invitation' => ['nullable', 'boolean'],
            'apply_notes' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $updates = [];
        if (!empty($validated['apply_group_name'])) {
            $updates['group_name'] = !empty($validated['group_name']) ? trim($validated['group_name']) : null;
        }
        if (!empty($validated['apply_session_name'])) {
            $updates['session_name'] = !empty($validated['session_name']) ? trim($validated['session_name']) : null;
        }
        if (!empty($validated['apply_max_pax'])) {
            $updates['max_pax'] = (int) $validated['max_pax'];
        }
        if (!empty($validated['apply_is_invitation_sent'])) {
            $isSent = (bool) ($validated['is_invitation_sent'] ?? false);
            $updates['is_invitation_sent'] = $isSent;
            $updates['sent_at'] = $isSent ? now() : null;
        }
        if (!empty($validated['apply_is_physical_invitation'])) {
            $updates['is_physical_invitation'] = (bool) ($validated['is_physical_invitation'] ?? false);
        }
        if (!empty($validated['apply_notes'])) {
            $updates['notes'] = !empty($validated['notes']) ? trim($validated['notes']) : null;
        }

        if (empty($updates)) {
            return redirect()->back()
                ->with('error', 'Tidak ada bidang yang dipilih untuk diperbarui.');
        }

        $updatedCount = $wedding->guests()
            ->whereIn('id', $validated['guest_ids'])
            ->update($updates);

        return redirect()->back()
            ->with('success', "{$updatedCount} data tamu berhasil diperbarui.");
    }

    /**
     * Bulk mark invitations as sent.
     */
    public function bulkMarkSent(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);

        $validated = $request->validate([
            'guest_ids' => ['required', 'array', 'min:1'],
            'guest_ids.*' => ['required', 'integer'],
        ]);

        $count = $wedding->guests()
            ->whereIn('id', $validated['guest_ids'])
            ->update([
                'is_invitation_sent' => true,
                'sent_at' => now(),
            ]);

        return redirect()->back()
            ->with('success', "{$count} undangan berhasil ditandai sudah dikirim.");
    }

    /**
     * Import guests from an Excel (XLSX/XLS), CSV, or JSON file.
     */
    public function import(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);

        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, ['xlsx', 'xls', 'csv', 'txt', 'json'])) {
            return redirect()->back()
                ->with('error', 'Format file tidak didukung. Harap unggah file Excel (.xlsx, .xls), CSV, atau JSON.');
        }

        $importedCount = 0;

        if (in_array($extension, ['xlsx', 'xls'])) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $highestRow = $sheet->getHighestDataRow();

                // Detect headers from row 1
                $colHeaders = [];
                foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'] as $colLetter) {
                    $val = strtolower(trim((string) $sheet->getCell("{$colLetter}1")->getCalculatedValue()));
                    if ($val !== '') {
                        $colHeaders[$colLetter] = $val;
                    }
                }

                $sesiCol = null;
                $physicalCol = null;
                $notesCol = null;

                foreach ($colHeaders as $col => $headerName) {
                    if (str_contains($headerName, 'sesi')) {
                        $sesiCol = $col;
                    } elseif (str_contains($headerName, 'fisik') || str_contains($headerName, 'physical')) {
                        $physicalCol = $col;
                    } elseif (str_contains($headerName, 'catatan') || str_contains($headerName, 'notes') || str_contains($headerName, 'note')) {
                        $notesCol = $col;
                    }
                }

                // Fallbacks if not detected by header name
                if ($sesiCol === null && count($colHeaders) >= 6) {
                    $sesiCol = 'E';
                }
                if ($physicalCol === null && count($colHeaders) >= 7 && $sesiCol === 'E') {
                    $physicalCol = 'F';
                }
                if ($notesCol === null) {
                    $notesCol = $physicalCol !== null ? 'G' : ($sesiCol !== null ? 'F' : 'E');
                }

                for ($row = 2; $row <= $highestRow; $row++) {
                    $name = trim((string) $sheet->getCell("A{$row}")->getCalculatedValue());
                    if (empty($name)) {
                        continue;
                    }

                    $phone = trim((string) $sheet->getCell("B{$row}")->getCalculatedValue());
                    $phone = $phone !== '' ? $phone : null;

                    $group = trim((string) $sheet->getCell("C{$row}")->getCalculatedValue());
                    $group = $group !== '' ? $group : null;

                    $maxPaxRaw = $sheet->getCell("D{$row}")->getCalculatedValue();
                    $maxPax = is_numeric($maxPaxRaw) ? max(1, (int) $maxPaxRaw) : 2;

                    $session = null;
                    if ($sesiCol !== null) {
                        $sVal = trim((string) $sheet->getCell("{$sesiCol}{$row}")->getCalculatedValue());
                        $session = $sVal !== '' ? $sVal : null;
                    }

                    $isPhysical = false;
                    if ($physicalCol !== null) {
                        $pVal = $sheet->getCell("{$physicalCol}{$row}")->getCalculatedValue();
                        $isPhysical = $this->parsePhysicalInvitationValue($pVal);
                    }

                    $notes = null;
                    if ($notesCol !== null) {
                        $nVal = trim((string) $sheet->getCell("{$notesCol}{$row}")->getCalculatedValue());
                        $notes = $nVal !== '' ? $nVal : null;
                    }

                    $wedding->guests()->create([
                        'name' => $name,
                        'phone_number' => $phone,
                        'group_name' => $group,
                        'session_name' => $session,
                        'max_pax' => $maxPax,
                        'is_physical_invitation' => $isPhysical,
                        'notes' => $notes,
                        'token' => Str::random(64),
                        'slug' => Str::slug($name) . '-' . Str::random(6),
                    ]);
                    $importedCount++;
                }
            } catch (\Throwable $e) {
                return redirect()->back()
                    ->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
            }
        } elseif ($extension === 'json') {
            $content = file_get_contents($file->getRealPath());
            $rows = json_decode($content, true) ?: [];

            foreach ($rows as $row) {
                if (empty($row['name'])) continue;

                $wedding->guests()->create([
                    'name' => (string) $row['name'],
                    'phone_number' => isset($row['phone_number']) ? (string) $row['phone_number'] : null,
                    'group_name' => isset($row['group_name']) ? (string) $row['group_name'] : null,
                    'session_name' => isset($row['session_name']) ? (string) $row['session_name'] : (isset($row['sesi']) ? (string) $row['sesi'] : null),
                    'max_pax' => isset($row['max_pax']) ? max(1, (int) $row['max_pax']) : 2,
                    'is_physical_invitation' => isset($row['is_physical_invitation']) ? (bool) $row['is_physical_invitation'] : (isset($row['undangan_fisik']) ? $this->parsePhysicalInvitationValue($row['undangan_fisik']) : false),
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
                $headerLower = array_map(fn($h) => strtolower(trim((string) $h)), $header ?: []);
                $sesiIdx = null;
                $physicalIdx = null;
                $notesIdx = null;

                foreach ($headerLower as $idx => $hName) {
                    if (str_contains($hName, 'sesi')) {
                        $sesiIdx = $idx;
                    } elseif (str_contains($hName, 'fisik') || str_contains($hName, 'physical')) {
                        $physicalIdx = $idx;
                    } elseif (str_contains($hName, 'catatan') || str_contains($hName, 'notes') || str_contains($hName, 'note')) {
                        $notesIdx = $idx;
                    }
                }

                // Fallbacks if not detected
                if ($sesiIdx === null && count($headerLower) >= 6) {
                    $sesiIdx = 4;
                }
                if ($physicalIdx === null && count($headerLower) >= 7 && $sesiIdx === 4) {
                    $physicalIdx = 5;
                }
                if ($notesIdx === null) {
                    $notesIdx = $physicalIdx !== null ? 6 : ($sesiIdx !== null ? 5 : 4);
                }

                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if (empty($data[0])) continue;

                    $name = trim($data[0]);
                    $phone = !empty($data[1]) ? trim($data[1]) : null;
                    $group = !empty($data[2]) ? trim($data[2]) : null;
                    $maxPax = !empty($data[3]) && is_numeric($data[3]) ? max(1, (int) $data[3]) : 2;

                    $session = ($sesiIdx !== null && !empty($data[$sesiIdx])) ? trim($data[$sesiIdx]) : null;
                    $isPhysical = ($physicalIdx !== null && isset($data[$physicalIdx])) ? $this->parsePhysicalInvitationValue($data[$physicalIdx]) : false;
                    $notes = ($notesIdx !== null && !empty($data[$notesIdx])) ? trim($data[$notesIdx]) : null;

                    $wedding->guests()->create([
                        'name' => $name,
                        'phone_number' => $phone,
                        'group_name' => $group,
                        'session_name' => $session,
                        'max_pax' => $maxPax,
                        'is_physical_invitation' => $isPhysical,
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
     * Download Excel / CSV template for bulk importing guests.
     */
    public function template(Request $request, Wedding $wedding)
    {
        $this->authorizeWedding($request, $wedding);

        if ($request->query('format') === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="template-tamu-walimyuk.csv"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function () {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($file, ['Nama Tamu (Wajib)', 'Nomor WhatsApp', 'Kategori / Grup', 'Maks Pax', 'Sesi', 'Undangan Fisik', 'Catatan']);
                fputcsv($file, ['Keluarga', '', 'K. Inti (Hakim)', 6, 'Sesi Akad (08.00-10.00)', 'Ya', '']);
                fputcsv($file, ['Nenek, Om amien n keluarga', '', 'Keluarga Nenek Klender (Hakim)', 5, 'Sesi Akad (08.00-10.00)', 'Ya', 'VIP']);
                fputcsv($file, ['Om adhi n keluarga', '', 'Keluarga Nenek Klender (Hakim)', 4, 'Sesi Akad (08.00-10.00)', 'Tidak', '']);
                fclose($file);
            };

            return response()->streamDownload($callback, 'template-tamu-walimyuk.csv', $headers);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Tamu');

        // Headers: 7 columns
        $headers = [
            'A1' => 'Nama Tamu (Wajib)',
            'B1' => 'Nomor WhatsApp',
            'C1' => 'Kategori / Grup',
            'D1' => 'Maks Pax',
            'E1' => 'Sesi',
            'F1' => 'Undangan Fisik',
            'G1' => 'Catatan',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        // Style Header
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF047857'], // Emerald 700
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCBD5E1'],
                ],
            ],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Sample data from user specification
        $samples = [
            ['Keluarga', '', 'K. Inti (Hakim)', 6, 'Sesi Akad (08.00-10.00)', 'Ya', ''],
            ['Nenek, Om amien n keluarga', '08123456789', 'Keluarga Nenek Klender (Hakim)', 5, 'Sesi Akad (08.00-10.00)', 'Ya', 'VIP'],
            ['Om adhi n keluarga', '', 'Keluarga Nenek Klender (Hakim)', 4, 'Sesi Akad (08.00-10.00)', 'Tidak', ''],
        ];

        $rowIdx = 2;
        foreach ($samples as $sample) {
            $sheet->setCellValueExplicit("A{$rowIdx}", $sample[0], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("B{$rowIdx}", $sample[1], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$rowIdx}", $sample[2], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("D{$rowIdx}", $sample[3]);
            $sheet->setCellValueExplicit("E{$rowIdx}", $sample[4], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("F{$rowIdx}", $sample[5], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("G{$rowIdx}", $sample[6], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $sheet->getStyle("A{$rowIdx}:G{$rowIdx}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB('FFE2E8F0');
            $rowIdx++;
        }

        // Set phone number column explicit format to text so leading zeros are preserved
        $sheet->getStyle('B2:B1000')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // Auto-fit columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'template-tamu-walimyuk.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
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
                'Maks Pax',
                'Sesi Acara',
                'Undangan Fisik',
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
                    $guest->session_name ?? '-',
                    $guest->is_physical_invitation ? 'Ya' : 'Tidak',
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
     * Determine boolean value for physical invitation from import cell.
     */
    private function parsePhysicalInvitationValue(mixed $val): bool
    {
        if (is_bool($val)) {
            return $val;
        }
        $v = strtolower(trim((string) $val));

        return in_array($v, ['ya', 'y', 'yes', 'true', '1', 'fisik', 'v', '✓']);
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
