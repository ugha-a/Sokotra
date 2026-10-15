<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\TraderStat;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FollowingController extends Controller
{
    public function index(Request $request): Response
    {
        $follows = Follow::query()
            ->where('follower_id', $request->user()?->id)
            ->with('trader.traderProfile.rank')
            ->latest('created_at')
            ->get();
        $stats = TraderStat::query()->where('period', 'all_time')->whereIn('user_id', $follows->pluck('trader_id'))->get()->keyBy('user_id');

        return Inertia::render('member/following', [
            'traders' => $follows->filter(fn (Follow $follow) => $follow->trader->traderProfile !== null)->map(function (Follow $follow) use ($stats) {
                $profile = $follow->trader->traderProfile;

                return [
                    'id' => $profile?->id,
                    'name' => $profile?->display_name,
                    'slug' => $profile?->slug,
                    'rank' => $profile?->rank ? ['name' => $profile->rank->name, 'color' => $profile->rank->color] : null,
                    'stats' => $stats->get($follow->trader_id)?->summary(),
                    'followed_at' => $follow->created_at?->toIso8601String(),
                ];
            })->values()->all(),
        ]);
    }
}
