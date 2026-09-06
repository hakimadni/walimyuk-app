<?php

use App\Http\Controllers\Dashboard\CoupleProfileController;
use App\Http\Controllers\Dashboard\DocumentChecklistController;
use App\Http\Controllers\Dashboard\EventController;
use App\Http\Controllers\Dashboard\GiftAddressController;
use App\Http\Controllers\Dashboard\GiftBankAccountController;
use App\Http\Controllers\Dashboard\GuestController;
use App\Http\Controllers\Dashboard\RsvpController;
use App\Http\Controllers\Dashboard\WeddingController;
use App\Http\Controllers\Dashboard\WeddingVerseController;
use App\Http\Controllers\Dashboard\WishController;
use App\Http\Controllers\Platform\SuperadminDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicInvitationController;
use App\Http\Controllers\ShortLinkController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ─── Home: redirect authenticated users to dashboard, show Welcome for guests ───
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

// ─── Auth Routes (Breeze defaults) ───
require __DIR__.'/auth.php';

// ─── Profile Routes ───
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ═══════════════════════════════════════════════════════════════════════════════
// PLATFORM ROUTES (Superadmin only)
// ═══════════════════════════════════════════════════════════════════════════════
Route::prefix('platform')
    ->middleware(['auth', 'superadmin'])
    ->group(function () {
        Route::get('/dashboard', SuperadminDashboardController::class)
            ->name('platform.dashboard');
    });

// ═══════════════════════════════════════════════════════════════════════════════
// DASHBOARD ROUTES (Authenticated + Verified owners)
// ═══════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'verified'])->group(function () {

    // ─── Dashboard Overview ───
    Route::get('/dashboard', [\App\Http\Controllers\Dashboard\DashboardController::class, 'index'])
        ->name('dashboard');

    // ─── User CRUD (Admin Only) ───
    Route::resource('users', \App\Http\Controllers\Dashboard\UserController::class)
        ->names('dashboard.users');

    // ─── Wedding CRUD ───
    Route::resource('weddings', WeddingController::class)
        ->names('dashboard.weddings');
    Route::resource('dashboard/weddings', WeddingController::class)
        ->names('dashboard.weddings.legacy');

    // ─── Publish wedding ───
    Route::post('weddings/{wedding}/publish', [WeddingController::class, 'publish'])
        ->name('dashboard.weddings.publish');

    // ─── Invitation builder ───
    Route::get('weddings/{wedding}/builder', [WeddingController::class, 'builder'])
        ->name('dashboard.weddings.builder');
    Route::put('weddings/{wedding}/builder', [WeddingController::class, 'updateBuilder'])
        ->name('dashboard.weddings.builder.update');
    Route::get('my-weddings/{wedding}/builder', [WeddingController::class, 'builder'])
        ->name('dashboard.my-weddings.builder');
    Route::put('my-weddings/{wedding}/builder', [WeddingController::class, 'updateBuilder'])
        ->name('dashboard.my-weddings.builder.update');
    Route::get('dashboard/my-weddings/{wedding}/builder', [WeddingController::class, 'builder']);
    Route::put('dashboard/my-weddings/{wedding}/builder', [WeddingController::class, 'updateBuilder']);

    // ─── Nested under a specific wedding ───
    Route::prefix('weddings/{wedding}')->group(function () {

        // Guests (full CRUD + import/export/mark-sent)
        Route::resource('guests', GuestController::class)
            ->names('dashboard.weddings.guests');
        Route::post('guests/import', [GuestController::class, 'import'])
            ->name('dashboard.weddings.guests.import');
        Route::get('guests/export', [GuestController::class, 'export'])
            ->name('dashboard.weddings.guests.export');
        Route::post('guests/{guest}/mark-sent', [GuestController::class, 'markSent'])
            ->name('dashboard.weddings.guests.mark-sent');

        // RSVPs (index + analytics + export)
        Route::get('rsvps', [RsvpController::class, 'index'])
            ->name('dashboard.weddings.rsvps.index');
        Route::get('rsvps/analytics', [RsvpController::class, 'analytics'])
            ->name('dashboard.weddings.rsvps.analytics');
        Route::get('rsvps/export', [RsvpController::class, 'export'])
            ->name('dashboard.weddings.rsvps.export');

        // Wishes (index + approve + reject + destroy)
        Route::get('wishes', [WishController::class, 'index'])
            ->name('dashboard.weddings.wishes.index');
        Route::put('wishes/{wish}/approve', [WishController::class, 'approve'])
            ->name('dashboard.weddings.wishes.approve');
        Route::put('wishes/{wish}/reject', [WishController::class, 'reject'])
            ->name('dashboard.weddings.wishes.reject');
        Route::delete('wishes/{wish}', [WishController::class, 'destroy'])
            ->name('dashboard.weddings.wishes.destroy');

        // Events (full CRUD)
        Route::resource('events', EventController::class)
            ->names('dashboard.weddings.events');

        // Couple Profiles (show + update)
        Route::get('couple-profiles', [CoupleProfileController::class, 'show'])
            ->name('dashboard.weddings.couple-profiles.show');
        Route::put('couple-profiles', [CoupleProfileController::class, 'update'])
            ->name('dashboard.weddings.couple-profiles.update');

        // Wedding Verses (show + update)
        Route::get('wedding-verses', [WeddingVerseController::class, 'show'])
            ->name('dashboard.weddings.wedding-verses.show');
        Route::put('wedding-verses', [WeddingVerseController::class, 'update'])
            ->name('dashboard.weddings.wedding-verses.update');

        // Gift Bank Accounts (full CRUD)
        Route::resource('gift-bank-accounts', GiftBankAccountController::class)
            ->names('dashboard.weddings.gift-bank-accounts');

        // Gift Addresses (full CRUD)
        Route::resource('gift-addresses', GiftAddressController::class)
            ->names('dashboard.weddings.gift-addresses');

        // Document Checklist (index + store + update + destroy + reset)
        Route::get('document-checklist', [DocumentChecklistController::class, 'index'])
            ->name('dashboard.weddings.document-checklist.index');
        Route::post('document-checklist', [DocumentChecklistController::class, 'store'])
            ->name('dashboard.weddings.document-checklist.store');
        Route::patch('document-checklist/{documentChecklist}', [DocumentChecklistController::class, 'update'])
            ->name('dashboard.weddings.document-checklist.update');
        Route::delete('document-checklist/{documentChecklist}', [DocumentChecklistController::class, 'destroy'])
            ->name('dashboard.weddings.document-checklist.destroy');
        Route::post('document-checklist/reset', [DocumentChecklistController::class, 'reset'])
            ->name('dashboard.weddings.document-checklist.reset');
        Route::post('document-checklist/config', [DocumentChecklistController::class, 'updateConfig'])
            ->name('dashboard.weddings.document-checklist.config');
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// PUBLIC / GUEST INVITATION ROUTES
// Token is validated by ValidateGuestToken middleware (registered as
// 'validate.guest_token' in bootstrap/app.php).
// ═══════════════════════════════════════════════════════════════════════════════
Route::prefix('w/{wedding:slug}')
    ->middleware(['web'])
    ->group(function () {

        // Render the personalized invitation page
        Route::get('/', [PublicInvitationController::class, 'show'])
            ->middleware('validate.guest_token')
            ->name('public.invitation');

        // Submit or update RSVP
        Route::post('/rsvp', [PublicInvitationController::class, 'storeRsvp'])
            ->middleware('validate.guest_token')
            ->name('public.rsvp');

        // Submit a standalone wish
        Route::post('/wish', [PublicInvitationController::class, 'storeWish'])
            ->middleware('validate.guest_token')
            ->name('public.wish');
    });

// ═══════════════════════════════════════════════════════════════════════════════
// SHORT LINK REDIRECT ROUTE
// Redirects clean /s/{code} links to full invitation URLs with tokens
// ═══════════════════════════════════════════════════════════════════════════════
Route::get('/s/{code}', [ShortLinkController::class, 'redirect'])
    ->name('shortlink.redirect');

