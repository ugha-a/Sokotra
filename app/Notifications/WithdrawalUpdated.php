<?php

namespace App\Notifications;

use App\Enums\WithdrawalStatus;
use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalUpdated extends Notification
{
    use Queueable;

    public function __construct(public Withdrawal $withdrawal) {}

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

        return (new MailMessage)->subject($data['title'])->line($data['body'])->action('Buka Wallet', $data['url']);
    }

    /**
     * @return array{title: string, body: string, url: string}
     */
    public function toArray(object $notifiable): array
    {
        $amount = 'Rp'.number_format($this->withdrawal->amount, 0, ',', '.');

        $body = match ($this->withdrawal->status) {
            WithdrawalStatus::Processing => "Penarikan {$amount} disetujui dan sedang ditransfer.",
            WithdrawalStatus::Paid => "Penarikan {$amount} sudah ditransfer (ref. {$this->withdrawal->disbursement_reference}).",
            WithdrawalStatus::Rejected, WithdrawalStatus::Failed => "Penarikan {$amount} {$this->withdrawal->status->label()}: {$this->withdrawal->rejection_reason} Saldo dikembalikan ke wallet.",
            WithdrawalStatus::Requested => "Penarikan {$amount} diajukan.",
        };

        return [
            'title' => "Penarikan {$this->withdrawal->status->label()}",
            'body' => $body,
            'url' => route('wallet.show'),
        ];
    }
}
