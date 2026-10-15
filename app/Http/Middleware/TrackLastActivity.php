<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps users.last_active_at fresh (at most one write per hour) for the
 * "valid follower" rule in docs/logic/scoring-ranking.md.
 */
class TrackLastActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && ($user->last_active_at === null || $user->last_active_at->lte(now()->subHour()))) {
            User::query()->whereKey($user->id)->update(['last_active_at' => now()]);
        }

        return $next($request);
    }
}
