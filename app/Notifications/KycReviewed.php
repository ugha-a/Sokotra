<?php

namespace App\Notifications;

use App\Enums\KycStatus;
use App\Models\KycVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KycReviewed extends Notification
{
    use Queueable;

    public function __construct(public KycVerification $kyc) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)->greeting('Halo!');

        if ($this->kyc->status === KycStatus::Verified) {
            return $message
                ->subject('Verifikasi identitas berhasil')
                ->line('Data identitas kamu sudah terverifikasi.');
        }

        return $message
            ->subject('Verifikasi identitas perlu diperbaiki')
            ->line('Data identitas kamu belum bisa diverifikasi.')
            ->line('Alasan: '.$this->kyc->rejection_reason)
            ->action('Kirim ulang dokumen', route('become-trader.show'));
    }
}
