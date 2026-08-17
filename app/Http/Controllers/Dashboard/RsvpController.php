<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Services\RsvpService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RsvpController extends Controller
{
    public function __construct(
        private readonly RsvpService $rsvpService,
    ) {}

    /**
     * Display a listing of RSVPs for the wedding.
     */
    public function index(Request $request, Wedding $wedding): Response
    {
        $this->authorizeWedding($request, $wedding);

        $query = $wedding->rsvps()
            ->with('guest')
            ->orderByDesc('submitted_at');

        if ($request->filled('status')) {
            $query->where('attendance_status', $request->input('status'));
        }

        $rsvps = $query->paginate($request->input('per_page', 25));
        $analytics = $this->rsvpService->calculateCateringEstimate($wedding);

        return Inertia::render('Admin/Rsvps/Index', [
            'wedding' => $wedding,
            'rsvps' => $rsvps,
            'analytics' => $analytics,
            'filters' => [
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    /**
     * Display RSVP analytics for the wedding.
     */
    public function analytics(Request $request, Wedding $wedding): Response
    {
        $this->authorizeWedding($request, $wedding);

        $analytics = $this->rsvpService->calculateCateringEstimate($wedding);

        return Inertia::render('Admin/Rsvps/Analytics', [
            'wedding' => $wedding,
            'analytics' => $analytics,
        ]);
    }

    /**
     * Export RSVPs to CSV format.
     */
    public function export(Request $request, Wedding $wedding)
    {
        $this->authorizeWedding($request, $wedding);

        $rsvps = $wedding->rsvps()
            ->with('guest')
            ->orderByDesc('submitted_at')
            ->get();

        $filename = 'rekap-rsvp-' . ($wedding->slug ?: 'wedding-' . $wedding->id) . '-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($rsvps) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID RSVP',
                'Nama Tamu',
                'No. Telepon / WhatsApp',
                'Grup / Kategori',
                'Status Kehadiran',
                'Jumlah Pax Hadir',
                'Batas Kuota Tamu',
                'Pesan / Catatan',
                'Waktu Konfirmasi',
            ]);

            foreach ($rsvps as $rsvp) {
                $guest = $rsvp->guest;
                $statusLabel = match ($rsvp->attendance_status) {
                    'attending' => 'Hadir',
                    'not_attending' => 'Tidak Hadir',
                    'maybe' => 'Ragu-ragu',
                    default => 'Pending',
                };

                fputcsv($file, [
                    $rsvp->id,
                    $rsvp->guest_name_confirmed ?: ($guest?->name ?? 'Tamu'),
                    $rsvp->phone_number ?: ($guest?->phone_number ?? '-'),
                    $guest?->group_name ?? '-',
                    $statusLabel,
                    $rsvp->attendance_status === 'attending' ? ($rsvp->pax_count ?? 1) : 0,
                    $guest?->max_pax ?? 1,
                    $rsvp->comment ?? '-',
                    $rsvp->submitted_at ? $rsvp->submitted_at->format('Y-m-d H:i:s') : '-',
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
}
