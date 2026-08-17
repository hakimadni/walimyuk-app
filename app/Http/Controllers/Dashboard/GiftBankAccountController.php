<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\GiftBankAccount;
use App\Models\Wedding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GiftBankAccountController extends Controller
{
    /**
     * Display a listing of gift bank accounts for the wedding.
     */
    public function index(Request $request, Wedding $wedding): Response
    {
        $this->authorizeWedding($request, $wedding);

        $accounts = $wedding->giftBankAccounts()
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/GiftBankAccounts/Index', [
            'wedding' => $wedding,
            'accounts' => $accounts,
        ]);
    }

    /**
     * Show the form for creating a new gift bank account.
     */
    public function create(Request $request, Wedding $wedding): Response
    {
        $this->authorizeWedding($request, $wedding);

        return Inertia::render('Admin/GiftBankAccounts/Create', [
            'wedding' => $wedding,
        ]);
    }

    /**
     * Store a newly created gift bank account.
     */
    public function store(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);

        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $wedding->giftBankAccounts()->create([
            ...$validated,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('dashboard.weddings.gift-bank-accounts.index', $wedding)
            ->with('success', 'Rekening bank berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified gift bank account.
     */
    public function edit(Request $request, Wedding $wedding, GiftBankAccount $giftBankAccount): Response
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeAccount($wedding, $giftBankAccount);

        return Inertia::render('Admin/GiftBankAccounts/Edit', [
            'wedding' => $wedding,
            'account' => $giftBankAccount,
        ]);
    }

    /**
     * Update the specified gift bank account.
     */
    public function update(Request $request, Wedding $wedding, GiftBankAccount $giftBankAccount): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeAccount($wedding, $giftBankAccount);

        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $giftBankAccount->update($validated);

        return redirect()->route('dashboard.weddings.gift-bank-accounts.index', $wedding)
            ->with('success', 'Rekening bank berhasil diperbarui.');
    }

    /**
     * Remove the specified gift bank account.
     */
    public function destroy(Request $request, Wedding $wedding, GiftBankAccount $giftBankAccount): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeAccount($wedding, $giftBankAccount);

        $giftBankAccount->delete();

        return redirect()->route('dashboard.weddings.gift-bank-accounts.index', $wedding)
            ->with('success', 'Rekening bank berhasil dihapus.');
    }

    /**
     * Ensure the wedding belongs to the authenticated user.
     */
    private function authorizeWedding(Request $request, Wedding $wedding): void
    {
        $this->authorize('view', $wedding);
    }

    /**
     * Ensure the account belongs to the wedding.
     */
    private function authorizeAccount(Wedding $wedding, GiftBankAccount $account): void
    {
        abort_unless($account->wedding_id === $wedding->id, 404, 'Rekening tidak ditemukan.');
    }
}
