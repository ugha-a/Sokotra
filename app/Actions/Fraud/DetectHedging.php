<?php

namespace App\Actions\Fraud;

use App\Enums\FraudFlagStatus;
use App\Enums\FraudFlagType;
use App\Enums\SignalDirection;
use App\Models\FraudFlag;
use App\Models\Signal;
use App\Support\Settings;
use Illuminate\Support\Facades\DB;

/**
 * Opposite signals on the same instrument, from different traders on the same network,
 * published within fraud.hedge_window_minutes: one of them always "wins".
 * Matching uses the publish IP; the user-agent hash alone is too common to be evidence.
 */
class DetectHedging
{
    public function handle(Signal $signal): ?FraudFlag
    {
        if ($signal->publish_ip === null) {
            return null;
        }

        $window = Settings::get('fraud.hedge_window_minutes');
        $opposite = $signal->direction === SignalDirection::Buy ? SignalDirection::Sell : SignalDirection::Buy;

        $matches = Signal::query()
            ->where('instrument_id', $signal->instrument_id)
            ->where('direction', $opposite)
            ->where('trader_id', '!=', $signal->trader_id)
            ->where('publish_ip', $signal->publish_ip)
            ->whereBetween('published_at', [$signal->published_at->copy()->subMinutes($window), $signal->published_at->copy()->addMinutes($window)])
            ->get();

        if ($matches->isEmpty()) {
            return null;
        }

        $signalIds = $matches->pluck('id')->push($signal->id)->unique()->values()->all();

        return DB::transaction(function () use ($signal, $matches, $signalIds, $window) {
            Signal::whereIn('id', $signalIds)->update(['excluded_from_payroll' => true]);

            return FraudFlag::create([
                'type' => FraudFlagType::Hedging,
                'status' => FraudFlagStatus::Open,
                'subject_user_ids' => $matches->pluck('trader_id')->push($signal->trader_id)->unique()->values()->all(),
                'signal_ids' => $signalIds,
                'details' => ['instrument_id' => $signal->instrument_id, 'ip' => $signal->publish_ip, 'window_minutes' => $window],
            ]);
        });
    }
}
