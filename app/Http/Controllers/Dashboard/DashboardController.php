<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Wedding;
use App\Models\User;
use App\Models\Guest;
use App\Models\Rsvp;
use App\Models\Wish;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $isAdmin = in_array($user->role, ['super_admin', 'admin']);

        if ($isAdmin) {
            $totalWeddings = Wedding::count();
            $totalUsers = User::count();
            $totalGuests = Guest::count();
            $totalRsvps = Rsvp::count();
            $totalWishes = Wish::count();
            
            $recentWeddings = Wedding::with('user:id,name')->latest()->take(5)->get();
        } else {
            $totalWeddings = $user->weddings()->count();
            $totalUsers = null;
            
            // Get stats across all tenant's weddings
            $weddingIds = $user->weddings()->pluck('id');
            $totalGuests = Guest::whereIn('wedding_id', $weddingIds)->count();
            $totalRsvps = Rsvp::whereIn('wedding_id', $weddingIds)->count();
            $totalWishes = Wish::whereIn('wedding_id', $weddingIds)->count();
            
            $recentWeddings = $user->weddings()->latest()->take(5)->get();
        }

        // --- CHART DATA ---
        $monthsLabels = collect(range(5, 0))->map(function($i) {
            return now()->subMonths($i)->format('M Y');
        });

        $weddingsData = collect(range(5, 0))->map(function($i) use ($isAdmin, $user) {
            $query = Wedding::whereYear('created_at', now()->subMonths($i)->year)
                            ->whereMonth('created_at', now()->subMonths($i)->month);
            if (!$isAdmin) {
                $query->where('user_id', $user->id);
            }
            return $query->count();
        });

        $usersData = [];
        if ($isAdmin) {
            $usersData = collect(range(5, 0))->map(function($i) {
                return User::whereYear('created_at', now()->subMonths($i)->year)
                           ->whereMonth('created_at', now()->subMonths($i)->month)
                           ->count();
            });
        }

        $rsvpBaseQuery = Rsvp::query();
        if (!$isAdmin) {
            $rsvpBaseQuery->whereIn('wedding_id', $weddingIds ?? []);
        }
        $attendingCount = (clone $rsvpBaseQuery)->where('is_attending', true)->count();
        $notAttendingCount = (clone $rsvpBaseQuery)->where('is_attending', false)->count();

        return Inertia::render('Dashboard', [
            'metrics' => [
                'totalWeddings' => $totalWeddings,
                'totalUsers' => $totalUsers,
                'totalGuests' => $totalGuests,
                'totalRsvps' => $totalRsvps,
                'totalWishes' => $totalWishes,
            ],
            'chartData' => [
                'labels' => $monthsLabels,
                'weddings' => $weddingsData,
                'users' => $usersData,
                'rsvp' => [
                    'attending' => $attendingCount,
                    'notAttending' => $notAttendingCount
                ]
            ],
            'recentWeddings' => $recentWeddings,
            'isAdmin' => $isAdmin
        ]);
    }
}
