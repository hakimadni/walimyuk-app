<?php

namespace App\Http\Middleware;

use App\Models\Guest;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateGuestToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) $request->query('token', '');
        $wedding = $request->route('wedding');

        if (! $wedding) {
            abort(404, 'Not Found');
        }

        $user = $request->user();
        $isOwnerOrAdmin = $user && (
            $user->id === $wedding->user_id ||
            in_array($user->role, ['super_admin', 'admin'], true)
        );

        $guest = null;

        if ($token !== '') {
            $candidates = Guest::where('wedding_id', $wedding->id)
                ->select(['id', 'wedding_id', 'token'])
                ->get();

            foreach ($candidates as $candidate) {
                if (is_string($candidate->token) && hash_equals($candidate->token, $token)) {
                    $guest = Guest::find($candidate->id);
                    break;
                }
            }
        }

        // If accessed by owner or admin without a valid guest token, provide a fallback guest
        if (! $guest) {
            if ($isOwnerOrAdmin) {
                $guest = $wedding->guests()->oldest('id')->first();
                if (! $guest) {
                    $guest = $wedding->guests()->create([
                        'name' => 'Tamu Preview',
                        'token' => \Illuminate\Support\Str::random(64),
                        'slug' => 'tamu-preview-' . \Illuminate\Support\Str::random(6),
                        'max_pax' => 2,
                        'is_invitation_sent' => false,
                    ]);
                }
            } else {
                abort(404, 'Not Found');
            }
        }

        $request->attributes->set('guest', $guest);

        if ($wedding->isPublished()) {
            return $next($request);
        }

        if ($wedding->isDraft() && $isOwnerOrAdmin) {
            return $next($request);
        }

        abort(403, 'This invitation is not currently accessible.');
    }
}
