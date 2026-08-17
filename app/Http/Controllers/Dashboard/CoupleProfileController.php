<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CoupleProfile;
use App\Models\Wedding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CoupleProfileController extends Controller
{
    /**
     * Display the couple profiles for the wedding.
     */
    public function show(Request $request, Wedding $wedding): Response
    {
        $this->authorizeWedding($request, $wedding);

        $coupleProfiles = $wedding->coupleProfiles()
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/CoupleProfiles/Show', [
            'wedding' => $wedding,
            'coupleProfiles' => $coupleProfiles,
        ]);
    }

    /**
     * Update the couple profiles for the wedding.
     * Accepts an array of profiles and upserts them.
     */
    public function update(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);

        $validated = $request->validate([
            'profiles' => ['required', 'array', 'min:1', 'max:2'],
            'profiles.*.id' => ['nullable', 'integer'],
            'profiles.*.role' => ['required', 'string', 'in:groom,bride'],
            'profiles.*.full_name' => ['required', 'string', 'max:255'],
            'profiles.*.nickname' => ['nullable', 'string', 'max:100'],
            'profiles.*.father_name' => ['nullable', 'string', 'max:255'],
            'profiles.*.mother_name' => ['nullable', 'string', 'max:255'],
            'profiles.*.child_order_text' => ['nullable', 'string', 'max:100'],
            'profiles.*.photo_path' => ['nullable', 'string', 'max:500'],
            'profiles.*.instagram_url' => ['nullable', 'url', 'max:500'],
            'profiles.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        foreach ($validated['profiles'] as $profileData) {
            $profileId = $profileData['id'] ?? null;
            unset($profileData['id']);

            if ($profileId) {
                $profile = CoupleProfile::where('wedding_id', $wedding->id)->find($profileId);
                if ($profile) {
                    $profile->update($profileData);
                    continue;
                }
            }

            // Create new profile
            $wedding->coupleProfiles()->create($profileData);
        }

        return redirect()->route('dashboard.weddings.couple-profiles.show', $wedding)
            ->with('success', 'Profil pasangan berhasil diperbarui.');
    }

    /**
     * Ensure the wedding belongs to the authenticated user.
     */
    private function authorizeWedding(Request $request, Wedding $wedding): void
    {
        $this->authorize('view', $wedding);
    }
}
