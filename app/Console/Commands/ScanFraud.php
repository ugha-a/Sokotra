<?php

namespace App\Console\Commands;

use App\Actions\Fraud\ScanFollowerFarms;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('fraud:scan')]
#[Description('Look for follower farms (hedging is detected when signals are published)')]
class ScanFraud extends Command
{
    public function handle(ScanFollowerFarms $farms): int
    {
        $this->info("Flag follower palsu baru: {$farms->handle()}.");

        return self::SUCCESS;
    }
}
