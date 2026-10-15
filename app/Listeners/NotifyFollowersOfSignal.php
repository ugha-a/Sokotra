<?php

namespace App\Listeners;

use App\Enums\SignalStatus;
use App\Events\SignalClosed;
use App\Events\SignalPublished;
use App\Models\Signal;
use App\Models\User;
use App\Notifications\SignalClosedNotification;
use App\Notifications\SignalPublishedNotification;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Followers hear about new signals and their results (docs/logic/signal-lifecycle.md → Notifikasi).
 */
class NotifyFollowersOfSignal
{
    public function handlePublished(SignalPublished $event): void
    {
        $this->notify($event->signal, new SignalPublishedNotification($event->signal));
    }

    public function handleClosed(SignalClosed $event): void
    {
        if ($event->signal->status === SignalStatus::Cancelled) {
            return;
        }

        $this->notify($event->signal, new SignalClosedNotification($event->signal));
    }

    private function notify(Signal $signal, BaseNotification $notification): void
    {
        User::query()
            ->whereIn('id', $signal->trader->followers()->select('follower_id'))
            ->whereKeyNot($signal->trader_id)
            ->chunkById(500, fn ($followers) => Notification::send($followers, $notification));
    }
}
