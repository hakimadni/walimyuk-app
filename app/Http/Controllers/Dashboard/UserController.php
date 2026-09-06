<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        abort_if(!in_array($request->user()->role, ['super_admin', 'admin']), 403);
        
        $users = User::withCount('weddings')->latest()->get();
        
        return Inertia::render('Admin/Users/Index', [
            'users' => $users
        ]);
    }

    public function create(Request $request): Response
    {
        abort_if(!in_array($request->user()->role, ['super_admin', 'admin']), 403);
        
        return Inertia::render('Admin/Users/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(!in_array($request->user()->role, ['super_admin', 'admin']), 403);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:tenant,admin,super_admin',
            'is_premium' => 'boolean'
        ]);
        
        $validated['password'] = Hash::make($validated['password']);
        
        User::create($validated);
        
        return redirect()->route('dashboard.users.index')->with('success', 'User berhasil dibuat');
    }

    public function edit(Request $request, User $user): Response
    {
        abort_if(!in_array($request->user()->role, ['super_admin', 'admin']), 403);
        
        return Inertia::render('Admin/Users/Edit', [
            'userModel' => $user
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if(!in_array($request->user()->role, ['super_admin', 'admin']), 403);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => 'required|string|in:tenant,admin,super_admin',
            'is_premium' => 'boolean'
        ]);
        
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        
        $user->update($validated);
        
        return redirect()->route('dashboard.users.index')->with('success', 'User berhasil diperbarui');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if(!in_array($request->user()->role, ['super_admin', 'admin']), 403);
        
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['error' => 'Tidak bisa menghapus akun sendiri.']);
        }
        
        $user->delete();
        
        return redirect()->route('dashboard.users.index')->with('success', 'User berhasil dihapus');
    }
}
