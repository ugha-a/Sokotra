<?php

namespace App\Notifications;

use App\Models\PayrollItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayrollPaid extends Notification
{
    use Queueable;

    public function __construct(public PayrollItem $item) {}

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

        return (new MailMessage)->subject($data['title'])->line($data['body'])->action('Lihat slip gaji', $data['url']);
    }

    /**
     * @return array{title: string, body: string, url: string}
     */
    public function toArray(object $notifiable): array
    {
        $amount = 'Rp'.number_format($this->item->total_amount, 0, ',', '.');

        return [
            'title' => "Gaji & reward {$this->item->period->label()} sudah masuk",
            'body' => "{$amount} dikreditkan ke saldo tersedia di wallet kamu.",
            'url' => route('trader.payroll'),
        ];
    }
}
