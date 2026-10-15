<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InactivePremiumChannels extends Notification
{
    use Queueable;

    /**
     * @param  list<string>  $traders
     */
    public function __construct(public array $traders, public int $days) {}

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
        return [
            'title' => count($this->traders).' channel premium tanpa sinyal premium '.$this->days.' hari',
            'body' => 'Punya subscriber aktif tapi tidak memposting sinyal premium: '.implode(', ', $this->traders).'.',
            'url' => route('admin.subscriptions.index'),
        ];
    }
}
