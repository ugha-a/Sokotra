<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionActivated extends Notification
{
    use Queueable;

    public function __construct(public Subscription $subscription, public bool $forTrader) {}

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

        return (new MailMessage)->subject($data['title'])->line($data['body'])->action('Buka', $data['url']);
    }

    /**
     * @return array{title: string, body: string, url: string}
     */
    public function toArray(object $notifiable): array
    {
        $trader = $this->subscription->trader->traderProfile->display_name ?? $this->subscription->trader->name;
        $until = $this->subscription->ends_at?->translatedFormat('j F Y');

        return $this->forTrader
            ? [
                'title' => 'Subscriber baru untuk channel kamu',
                'body' => "{$this->subscription->subscriber->name} berlangganan {$this->subscription->months} bulan. Pendapatan masuk ke saldo tertahan.",
                'url' => route('trader.channel'),
            ]
            : [
                'title' => "Langganan {$trader} aktif",
                'body' => "Sinyal premium {$trader} terbuka untukmu sampai {$until}.",
                'url' => route('subscriptions.index'),
            ];
    }
}
