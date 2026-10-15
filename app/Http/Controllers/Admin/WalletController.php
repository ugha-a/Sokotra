<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Wallet\AdjustWallet;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WalletController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/wallets/index', [
            'wallets' => Wallet::query()
                ->with('user:id,name,email')
                ->orderByDesc('available_balance')
                ->paginate(25)
                ->through(fn (Wallet $wallet) => [
                    'user' => ['id' => $wallet->user->id, 'name' => $wallet->user->name, 'email' => $wallet->user->email],
                    ...$wallet->summary(),
                ]),
            'totals' => [
                'available_balance' => (int) Wallet::sum('available_balance'),
                'pending_balance' => (int) Wallet::sum('pending_balance'),
                'locked_balance' => (int) Wallet::sum('locked_balance'),
            ],
        ]);
    }

    public function adjust(Request $request, User $user, AdjustWallet $adjust): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'not_in:0'],
            'note' => ['required', 'string', 'max:255'],
        ], [], ['amount' => 'nominal', 'note' => 'catatan']);

        /** @var User $admin */
        $admin = $request->user();
        $adjust->handle($admin, $user, (int) $validated['amount'], $validated['note']);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Saldo dikoreksi.']);

        return back();
    }
}
