<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CoupleProfile;
use App\Models\Wedding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'couple_photo_frame' => ['nullable', 'string', 'in:circle,portrait,rounded_square,arch'],
            'profiles' => ['required', 'array', 'min:1', 'max:2'],
            'profiles.*.id' => ['nullable', 'integer'],
            'profiles.*.role' => ['required', 'string', 'in:groom,bride'],
            'profiles.*.full_name' => ['required', 'string', 'max:255'],
            'profiles.*.nickname' => ['nullable', 'string', 'max:100'],
            'profiles.*.father_name' => ['nullable', 'string', 'max:255'],
            'profiles.*.mother_name' => ['nullable', 'string', 'max:255'],
            'profiles.*.child_order_text' => ['nullable', 'string', 'max:100'],
            'profiles.*.photo_path' => ['nullable', 'string', 'max:500'],
            'profiles.*.photo_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'profiles.*.instagram_url' => ['nullable', 'url', 'max:500'],
            'profiles.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->filled('couple_photo_frame')) {
            $themeConfig = $wedding->theme_config ?? [];
            data_set($themeConfig, 'builder.content.couple_photo_frame', $validated['couple_photo_frame']);
            $wedding->update(['theme_config' => $themeConfig]);
        }

        foreach ($validated['profiles'] as $index => $profileData) {
            $profileId = $profileData['id'] ?? null;
            unset($profileData['id']);

            // Handle file upload if present
            if ($request->hasFile("profiles.{$index}.photo_file")) {
                $file = $request->file("profiles.{$index}.photo_file");
                $storedPath = $file->store("weddings/{$wedding->id}/couples", 'public');
                $profileData['photo_path'] = Storage::disk('public')->url($storedPath);
            } elseif (array_key_exists('photo_path', $profileData) && empty($profileData['photo_path'])) {
                $profileData['photo_path'] = null;
            }

            unset($profileData['photo_file']);

            if ($profileId) {
                $profile = CoupleProfile::where('wedding_id', $wedding->id)->find($profileId);
                if ($profile) {
                    // Clean up old stored local file if replaced or removed
                    if (
                        array_key_exists('photo_path', $profileData)
                        && $profile->photo_path
                        && $profile->photo_path !== $profileData['photo_path']
                    ) {
                        $this->deleteStoredPhotoIfLocal($profile->photo_path);
                    }
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
     * Delete stored file from public disk if it belongs to local storage.
     */
    private function deleteStoredPhotoIfLocal(?string $photoPath): void
    {
        if (! $photoPath) {
            return;
        }

        $parsedPath = parse_url($photoPath, PHP_URL_PATH) ?? $photoPath;
        if (str_contains($parsedPath, '/storage/')) {
            $relative = ltrim(strstr($parsedPath, '/storage/'), '/storage/');
            if ($relative && Storage::disk('public')->exists($relative)) {
                Storage::disk('public')->delete($relative);
            }
        } elseif (Storage::disk('public')->exists($photoPath)) {
            Storage::disk('public')->delete($photoPath);
        }
    }

    /**
     * Ensure the wedding belongs to the authenticated user.
     */
    private function authorizeWedding(Request $request, Wedding $wedding): void
    {
        $this->authorize('view', $wedding);
    }
}
