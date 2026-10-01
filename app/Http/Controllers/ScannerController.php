<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScannerController extends Controller
{
    public function index(Request $request, Wedding $wedding): Response
    {
        // Require owner or admin
        if ($wedding->user_id !== $request->user()->id && !in_array($request->user()->role, ['super_admin', 'admin'])) {
            abort(403);
        }

        return Inertia::render('Public/Scanner', [
            'wedding' => $wedding,
        ]);
    }

    public function checkIn(Request $request, Wedding $wedding)
    {
        if ($wedding->user_id !== $request->user()->id && !in_array($request->user()->role, ['super_admin', 'admin'])) {
            abort(403);
        }

        $validated = $request->validate([
            'qr_code_hash' => ['required', 'string']
        ]);

        $guest = $wedding->guests()->where('qr_code_hash', $validated['qr_code_hash'])->first();

        if (!$guest) {
            return response()->json(['error' => 'QR Code tidak valid atau bukan tamu undangan ini.'], 404);
        }

        if ($guest->is_attended) {
            return response()->json([
                'success' => false,
                'message' => 'Tamu ini sudah check-in sebelumnya.',
                'guest' => $guest
            ], 200);
        }

        $guest->update([
            'is_attended' => true,
            'attended_at' => now(),
        ]);

        $pax = $guest->rsvp ? $guest->rsvp->pax_count : 0;

        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil!',
            'guest' => [
                'name' => $guest->name,
                'group' => $guest->group_name,
                'pax_count' => $pax,
            ]
        ]);
    }
}
