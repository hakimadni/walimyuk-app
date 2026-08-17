<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\GiftAddress;
use App\Models\Wedding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GiftAddressController extends Controller
{
    /**
     * Display a listing of gift addresses for the wedding.
     */
    public function index(Request $request, Wedding $wedding): Response
    {
        $this->authorizeWedding($request, $wedding);

        $addresses = $wedding->giftAddresses()
            ->orderBy('created_at')
            ->get();

        return Inertia::render('Admin/GiftAddresses/Index', [
            'wedding' => $wedding,
            'addresses' => $addresses,
        ]);
    }

    /**
     * Show the form for creating a new gift address.
     */
    public function create(Request $request, Wedding $wedding): Response
    {
        $this->authorizeWedding($request, $wedding);

        return Inertia::render('Admin/GiftAddresses/Create', [
            'wedding' => $wedding,
        ]);
    }

    /**
     * Store a newly created gift address.
     */
    public function store(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);

        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $wedding->giftAddresses()->create([
            ...$validated,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('dashboard.weddings.gift-addresses.index', $wedding)
            ->with('success', 'Alamat pengiriman hadiah berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified gift address.
     */
    public function edit(Request $request, Wedding $wedding, GiftAddress $giftAddress): Response
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeAddress($wedding, $giftAddress);

        return Inertia::render('Admin/GiftAddresses/Edit', [
            'wedding' => $wedding,
            'address' => $giftAddress,
        ]);
    }

    /**
     * Update the specified gift address.
     */
    public function update(Request $request, Wedding $wedding, GiftAddress $giftAddress): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeAddress($wedding, $giftAddress);

        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $giftAddress->update($validated);

        return redirect()->route('dashboard.weddings.gift-addresses.index', $wedding)
            ->with('success', 'Alamat pengiriman hadiah berhasil diperbarui.');
    }

    /**
     * Remove the specified gift address.
     */
    public function destroy(Request $request, Wedding $wedding, GiftAddress $giftAddress): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeAddress($wedding, $giftAddress);

        $giftAddress->delete();

        return redirect()->route('dashboard.weddings.gift-addresses.index', $wedding)
            ->with('success', 'Alamat pengiriman hadiah berhasil dihapus.');
    }

    /**
     * Ensure the wedding belongs to the authenticated user.
     */
    private function authorizeWedding(Request $request, Wedding $wedding): void
    {
        $this->authorize('view', $wedding);
    }

    /**
     * Ensure the address belongs to the wedding.
     */
    private function authorizeAddress(Wedding $wedding, GiftAddress $address): void
    {
        abort_unless($address->wedding_id === $wedding->id, 404, 'Alamat tidak ditemukan.');
    }
}
