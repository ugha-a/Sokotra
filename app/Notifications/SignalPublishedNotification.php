<?php

namespace App\Notifications;

use App\Enums\SignalVisibility;
use App\Models\Signal;
use App\Notifications\Concerns\DescribesSignal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SignalPublishedNotification extends Notification implements ShouldQueue
{
    use DescribesSignal, Queueable;

    public function __construct(public Signal $signal) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{title: string, body: string, url: string}
     */
    public function toArray(object $notifiable): array
    {
        $premium = $this->signal->visibility === SignalVisibility::Premium;

        return [
            'title' => 'Sinyal baru: '.$this->signalName($this->signal),
            'body' => $premium
                ? 'Sinyal premium. Detail terbuka untuk subscriber dan untuk semua setelah sinyal selesai.'
                : "{$this->signal->order_type->label()} di {$this->signal->entry_price}, SL {$this->signal->stop_loss}, TP {$this->signal->take_profit}.",
            'url' => $this->traderUrl($this->signal),
        ];
    }
}
