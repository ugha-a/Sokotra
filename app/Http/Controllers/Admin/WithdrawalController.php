<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Wallet\ReviewWithdrawal;
use App\Enums\WithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class WithdrawalController extends Controller
{
    public function index(Request $request): Response
    {
        $status = WithdrawalStatus::from($request->validate(['status' => ['nullable', Rule::enum(WithdrawalStatus::class)]])['status'] ?? 'requested');
        $counts = Withdrawal::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return Inertia::render('admin/withdrawals/index', [
            'status' => $status->value,
            'statuses' => array_map(fn (WithdrawalStatus $case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'count' => (int) ($counts[$case->value] ?? 0),
            ], WithdrawalStatus::cases()),
            'withdrawals' => Withdrawal::query()
                ->with(['user.kycVerification', 'user.wallet', 'bankAccount.bank'])
                ->where('status', $status)
                ->oldest('id')
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Withdrawal $withdrawal) => [
                    'id' => $withdrawal->id,
                    'user' => ['name' => $withdrawal->user->name, 'email' => $withdrawal->user->email],
                    'kyc_name' => $withdrawal->user->kycVerification?->full_name,
                    'bank' => $withdrawal->bankAccount->bank->name,
                    'account_number' => $withdrawal->bankAccount->maskedNumber(),
                    'account_name' => $withdrawal->bankAccount->account_name,
                    'name_mismatch' => $withdrawal->name_mismatch,
                    'amount' => $withdrawal->amount,
                    'fee' => $withdrawal->fee,
                    'status' => $withdrawal->status->value,
                    'status_label' => $withdrawal->status->label(),
                    'rejection_reason' => $withdrawal->rejection_reason,
                    'disbursement_reference' => $withdrawal->disbursement_reference,
                    'created_at' => $withdrawal->created_at?->toIso8601String(),
                ]),
        ]);
    }

    public function approve(Request $request, Withdrawal $withdrawal, ReviewWithdrawal $review): RedirectResponse
    {
        $review->approve($this->admin($request), $withdrawal);

        return $this->back('Penarikan disetujui. Lakukan transfer lalu tandai dibayar.');
    }

    public function paid(Request $request, Withdrawal $withdrawal, ReviewWithdrawal $review): RedirectResponse
    {
        $reference = $request->validate(['reference' => ['required', 'string', 'max:100']], [], ['reference' => 'referensi transfer'])['reference'];
        $review->markPaid($this->admin($request), $withdrawal, $reference);

        return $this->back('Penarikan ditandai dibayar.');
    }

    public function reject(Request $request, Withdrawal $withdrawal, ReviewWithdrawal $review): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'max:500']], [], ['reason' => 'alasan'])['reason'];
        $review->reject($this->admin($request), $withdrawal, $reason);

        return $this->back('Penarikan dibatalkan dan saldo dikembalikan.');
    }

    private function admin(Request $request): User
    {
        /** @var User */
        return $request->user();
    }

    private function back(string $message): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return back();
    }
}
