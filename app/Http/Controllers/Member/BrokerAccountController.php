<?php

namespace App\Http\Controllers\Member;

use App\Enums\BrokerAccountStatus;
use App\Http\Controllers\Controller;
use App\Models\Broker;
use App\Models\BrokerAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BrokerAccountController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('member/broker-accounts', [
            'accounts' => BrokerAccount::query()->with('broker')->where('user_id', $request->user()?->id)->latest('id')->get()
                ->map(fn (BrokerAccount $account) => [
                    'id' => $account->id,
                    'broker' => $account->broker->name,
                    'account_login' => $account->account_login,
                    'account_type' => $account->account_type,
                    'status' => $account->status->value,
                    'status_label' => $account->status->label(),
                    'notes' => $account->notes,
                ])->values()->all(),
            'brokers' => Broker::query()->where('is_active', true)->orderBy('sort_order')->get()
                ->map(fn (Broker $broker) => ['value' => $broker->id, 'label' => $broker->name, 'account_types' => array_column($broker->account_types, 'name')])
                ->values()->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'broker_id' => ['required', 'integer', Rule::exists('brokers', 'id')->where('is_active', true)],
            'account_login' => ['required', 'string', 'max:40', Rule::unique('broker_accounts')->where('broker_id', $request->integer('broker_id'))],
            'account_type' => ['nullable', 'string', 'max:50'],
        ], ['account_login.unique' => 'Nomor akun ini sudah terdaftar.'], ['broker_id' => 'broker', 'account_login' => 'nomor akun', 'account_type' => 'tipe akun']);

        BrokerAccount::create([...$validated, 'user_id' => $request->user()?->id, 'status' => BrokerAccountStatus::Submitted]);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Akun dikirim untuk diverifikasi.']);

        return back();
    }
}
