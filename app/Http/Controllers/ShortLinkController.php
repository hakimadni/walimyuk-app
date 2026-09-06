<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShortLinkController extends Controller
{
    /**
     * Redirect short link code to the full public invitation URL with token.
     */
    public function redirect(Request $request, string $code): RedirectResponse
    {
        $code = strtolower(trim($code));

        $guest = Guest::where('short_code', $code)
            ->with('wedding')
            ->first();

        if (! $guest || ! $guest->wedding) {
            abort(404, 'Undangan tidak ditemukan atau tautan tidak valid.');
        }

        return redirect()->route('public.invitation', [
            'wedding' => $guest->wedding->slug,
            'token' => $guest->token,
        ]);
    }
}
