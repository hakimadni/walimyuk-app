<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRsvpRequest;
use App\Models\Wedding;
use App\Models\Wish;
use App\Services\RsvpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class PublicInvitationController extends Controller
{
    public function __construct(
        private readonly RsvpService $rsvpService,
    ) {}

    /**
     * Display the personalized wedding invitation page.
     *
     * The ValidateGuestToken middleware resolves the guest from the query token
     * and attaches it to the request. If the wedding is not published and the
     * user is not the owner, the middleware aborts with 403.
     */
    public function show(Request $request, Wedding $wedding): Response
    {
        $guest = $request->attributes->get('guest');

        $wedding->load([
            'coupleProfiles' => fn ($q) => $q->orderBy('sort_order'),
            'weddingVerses',
            'events' => fn ($q) => $q->orderBy('sort_order')->orderBy('date'),
            'giftBankAccounts' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'giftAddresses' => fn ($q) => $q->where('is_active', true),
        ]);

        $existingRsvp = $guest->rsvp;
        $approvedWishes = $wedding->wishes()
            ->where('moderation_status', 'approved')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return Inertia::render('Public/InvitationShell', [
            'wedding' => $wedding,
            'coupleProfiles' => $wedding->coupleProfiles,
            'verse' => $wedding->weddingVerses->first(),
            'events' => $wedding->events,
            'giftBankAccounts' => $wedding->giftBankAccounts,
            'giftAddresses' => $wedding->giftAddresses,
            'guest' => $guest,
            'existingRsvp' => $existingRsvp,
            'approvedWishes' => $approvedWishes,
            'themeConfig' => $wedding->theme_config ?? [],
        ]);
    }

    /**
     * Submit or update an RSVP for the guest.
     *
     * Uses RsvpService::submitRsvp() to handle all business logic
     * (pax validation, upsert, wish creation from comment).
     */
    public function storeRsvp(StoreRsvpRequest $request, Wedding $wedding): RedirectResponse
    {
        $guest = $request->attributes->get('guest');

        try {
            $this->rsvpService->submitRsvp($guest, $request->validated());
        } catch (RuntimeException $e) {
            return redirect()->back()
                ->withErrors(['attendance_status' => $e->getMessage()]);
        }

        return redirect()->route('public.invitation', [
            'wedding' => $wedding->slug,
            'token' => $request->query('token'),
        ])->with('success', 'RSVP berhasil dikirim. Terima kasih!');
    }

    /**
     * Store a standalone wish (not tied to an RSVP).
     */
    public function storeWish(Request $request, Wedding $wedding): RedirectResponse
    {
        $guest = $request->attributes->get('guest');

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $moderationStatus = $wedding->comments_need_approval ? 'pending' : 'approved';

        Wish::create([
            'wedding_id' => $wedding->id,
            'guest_id' => $guest->id,
            'name' => $guest->name,
            'message' => trim($validated['message']),
            'moderation_status' => $moderationStatus,
        ]);

        $token = $request->query('token');

        return redirect()->route('public.invitation', [
            'wedding' => $wedding->slug,
            'token' => $token,
        ])->with('success', 'Ucapan berhasil dikirim!');
    }
}
