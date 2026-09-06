<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Wedding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    private function resolveCoordinates(?string $url, ?float &$latitude, ?float &$longitude): void
    {
        if (!$url || ($latitude && $longitude)) {
            return;
        }

        try {
            // If it's a short url, get the final redirected URL
            $response = Http::withOptions(['allow_redirects' => true])->head($url);
            $finalUrl = $response->effectiveUri() ? (string) $response->effectiveUri() : $url;
            
            if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $finalUrl, $matches)) {
                $latitude = (float) $matches[1];
                $longitude = (float) $matches[2];
            } elseif (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $finalUrl, $matches)) {
                $latitude = (float) $matches[1];
                $longitude = (float) $matches[2];
            }
        } catch (\Throwable $e) {
            // Ignore if we can't resolve it
        }
    }
    /**
     * Display a listing of events for the wedding.
     */
    public function index(Request $request, Wedding $wedding): Response
    {
        $this->authorizeWedding($request, $wedding);

        $events = $wedding->events()
            ->orderBy('sort_order')
            ->orderBy('date')
            ->get();

        return Inertia::render('Admin/Events/Index', [
            'wedding' => $wedding,
            'events' => $events,
        ]);
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(Request $request, Wedding $wedding): Response
    {
        $this->authorizeWedding($request, $wedding);

        return Inertia::render('Admin/Events/Create', [
            'wedding' => $wedding,
        ]);
    }

    /**
     * Store a newly created event.
     */
    public function store(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'start_time' => ['nullable', 'string', 'max:10'],
            'end_time' => ['nullable', 'string', 'max:10'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'google_maps_url' => ['nullable', 'url', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $latitude = $validated['latitude'] ?? null;
        $longitude = $validated['longitude'] ?? null;
        $this->resolveCoordinates($validated['google_maps_url'] ?? null, $latitude, $longitude);
        $validated['latitude'] = $latitude;
        $validated['longitude'] = $longitude;

        $wedding->events()->create($validated);

        return redirect()->route('dashboard.weddings.events.index', $wedding)
            ->with('success', 'Acara berhasil ditambahkan.');
    }

    /**
     * Display the specified event.
     */
    public function show(Request $request, Wedding $wedding, Event $event): Response
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeEvent($wedding, $event);

        return Inertia::render('Admin/Events/Show', [
            'wedding' => $wedding,
            'event' => $event,
        ]);
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Request $request, Wedding $wedding, Event $event): Response
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeEvent($wedding, $event);

        return Inertia::render('Admin/Events/Edit', [
            'wedding' => $wedding,
            'event' => $event,
        ]);
    }

    /**
     * Update the specified event.
     */
    public function update(Request $request, Wedding $wedding, Event $event): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeEvent($wedding, $event);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'start_time' => ['nullable', 'string', 'max:10'],
            'end_time' => ['nullable', 'string', 'max:10'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'google_maps_url' => ['nullable', 'url', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $latitude = $validated['latitude'] ?? null;
        $longitude = $validated['longitude'] ?? null;
        $this->resolveCoordinates($validated['google_maps_url'] ?? null, $latitude, $longitude);
        $validated['latitude'] = $latitude;
        $validated['longitude'] = $longitude;

        $event->update($validated);

        return redirect()->route('dashboard.weddings.events.index', $wedding)
            ->with('success', 'Acara berhasil diperbarui.');
    }

    /**
     * Remove the specified event.
     */
    public function destroy(Request $request, Wedding $wedding, Event $event): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);
        $this->authorizeEvent($wedding, $event);

        $event->delete();

        return redirect()->route('dashboard.weddings.events.index', $wedding)
            ->with('success', 'Acara berhasil dihapus.');
    }

    /**
     * Ensure the wedding belongs to the authenticated user.
     */
    private function authorizeWedding(Request $request, Wedding $wedding): void
    {
        $this->authorize('view', $wedding);
    }

    /**
     * Ensure the event belongs to the wedding.
     */
    private function authorizeEvent(Wedding $wedding, Event $event): void
    {
        abort_unless($event->wedding_id === $wedding->id, 404, 'Acara tidak ditemukan.');
    }
}
