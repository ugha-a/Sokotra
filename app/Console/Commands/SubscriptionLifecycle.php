<?php

namespace App\Console\Commands;

use App\Actions\Subscription\RunSubscriptionLifecycle;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('subscriptions:lifecycle')]
#[Description('Expire subscriptions and checkouts, send reminders, alert on inactive premium channels')]
class SubscriptionLifecycle extends Command
{
    public function handle(RunSubscriptionLifecycle $lifecycle): int
    {
        $lifecycle->handle();
        $this->info('Siklus langganan selesai.');

        return self::SUCCESS;
    }
}
