<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Models\WeddingDocumentChecklist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentChecklistController extends Controller
{
    /**
     * Display the wedding document checklist.
     */
    public function index(Request $request, Wedding $wedding): Response
    {
        $this->authorizeWedding($wedding);

        // Auto-seed default requirements if none exist yet
        if ($wedding->documentChecklists()->count() === 0) {
            $this->seedDefaultChecklists($wedding);
        }

        $checklists = $wedding->documentChecklists()->get();

        $config = data_get($wedding->theme_config, 'checklist', [
            'scenario' => 'both', // 'both' | 'groom_only' | 'bride_only' | 'none'
            'kua_groom' => '',
            'kua_bride' => '',
            'kua_venue' => '',
        ]);

        $scenario = $config['scenario'] ?? 'both';

        $totalGroomEligible = 0;
        $totalBrideEligible = 0;
        $groomChecked = 0;
        $brideChecked = 0;

        foreach ($checklists as $item) {
            $isKuaRekomendasi = ($item->stage_key === 'kua_rekomendasi');

            // Groom eligibility
            $groomEligible = true;
            if ($isKuaRekomendasi && in_array($scenario, ['bride_only', 'none'])) {
                $groomEligible = false;
            }

            // Bride eligibility
            $brideEligible = true;
            if ($isKuaRekomendasi && in_array($scenario, ['groom_only', 'none'])) {
                $brideEligible = false;
            }

            if ($groomEligible) {
                $totalGroomEligible++;
                if ($item->is_groom_checked) {
                    $groomChecked++;
                }
            }

            if ($brideEligible) {
                $totalBrideEligible++;
                if ($item->is_bride_checked) {
                    $brideChecked++;
                }
            }
        }

        $totalPossibleChecks = $totalGroomEligible + $totalBrideEligible;
        $totalCompletedChecks = $groomChecked + $brideChecked;

        $stats = [
            'total_items' => $checklists->count(),
            'total_groom_eligible' => $totalGroomEligible,
            'total_bride_eligible' => $totalBrideEligible,
            'groom_checked' => $groomChecked,
            'bride_checked' => $brideChecked,
            'groom_percentage' => $totalGroomEligible > 0 ? (int) round(($groomChecked / $totalGroomEligible) * 100) : 100,
            'bride_percentage' => $totalBrideEligible > 0 ? (int) round(($brideChecked / $totalBrideEligible) * 100) : 100,
            'overall_percentage' => $totalPossibleChecks > 0 ? (int) round(($totalCompletedChecks / $totalPossibleChecks) * 100) : 100,
        ];

        return Inertia::render('Admin/DocumentChecklists/Index', [
            'wedding' => $wedding,
            'checklists' => $checklists,
            'config' => $config,
            'stats' => $stats,
        ]);
    }

    /**
     * Store a new custom document checklist item.
     */
    public function store(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->authorizeWedding($wedding);

        $validated = $request->validate([
            'stage_key' => ['required', 'string', 'max:50'],
            'stage_title' => ['required', 'string', 'max:255'],
            'document_name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $maxSort = $wedding->documentChecklists()
            ->where('stage_key', $validated['stage_key'])
            ->max('sort_order') ?? 0;

        $wedding->documentChecklists()->create([
            'stage_key' => $validated['stage_key'],
            'stage_title' => $validated['stage_title'],
            'document_name' => $validated['document_name'],
            'notes' => $validated['notes'] ?? null,
            'is_groom_checked' => false,
            'is_bride_checked' => false,
            'sort_order' => $maxSort + 1,
        ]);

        return redirect()->back()->with('success', 'Persyaratan dokumen berhasil ditambahkan.');
    }

    /**
     * Update an existing document checklist item (checkmarks or notes).
     */
    public function update(Request $request, Wedding $wedding, WeddingDocumentChecklist $documentChecklist): RedirectResponse
    {
        $this->authorizeWedding($wedding);
        abort_if($documentChecklist->wedding_id !== $wedding->id, 404);

        $validated = $request->validate([
            'is_groom_checked' => ['sometimes', 'boolean'],
            'is_bride_checked' => ['sometimes', 'boolean'],
            'document_name' => ['sometimes', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (array_key_exists('is_groom_checked', $validated)) {
            $validated['groom_checked_at'] = $validated['is_groom_checked'] ? now() : null;
        }

        if (array_key_exists('is_bride_checked', $validated)) {
            $validated['bride_checked_at'] = $validated['is_bride_checked'] ? now() : null;
        }

        $documentChecklist->update($validated);

        return redirect()->back()->with('success', 'Status ceklis berhasil diperbarui.');
    }

    /**
     * Delete a document checklist item.
     */
    public function destroy(Request $request, Wedding $wedding, WeddingDocumentChecklist $documentChecklist): RedirectResponse
    {
        $this->authorizeWedding($wedding);
        abort_if($documentChecklist->wedding_id !== $wedding->id, 404);

        $documentChecklist->delete();

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus dari ceklis.');
    }

    /**
     * Reset the document checklist to standard default requirements.
     */
    public function reset(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->authorizeWedding($wedding);

        $wedding->documentChecklists()->delete();
        $this->seedDefaultChecklists($wedding);

        return redirect()->back()->with('success', 'Ceklis dokumen berhasil di-reset ke template standar.');
    }

    /**
     * Update the Numpang Nikah / KUA recommendation scenario configuration.
     */
    public function updateConfig(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->authorizeWedding($wedding);

        $validated = $request->validate([
            'scenario' => ['required', 'string', 'in:both,groom_only,bride_only,none'],
            'kua_groom' => ['nullable', 'string', 'max:150'],
            'kua_bride' => ['nullable', 'string', 'max:150'],
            'kua_venue' => ['nullable', 'string', 'max:150'],
        ]);

        $themeConfig = $wedding->theme_config ?? [];
        data_set($themeConfig, 'checklist', $validated);
        $wedding->update(['theme_config' => $themeConfig]);

        return redirect()->back()->with('success', 'Alur pengurusan KUA berhasil disimpan.');
    }

    /**
     * Ensure the user is authorized to manage this wedding.
     */
    private function authorizeWedding(Wedding $wedding): void
    {
        $this->authorize('view', $wedding);
    }

    /**
     * Seed default standard checklist requirements.
     */
    private function seedDefaultChecklists(Wedding $wedding): void
    {
        $template = WeddingDocumentChecklist::defaultTemplate();
        $globalOrder = 1;

        foreach ($template as $stage) {
            foreach ($stage['documents'] as $docName) {
                $wedding->documentChecklists()->create([
                    'stage_key' => $stage['stage_key'],
                    'stage_title' => $stage['stage_title'],
                    'document_name' => $docName,
                    'notes' => null,
                    'is_groom_checked' => false,
                    'is_bride_checked' => false,
                    'sort_order' => $globalOrder++,
                ]);
            }
        }
    }
}
