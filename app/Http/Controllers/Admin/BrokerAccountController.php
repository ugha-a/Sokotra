<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Broker\ReviewBrokerAccount;
use App\Enums\BrokerAccountStatus;
use App\Http\Controllers\Controller;
use App\Models\BrokerAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BrokerAccountController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->validate(['status' => ['nullable', Rule::enum(BrokerAccountStatus::class)]])['status'] ?? null;

        return Inertia::render('admin/broker-accounts/index', [
            'status' => $status,
            'accounts' => BrokerAccount::query()
                ->with(['user:id,name,email', 'broker:id,name'])
                ->when($status, fn ($query, string $value) => $query->where('status', $value))
                ->orderByRaw("case when status = 'submitted' then 0 else 1 end")
                ->latest('id')
                ->paginate(25)
                ->withQueryString()
                ->through(fn (BrokerAccount $account) => [
                    'id' => $account->id,
                    'user' => ['name' => $account->user->name, 'email' => $account->user->email],
                    'broker' => $account->broker->name,
                    'account_login' => $account->account_login,
                    'account_type' => $account->account_type,
                    'status' => $account->status->value,
                    'status_label' => $account->status->label(),
                    'notes' => $account->notes,
                    'created_at' => $account->created_at?->toIso8601String(),
                ]),
        ]);
    }

    public function link(Request $request, BrokerAccount $account, ReviewBrokerAccount $review): RedirectResponse
    {
        $review->link($this->admin($request), $account);

        return back();
    }

    public function reject(Request $request, BrokerAccount $account, ReviewBrokerAccount $review): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'max:255']], [], ['reason' => 'alasan'])['reason'];
        $review->reject($this->admin($request), $account, $reason);

        return back();
    }

    private function admin(Request $request): User
    {
        /** @var User */
        return $request->user();
    }
}
