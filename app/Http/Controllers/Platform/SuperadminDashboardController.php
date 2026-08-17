<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SuperadminDashboardController extends Controller
{
    /**
     * Display the superadmin dashboard.
     */
    public function __invoke(Request $request): Response
    {
        $totalUsers = User::count();
        $totalWeddings = Wedding::count();
        $publishedWeddings = Wedding::where('status', 'published')->count();
        $draftWeddings = Wedding::where('status', 'draft')->count();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_users' => $totalUsers,
                'total_weddings' => $totalWeddings,
                'published_weddings' => $publishedWeddings,
                'draft_weddings' => $draftWeddings,
            ],
        ]);
    }
}
