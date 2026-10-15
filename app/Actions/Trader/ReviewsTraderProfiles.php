<?php

namespace App\Actions\Trader;

use App\Enums\TraderStatus;
use App\Models\TraderProfile;
use Illuminate\Validation\ValidationException;

/**
 * Shared state guard for admin review actions.
 */
trait ReviewsTraderProfiles
{
    private function ensureStatus(TraderProfile $profile, TraderStatus $expected, string $message): void
    {
        if ($profile->status !== $expected) {
            throw ValidationException::withMessages(['application' => $message]);
        }
    }
}
