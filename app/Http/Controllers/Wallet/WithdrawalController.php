<?php

namespace App\Http\Controllers\Wallet;

use App\Actions\Wallet\RequestWithdrawal;
use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class WithdrawalController extends Controller
{
    public function store(Request $request, RequestWithdrawal $withdraw): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'bank_account_id' => ['required', 'integer', Rule::exists('bank_accounts', 'id')->where('user_id', $user->id)],
        ], [], ['amount' => 'nominal', 'bank_account_id' => 'rekening']);

        $withdraw->handle($user, BankAccount::query()->whereKey((int) $validated['bank_account_id'])->firstOrFail(), (int) $validated['amount']);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Penarikan diajukan. Admin akan memprosesnya.']);

        return back();
    }
}
