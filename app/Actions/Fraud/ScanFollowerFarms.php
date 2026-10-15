<?php

namespace App\Actions\Fraud;

use App\Enums\FraudFlagStatus;
use App\Enums\FraudFlagType;
use App\Models\Follow;
use App\Models\FraudFlag;
use App\Support\Settings;

/**
 * Many new followers of one trader registered from the same IP look like a farm.
 * One flag per (trader, IP); re-scans never duplicate it.
 */
class ScanFollowerFarms
{
    public function handle(): int
    {
        $threshold = Settings::get('fraud.follower_ip_threshold');
        $since = now()->subDays(Settings::get('fraud.follower_window_days'));
        $created = 0;

        $groups = Follow::query()
            ->join('users', 'users.id', '=', 'follows.follower_id')
            ->where('follows.created_at', '>=', $since)
            ->whereNotNull('users.registration_ip')
            ->selectRaw('follows.trader_id, users.registration_ip as ip, count(*) as total')
            ->groupBy('follows.trader_id', 'users.registration_ip')
            ->havingRaw('count(*) >= ?', [$threshold])
            ->get();

        foreach ($groups as $group) {
            $traderId = (int) $group->getAttribute('trader_id');
            $ip = (string) $group->getAttribute('ip');

            $exists = FraudFlag::query()
                ->where('type', FraudFlagType::FollowerFarm)
                ->get()
                ->contains(fn (FraudFlag $flag) => ($flag->details['trader_id'] ?? null) === $traderId && ($flag->details['ip'] ?? null) === $ip);

            if ($exists) {
                continue;
            }

            FraudFlag::create([
                'type' => FraudFlagType::FollowerFarm,
                'status' => FraudFlagStatus::Open,
                'subject_user_ids' => Follow::query()
                    ->join('users', 'users.id', '=', 'follows.follower_id')
                    ->where('follows.trader_id', $traderId)
                    ->where('users.registration_ip', $ip)
                    ->where('follows.created_at', '>=', $since)
                    ->pluck('follows.follower_id')->map(fn ($id) => (int) $id)->values()->all(),
                'details' => ['trader_id' => $traderId, 'ip' => $ip, 'count' => (int) $group->getAttribute('total')],
            ]);
            $created++;
        }

        return $created;
    }
}
