<?php

namespace App\Notifications;

use App\Models\Signal;
use App\Notifications\Concerns\DescribesSignal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SignalClosedNotification extends Notification implements ShouldQueue
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
        $result = $this->signal->result_r === null
            ? ''
            : ' ('.((float) $this->signal->result_r > 0 ? '+' : '').number_format((float) $this->signal->result_r, 2, ',', '.').'R)';

        return [
            'title' => $this->signal->status->label().': '.$this->signalName($this->signal).$result,
            'body' => 'Hasil dinilai otomatis dari harga pasar.',
            'url' => $this->traderUrl($this->signal),
        ];
    }
}
