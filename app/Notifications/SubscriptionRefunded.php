<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRefunded extends Notification
{
    use Queueable;

    public function __construct(public Subscription $subscription) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $data = $this->toArray($notifiable);

        return (new MailMessage)->subject($data['title'])->line($data['body']);
    }

    /**
     * @return array{title: string, body: string, url: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Langganan di-refund',
            'body' => 'Rp'.number_format($this->subscription->price_paid, 0, ',', '.').' dikembalikan. Alasan: '.$this->subscription->refund_reason,
            'url' => route('subscriptions.index'),
        ];
    }
}
