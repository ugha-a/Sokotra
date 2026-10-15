<?php

namespace App\Notifications;

use App\Models\Rank;
use App\Models\TraderProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RankChanged extends Notification
{
    use Queueable;

    public function __construct(public TraderProfile $profile, public ?Rank $from) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Rank kamu sekarang {$this->profile->rank?->name}")
            ->greeting("Halo, {$this->profile->display_name}!")
            ->line($this->sentence())
            ->line('Rank dihitung ulang setiap Senin dari statistik 90 hari terakhir dan follower valid.')
            ->action('Buka Dashboard Trader', route('trader.dashboard'));
    }

    /**
     * @return array{title: string, body: string, url: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Rank kamu sekarang {$this->profile->rank?->name}",
            'body' => $this->sentence(),
            'url' => route('trader.dashboard'),
        ];
    }

    private function sentence(): string
    {
        $promoted = ($this->profile->rank->level ?? 0) > ($this->from->level ?? 0);

        return $promoted
            ? "Selamat, rank kamu naik dari {$this->from?->name} ke {$this->profile->rank?->name}."
            : "Rank kamu turun dari {$this->from?->name} ke {$this->profile->rank?->name}.";
    }
}
