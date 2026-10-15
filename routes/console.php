<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('signals:evaluate')->everyMinute()->withoutOverlapping();
Schedule::command('stats:refresh')->dailyAt('00:15')->withoutOverlapping();
Schedule::command('ranks:recalculate')->weeklyOn(1, '00:05')->timezone('Asia/Jakarta')->withoutOverlapping();
Schedule::command('wallets:release')->hourly()->withoutOverlapping();
Schedule::command('wallets:reconcile')->dailyAt('00:30');
