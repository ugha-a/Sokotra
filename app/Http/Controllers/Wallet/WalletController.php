<?php

namespace App\Http\Controllers\Wallet;

use App\Enums\KycStatus;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use App\Support\Settings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WalletController extends Controller
{
    public function show(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $wallet = Wallet::firstOrNew(['user_id' => $user->id], ['available_balance' => 0, 'pending_balance' => 0, 'locked_balance' => 0]);

        return Inertia::render('wallet/show', [
            'wallet' => $wallet->summary(),
            'transactions' => WalletTransaction::query()
                ->where('wallet_id', $wallet->id ?? 0)
                ->latest('id')
                ->paginate(20)
                ->through(fn (WalletTransaction $tx) => [
                    'id' => $tx->id,
                    'type' => $tx->type->value,
                    'type_label' => $tx->type->label(),
                    'bucket' => $tx->bucket->value,
                    'bucket_label' => $tx->bucket->label(),
                    'amount' => $tx->amount,
                    'balance_after' => $tx->balance_after,
                    'description' => $tx->description,
                    'available_at' => $tx->available_at?->toIso8601String(),
                    'created_at' => $tx->created_at?->toIso8601String(),
                ]),
            'bankAccounts' => $user->bankAccounts()->with('bank')->get()->map(fn (BankAccount $account) => $account->summary())->values()->all(),
            'banks' => Bank::query()->where('is_active', true)->orderBy('type')->orderBy('name')->get()
                ->map(fn (Bank $bank) => ['value' => $bank->id, 'label' => $bank->name])->values()->all(),
            'withdrawals' => Withdrawal::query()->where('user_id', $user->id)->with('bankAccount.bank')->latest('id')->limit(10)->get()
                ->map(fn (Withdrawal $withdrawal) => [
                    'id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                    'fee' => $withdrawal->fee,
                    'status' => $withdrawal->status->value,
                    'status_label' => $withdrawal->status->label(),
                    'bank_account' => $withdrawal->bankAccount->summary(),
                    'rejection_reason' => $withdrawal->rejection_reason,
                    'disbursement_reference' => $withdrawal->disbursement_reference,
                    'created_at' => $withdrawal->created_at?->toIso8601String(),
                ])->values()->all(),
            'rules' => [
                'min_withdraw' => Settings::get('wallet.min_withdraw'),
                'withdraw_fee' => Settings::get('wallet.withdraw_fee'),
                'max_bank_accounts' => Settings::get('wallet.max_bank_accounts'),
                'hold_period_days' => Settings::get('wallet.hold_period_days'),
            ],
            'kycVerified' => $user->kycVerification?->status === KycStatus::Verified,
        ]);
    }
}
