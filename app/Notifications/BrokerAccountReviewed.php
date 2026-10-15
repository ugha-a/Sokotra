<?php

namespace App\Notifications;

use App\Enums\BrokerAccountStatus;
use App\Models\BrokerAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BrokerAccountReviewed extends Notification
{
    use Queueable;

    public function __construct(public BrokerAccount $account) {}

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
        $linked = $this->account->status === BrokerAccountStatus::Linked;

        return [
            'title' => $linked ? "Akun {$this->account->broker->name} terhubung" : "Akun {$this->account->broker->name} belum bisa dihubungkan",
            'body' => $linked ? "Login {$this->account->account_login} sudah terverifikasi." : 'Alasan: '.$this->account->notes,
            'url' => route('broker-accounts.index'),
        ];
    }
}
