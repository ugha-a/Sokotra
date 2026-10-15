<?php

namespace App\Notifications;

use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductReviewed extends Notification
{
    use Queueable;

    public function __construct(public Product $product) {}

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

        return (new MailMessage)->subject($data['title'])->line($data['body'])->action('Buka Produk Saya', $data['url']);
    }

    /**
     * @return array{title: string, body: string, url: string}
     */
    public function toArray(object $notifiable): array
    {
        $published = $this->product->status === ProductStatus::Published;

        return [
            'title' => $published ? "{$this->product->name} tayang di marketplace" : "{$this->product->name} perlu diperbaiki",
            'body' => $published ? 'Produk kamu sudah bisa dibeli.' : 'Alasan: '.$this->product->rejection_reason,
            'url' => route('trader.products.index'),
        ];
    }
}
