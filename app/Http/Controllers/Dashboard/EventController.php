<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Wedding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
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
