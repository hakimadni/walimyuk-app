<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BackgroundMusic;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class BackgroundMusicController extends Controller
{
    public function index(Request $request)
    {
        if (!in_array($request->user()->role, ['super_admin', 'admin'])) {
            abort(403);
        }

        return Inertia::render('Admin/BackgroundMusic/Index', [
            'musics' => BackgroundMusic::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        if (!in_array($request->user()->role, ['super_admin', 'admin'])) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:mp3,wav,ogg,m4a,aac|max:20480'
        ]);

        $path = $request->file('file')->store('library/music', 'public');

        BackgroundMusic::create([
            'title' => $validated['title'],
            'artist' => $validated['artist'],
            'file_path' => Storage::disk('public')->url($path),
        ]);

        return back()->with('success', 'Music uploaded successfully.');
    }

    public function destroy(Request $request, BackgroundMusic $backgroundMusic)
    {
        if (!in_array($request->user()->role, ['super_admin', 'admin'])) {
            abort(403);
        }

        // optionally delete file from storage here
        $backgroundMusic->delete();

        return back()->with('success', 'Music deleted.');
    }

    public function apiIndex(Request $request)
    {
        // For tenants to browse music library
        return response()->json(BackgroundMusic::latest()->get());
    }
}
