<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\User;
use App\Models\Withdrawal;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class BankAccountController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'bank_id' => ['required', 'integer', Rule::exists('banks', 'id')->where('is_active', true)],
            'account_number' => ['required', 'regex:/^\d{5,20}$/'],
            'account_name' => ['required', 'string', 'max:100'],
        ], ['account_number.regex' => 'Nomor rekening hanya angka (5–20 digit).'], [
            'bank_id' => 'bank',
            'account_number' => 'nomor rekening',
            'account_name' => 'nama pemilik rekening',
        ]);

        $max = Settings::get('wallet.max_bank_accounts');

        if ($user->bankAccounts()->count() >= $max) {
            throw ValidationException::withMessages(['bank_account' => "Maksimal {$max} rekening. Hapus rekening yang tidak dipakai dulu."]);
        }

        $user->bankAccounts()->create($validated);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Rekening ditambahkan.']);

        return back();
    }

    public function destroy(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        abort_unless($bankAccount->user_id === $request->user()?->id, 403);

        if (Withdrawal::where('bank_account_id', $bankAccount->id)->exists()) {
            throw ValidationException::withMessages(['bank_account' => 'Rekening yang pernah dipakai untuk penarikan tidak bisa dihapus.']);
        }

        $bankAccount->delete();

        return back();
    }
}
