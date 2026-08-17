<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Models\Wish;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WishController extends Controller
{
    /**
     * Display a listing of wishes for the wedding.
     */
    public function index(Request $request, Wedding $wedding): Response
    {
        $this->authorizeWedding($request, $wedding);

        $query = $wedding->wishes()
            ->with('guest')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('moderation_status', $request->input('status'));
        }

        $wishes = $query->paginate($request->input('per_page', 25));

        $stats = [
            'total' => $wedding->wishes()->count(),
            'approved' => $wedding->wishes()->where('moderation_status', 'approved')->count(),
            'pending' => $wedding->wishes()->where('moderation_status', 'pending')->count(),
            'rejected' => $wedding->wishes()->where('moderation_status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/Wishes/Index', [
            'wedding' => $wedding,
            'wishes' => $wishes,
            'stats' => $stats,
            'filters' => [
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    /**
     * Approve a wish.
     */
    public function approve(Request $request, Wedding $wedding, Wish $wish): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeWish($wedding, $wish);

        $wish->update(['moderation_status' => 'approved']);

        return redirect()->back()
            ->with('success', 'Ucapan berhasil disetujui.');
    }

    /**
     * Reject a wish.
     */
    public function reject(Request $request, Wedding $wedding, Wish $wish): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeWish($wedding, $wish);

        $wish->update(['moderation_status' => 'rejected']);

        return redirect()->back()
            ->with('success', 'Ucapan berhasil ditolak.');
    }

    /**
     * Remove the specified wish.
     */
    public function destroy(Request $request, Wedding $wedding, Wish $wish): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeWish($wedding, $wish);

        $wish->delete();

        return redirect()->back()
            ->with('success', 'Ucapan berhasil dihapus.');
    }

    /**
     * Ensure the wedding belongs to the authenticated user.
     */
    private function authorizeWedding(Request $request, Wedding $wedding): void
    {
        $this->authorize('view', $wedding);
    }

    /**
     * Ensure the wish belongs to the wedding.
     */
    private function authorizeWish(Wedding $wedding, Wish $wish): void
    {
        abort_unless($wish->wedding_id === $wedding->id, 404, 'Ucapan tidak ditemukan.');
    }
}
