<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Payments\HandlePaymentEvent;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\PaymentEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PaymentController extends Controller
{
    public function confirm(Payment $payment, HandlePaymentEvent $handle): RedirectResponse
    {
        $handle->handle(new PaymentEvent(
            $payment->gateway_reference,
            PaymentStatus::Paid,
            [
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now()->toIso8601String(),
            ],
        ));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pembayaran berhasil dikonfirmasi.']);

        return back();
    }

    public function reject(Request $request, Payment $payment, HandlePaymentEvent $handle): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ], [], ['reason' => 'alasan']);

        $handle->handle(new PaymentEvent(
            $payment->gateway_reference,
            PaymentStatus::Failed,
            [
                'rejected_by' => auth()->id(),
                'rejection_reason' => $validated['reason'],
                'rejected_at' => now()->toIso8601String(),
            ],
        ));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pembayaran ditolak.']);

        return back();
    }

    public function proof(Payment $payment): BinaryFileResponse
    {
        $path = $payment->raw_payload['proof_path'] ?? null;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }
}
