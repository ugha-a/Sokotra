<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiring extends Notification
{
    use Queueable;

    public function __construct(public Subscription $subscription, public int $days) {}

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

        return (new MailMessage)->subject($data['title'])->line($data['body'])->action('Perpanjang', $data['url']);
    }

    /**
     * @return array{title: string, body: string, url: string}
     */
    public function toArray(object $notifiable): array
    {
        $trader = $this->subscription->trader->traderProfile->display_name ?? $this->subscription->trader->name;

        return [
            'title' => "Langganan {$trader} berakhir dalam {$this->days} hari",
            'body' => 'Berakhir '.$this->subscription->ends_at?->translatedFormat('j F Y').'. Perpanjang jika ingin tetap menerima sinyal premium.',
            'url' => $this->subscription->trader->traderProfile
                ? route('subscriptions.create', $this->subscription->trader->traderProfile->id)
                : route('subscriptions.index'),
        ];
    }
}
