<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductSold extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

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
            'title' => "{$this->order->product->name} terjual",
            'body' => 'Pendapatan Rp'.number_format($this->order->seller_income, 0, ',', '.').' masuk ke saldo tertahan.',
            'url' => route('wallet.show'),
        ];
    }
}
