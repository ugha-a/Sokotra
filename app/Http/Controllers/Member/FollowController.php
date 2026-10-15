<?php

namespace App\Http\Controllers\Member;

use App\Enums\TraderStatus;
use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\TraderProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FollowController extends Controller
{
    public function store(Request $request, TraderProfile $profile): RedirectResponse
    {
        abort_unless($profile->status === TraderStatus::Approved, 404);

        if ($request->user()?->id === $profile->user_id) {
            throw ValidationException::withMessages(['follow' => 'Kamu tidak bisa mengikuti diri sendiri.']);
        }

        Follow::firstOrCreate(['follower_id' => $request->user()?->id, 'trader_id' => $profile->user_id]);

        return back();
    }

    public function destroy(Request $request, TraderProfile $profile): RedirectResponse
    {
        Follow::where('follower_id', $request->user()?->id)->where('trader_id', $profile->user_id)->delete();

        return back();
    }
}
