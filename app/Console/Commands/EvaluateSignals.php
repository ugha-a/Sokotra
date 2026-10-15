<?php

namespace App\Console\Commands;

use App\Actions\Signal\EvaluateOpenSignals;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('signals:evaluate')]
#[Description('Score open signals from the latest 1-minute candles')]
class EvaluateSignals extends Command
{
    public function handle(EvaluateOpenSignals $evaluate): int
    {
        $summary = $evaluate->handle();

        $this->info("Instrumen: {$summary['instruments']}, sinyal ditutup: {$summary['closed']}.");

        if ($summary['failed'] !== []) {
            $this->warn('Feed gagal untuk: '.implode(', ', $summary['failed']));
        }

        return self::SUCCESS;
    }
}
