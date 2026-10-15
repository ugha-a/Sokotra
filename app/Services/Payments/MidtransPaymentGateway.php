<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Midtrans Snap (https://docs.midtrans.com) driver. Webhooks are verified with
 * sha512(order_id + status_code + gross_amount + server_key).
 */
class MidtransPaymentGateway implements PaymentGateway
{
    public function __construct(private string $serverKey, private bool $production) {}

    public function name(): string
    {
        return 'midtrans';
    }

    public function createCheckout(Payment $payment): Checkout
    {
        $orderId = "SKT-{$payment->id}-".Str::upper(Str::random(6));
        $payment->loadMissing(['user', 'payable']);

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->acceptJson()
                ->timeout(15)
                ->post($this->snapUrl(), [
                    'transaction_details' => ['order_id' => $orderId, 'gross_amount' => $payment->amount],
                    'item_details' => [[
                        'id' => "{$payment->payable_type}-{$payment->payable_id}",
                        'price' => $payment->amount,
                        'quantity' => 1,
                        'name' => Str::limit($this->itemName($payment), 50, ''),
                    ]],
                    'customer_details' => ['first_name' => $payment->user->name, 'email' => $payment->user->email],
                    'callbacks' => ['finish' => route('payments.show', $payment)],
                    'expiry' => ['unit' => 'hour', 'duration' => (int) config('services.payments.checkout_expiry_hours', 24)],
                ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Midtrans tidak dapat dihubungi: '.$exception->getMessage(), previous: $exception);
        }

        if ($response->failed() || ! is_string($response->json('redirect_url'))) {
            throw new RuntimeException('Midtrans menolak transaksi: '.$response->body());
        }

        return new Checkout($orderId, (string) $response->json('redirect_url'));
    }

    public function parseWebhook(Request $request): PaymentEvent
    {
        $orderId = (string) $request->input('order_id');
        $statusCode = (string) $request->input('status_code');
        $grossAmount = (string) $request->input('gross_amount');
        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        if (! hash_equals($expected, (string) $request->input('signature_key'))) {
            throw new InvalidWebhookSignature('Invalid Midtrans signature.');
        }

        return new PaymentEvent($orderId, $this->mapStatus((string) $request->input('transaction_status'), $request->input('fraud_status')), $request->all());
    }

    private function mapStatus(string $transactionStatus, mixed $fraudStatus): PaymentStatus
    {
        return match ($transactionStatus) {
            'settlement' => PaymentStatus::Paid,
            'capture' => $fraudStatus === 'accept' || $fraudStatus === null ? PaymentStatus::Paid : PaymentStatus::Pending,
            'expire' => PaymentStatus::Expired,
            'deny', 'cancel', 'failure' => PaymentStatus::Failed,
            default => PaymentStatus::Pending,
        };
    }

    private function snapUrl(): string
    {
        return ($this->production ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com').'/snap/v1/transactions';
    }

    private function itemName(Payment $payment): string
    {
        return match (true) {
            $payment->payable instanceof Subscription => "Langganan Sokotra {$payment->payable->months} bulan",
            $payment->payable instanceof Order => "Sokotra: {$payment->payable->product->name}",
            default => 'Pembayaran Sokotra',
        };
    }
}
