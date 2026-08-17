<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WeddingVerseController extends Controller
{
    /**
     * Display the wedding verse.
     */
    public function show(Request $request, Wedding $wedding): Response
    {
        $this->authorizeWedding($request, $wedding);

        $verse = $wedding->weddingVerses()->first();

        return Inertia::render('Admin/WeddingVerses/Show', [
            'wedding' => $wedding,
            'verse' => $verse,
        ]);
    }

    /**
     * Update the wedding verse. Creates one if it doesn't exist.
     */
    public function update(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);

        $validated = $request->validate([
            'source_label' => ['nullable', 'string', 'max:255'],
            'arabic_text' => ['nullable', 'string'],
            'transliteration' => ['nullable', 'string'],
            'translation' => ['nullable', 'string'],
        ]);

        $wedding->weddingVerses()->updateOrCreate(
            ['wedding_id' => $wedding->id],
            $validated,
        );

        return redirect()->route('dashboard.weddings.wedding-verses.show', $wedding)
            ->with('success', 'Ayat berhasil diperbarui.');
    }

    /**
     * Ensure the wedding belongs to the authenticated user.
     */
    private function authorizeWedding(Request $request, Wedding $wedding): void
    {
        $this->authorize('view', $wedding);
    }
}
