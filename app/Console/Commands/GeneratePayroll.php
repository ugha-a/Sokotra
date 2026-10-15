<?php

namespace App\Console\Commands;

use App\Actions\Payroll\GeneratePayrollDraft;
use App\Support\BusinessCalendar;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('payroll:generate {month? : YYYY-MM, default bulan lalu (WIB)}')]
#[Description('Create or rebuild the draft payroll for a month')]
class GeneratePayroll extends Command
{
    public function handle(GeneratePayrollDraft $generate): int
    {
        $argument = $this->argument('month');
        $month = is_string($argument) && $argument !== '' ? $argument : BusinessCalendar::now()->subMonthNoOverflow()->format('Y-m');
        $period = $generate->handle($month);

        $this->info("Draft payroll {$period->month}: {$period->items()->count()} trader, total Rp".number_format($period->total_amount, 0, ',', '.').'.');

        return self::SUCCESS;
    }
}
