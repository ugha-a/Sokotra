<?php

namespace App\Actions\Wallet;

use App\Enums\KycStatus;
use App\Enums\WalletBucket;
use App\Enums\WalletTransactionType;
use App\Enums\WithdrawalStatus;
use App\Exceptions\InsufficientBalance;
use App\Models\BankAccount;
use App\Models\KycVerification;
use App\Models\User;
use App\Models\Withdrawal;
use App\Support\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * docs/logic/wallet-ledger.md → Withdraw → Syarat request.
 */
class RequestWithdrawal
{
    public function __construct(private MoveBetweenBuckets $move) {}

    public function handle(User $user, BankAccount $account, int $amount): Withdrawal
    {
        $kyc = KycVerification::where('user_id', $user->id)->first();

        if ($kyc?->status !== KycStatus::Verified) {
            throw ValidationException::withMessages(['withdrawal' => 'Verifikasi identitas (KYC) wajib sebelum menarik saldo.']);
        }

        if (Withdrawal::where('user_id', $user->id)->whereIn('status', [WithdrawalStatus::Requested, WithdrawalStatus::Processing])->exists()) {
            throw ValidationException::withMessages(['withdrawal' => 'Masih ada penarikan yang sedang diproses.']);
        }

        $min = Settings::get('wallet.min_withdraw');
        $fee = Settings::get('wallet.withdraw_fee');

        if ($amount < $min) {
            throw ValidationException::withMessages(['amount' => 'Minimum penarikan Rp'.number_format($min, 0, ',', '.').'.']);
        }

        return DB::transaction(function () use ($user, $account, $amount, $fee, $kyc) {
            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'bank_account_id' => $account->id,
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => $amount,
                'status' => WithdrawalStatus::Requested,
                'name_mismatch' => self::normalizeName($account->account_name) !== self::normalizeName($kyc->full_name),
            ]);

            try {
                $this->move->handle($user, WalletTransactionType::WithdrawLock, WalletBucket::Available, WalletBucket::Locked, $withdrawal->total(), $withdrawal->reference(), 'Penarikan ke '.$account->bank->name);
            } catch (InsufficientBalance) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo tersedia tidak cukup untuk nominal ini ditambah biaya Rp'.number_format($fee, 0, ',', '.').'.',
                ]);
            }

            return $withdrawal;
        });
    }

    public static function normalizeName(string $name): string
    {
        return (string) preg_replace('/\s+/', ' ', mb_strtoupper(trim($name)));
    }
}
