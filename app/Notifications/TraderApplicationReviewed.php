<?php

namespace App\Notifications;

use App\Enums\TraderStatus;
use App\Models\TraderProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TraderApplicationReviewed extends Notification
{
    use Queueable;

    public function __construct(public TraderProfile $profile) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)->greeting("Halo, {$this->profile->display_name}!");

        if ($this->profile->status === TraderStatus::Approved) {
            return $message
                ->subject('Aplikasi trader kamu disetujui')
                ->line('Selamat, kamu sekarang trader di Sotra dan bisa mulai memposting analisa.')
                ->line('Setiap sinyal dinilai otomatis dari harga pasar dan masuk ke statistik kamu.')
                ->action('Buka Dashboard Trader', route('trader.dashboard'));
        }

        return $message
            ->subject('Aplikasi trader kamu belum disetujui')
            ->line('Alasan: '.$this->profile->rejection_reason)
            ->action('Lihat status pengajuan', route('become-trader.show'));
    }
}
