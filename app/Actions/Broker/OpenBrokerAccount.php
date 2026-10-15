<?php

namespace App\Actions\Broker;

use App\Models\Broker;
use App\Models\BrokerReferral;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Records the click and returns the broker's IB link with the referral code.
 */
class OpenBrokerAccount
{
    public function handle(User $user, Broker $broker): string
    {
        do {
            $code = Str::upper(Str::random(10));
        } while (BrokerReferral::where('ref_code', $code)->exists());

        BrokerReferral::create(['user_id' => $user->id, 'broker_id' => $broker->id, 'ref_code' => $code, 'clicked_at' => now()]);

        return str_replace('{ref}', $code, $broker->affiliate_url);
    }
}
