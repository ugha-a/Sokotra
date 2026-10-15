<?php

namespace App\Http\Controllers;

use App\Actions\Payments\HandlePaymentEvent;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\Payments\InvalidWebhookSignature;
use App\Services\Payments\PaymentEvent;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\SimulatedPaymentGateway;
use App\Support\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function show(Request $request, Payment $payment): Response
    {
        abort_unless($payment->user_id === $request->user()?->id, 403);

        return Inertia::render('payments/show', ['payment' => $this->present($payment)]);
    }

    public function webhook(Request $request, string $gateway, PaymentGateway $payments, HandlePaymentEvent $handle): JsonResponse
    {
        abort_unless($gateway === $payments->name(), 404);

        try {
            $event = $payments->parseWebhook($request);
        } catch (InvalidWebhookSignature) {
            abort(403);
        }

        $handle->handle($event);

        return response()->json(['ok' => true]);
    }

    public function simulate(Request $request, Payment $payment, PaymentGateway $payments): Response
    {
        $this->ensureSimulation($request, $payment, $payments);

        return Inertia::render('payments/simulate', ['payment' => $this->present($payment)]);
    }

    public function completeSimulation(Request $request, Payment $payment, PaymentGateway $payments, HandlePaymentEvent $handle): RedirectResponse
    {
        $this->ensureSimulation($request, $payment, $payments);
        $outcome = $request->validate(['outcome' => ['required', 'in:paid,failed']])['outcome'];

        $handle->handle(new PaymentEvent($payment->gateway_reference, PaymentStatus::from($outcome), ['simulated' => true]));

        return to_route('payments.show', $payment);
    }

    public function submitProof(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->user_id === $request->user()?->id, 403);
        abort_unless($payment->status === PaymentStatus::Pending, 400);

        $validated = $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'sender_name' => ['nullable', 'string', 'max:100'],
            'sender_bank' => ['nullable', 'string', 'max:100'],
        ], [
            'proof.required' => 'Unggah file bukti transfer.',
            'proof.mimes' => 'Format bukti transfer harus JPG, PNG, WEBP, atau PDF.',
            'proof.max' => 'Ukuran file bukti transfer maksimal 5 MB.',
        ]);

        $path = $request->file('proof')->store("payment-proofs/{$payment->id}", 'local');

        $payment->update([
            'raw_payload' => array_merge((array) $payment->raw_payload, [
                'proof_path' => $path,
                'sender_name' => $validated['sender_name'] ?? null,
                'sender_bank' => $validated['sender_bank'] ?? null,
                'transferred_at' => now()->toIso8601String(),
            ]),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Bukti transfer berhasil dikirim. Menunggu verifikasi admin.']);

        return to_route('payments.show', $payment);
    }

    private function ensureSimulation(Request $request, Payment $payment, PaymentGateway $payments): void
    {
        abort_unless($payments instanceof SimulatedPaymentGateway && ! app()->isProduction(), 404);
        abort_unless($payment->user_id === $request->user()?->id, 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Payment $payment): array
    {
        $payment->loadMissing('payable');

        return [
            'id' => $payment->id,
            'amount' => $payment->amount,
            'status' => $payment->status->value,
            'status_label' => $payment->status->label(),
            'gateway' => $payment->gateway,
            'gateway_reference' => $payment->gateway_reference,
            'description' => $payment->payable instanceof Subscription
                ? "Langganan {$payment->payable->months} bulan"
                : 'Pembayaran',
            'checkout_url' => $payment->checkout_url,
            'return_url' => $payment->payable instanceof Subscription
                ? route('subscriptions.index')
                : route('purchases.index'),
            'expires_at' => $payment->expires_at?->toIso8601String(),
            'created_at' => $payment->created_at?->toIso8601String(),
            'bank_destination' => [
                'bank_name' => Settings::get('payment.bank_name'),
                'account_number' => Settings::get('payment.account_number'),
                'account_holder' => Settings::get('payment.account_holder'),
                'instructions' => Settings::get('payment.instructions'),
            ],
            'proof' => [
                'has_proof' => ! empty($payment->raw_payload['proof_path']),
                'sender_name' => $payment->raw_payload['sender_name'] ?? null,
                'sender_bank' => $payment->raw_payload['sender_bank'] ?? null,
                'transferred_at' => $payment->raw_payload['transferred_at'] ?? null,
            ],
        ];
    }
}
