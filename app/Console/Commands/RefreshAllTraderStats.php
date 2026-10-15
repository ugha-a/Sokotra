<?php

namespace App\Console\Commands;

use App\Actions\Ranking\RefreshTraderStats;
use App\Models\TraderProfile;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('stats:refresh')]
#[Description('Recompute statistics for every trader (rolling windows move daily)')]
class RefreshAllTraderStats extends Command
{
    public function handle(RefreshTraderStats $refresh): int
    {
        $count = 0;

        TraderProfile::query()->with('user')->chunkById(200, function ($profiles) use ($refresh, &$count) {
            foreach ($profiles as $profile) {
                $refresh->handle($profile->user);
                $count++;
            }
        });

        $this->info("Statistik diperbarui untuk {$count} trader.");

        return self::SUCCESS;
    }
}
