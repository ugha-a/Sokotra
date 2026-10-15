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
            'description' => $payment->payable instanceof Subscription
                ? "Langganan {$payment->payable->months} bulan"
                : 'Pembayaran',
            'checkout_url' => $payment->checkout_url,
            'created_at' => $payment->created_at?->toIso8601String(),
        ];
    }
}
