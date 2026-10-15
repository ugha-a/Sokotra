<?php

namespace App\Console\Commands;

use App\Actions\Ranking\RecalculateRanks;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ranks:recalculate')]
#[Description('Recalculate trader ranks from rolling statistics and valid followers')]
class RecalculateTraderRanks extends Command
{
    public function handle(RecalculateRanks $recalculate): int
    {
        $this->call('stats:refresh');

        $changed = $recalculate->handle();
        $this->info("Rank berubah untuk {$changed} trader.");

        return self::SUCCESS;
    }
}
