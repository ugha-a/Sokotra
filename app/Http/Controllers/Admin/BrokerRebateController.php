<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Broker\ImportBrokerRebates;
use App\Http\Controllers\Controller;
use App\Models\Broker;
use App\Models\BrokerRebate;
use App\Models\User;
use App\Support\BusinessCalendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BrokerRebateController extends Controller
{
    public function index(Request $request): Response
    {
        $period = $request->validate(['period' => ['nullable', 'date_format:Y-m']])['period']
            ?? BusinessCalendar::now()->subMonthNoOverflow()->format('Y-m');
        $rebates = BrokerRebate::query()->with(['broker:id,name', 'brokerAccount.user:id,name'])->where('period', $period)->orderByDesc('rebate_idr')->get();

        return Inertia::render('admin/broker-rebates/index', [
            'period' => $period,
            'brokers' => Broker::query()->orderBy('name')->get()->map(fn (Broker $broker) => ['value' => $broker->id, 'label' => $broker->name])->values()->all(),
            'rows' => $rebates->map(fn (BrokerRebate $rebate) => [
                'id' => $rebate->id,
                'broker' => $rebate->broker->name,
                'account_login' => $rebate->account_login,
                'member' => $rebate->brokerAccount?->user->name,
                'lots' => (float) $rebate->lots,
                'rebate_usd' => (float) $rebate->rebate_usd,
                'rebate_idr' => $rebate->rebate_idr,
                'matched' => $rebate->broker_account_id !== null,
            ])->values()->all(),
            'totals' => [
                'lots' => round((float) $rebates->sum('lots'), 2),
                'rebate_usd' => round((float) $rebates->sum('rebate_usd'), 2),
                'rebate_idr' => (int) $rebates->sum('rebate_idr'),
                'unmatched' => $rebates->whereNull('broker_account_id')->count(),
            ],
            'byBroker' => $rebates->groupBy('broker_id')->map(fn ($rows) => [
                'broker' => $rows->first()->broker->name,
                'rebate_idr' => (int) $rows->sum('rebate_idr'),
                'lots' => round((float) $rows->sum('lots'), 2),
            ])->values()->all(),
        ]);
    }

    public function import(Request $request, ImportBrokerRebates $import): RedirectResponse
    {
        $validated = $request->validate([
            'broker_id' => ['required', 'integer', 'exists:brokers,id'],
            'period' => ['required', 'date_format:Y-m'],
            'fx_rate' => ['required', 'numeric', 'gt:0'],
            'file' => ['required', 'file', 'extensions:csv,txt', 'max:5120'],
        ], [], ['broker_id' => 'broker', 'period' => 'periode', 'fx_rate' => 'kurs USD/IDR', 'file' => 'file CSV']);

        /** @var User $admin */
        $admin = $request->user();
        $summary = $import->handle($admin, Broker::query()->whereKey((int) $validated['broker_id'])->firstOrFail(), $validated['period'], (float) $validated['fx_rate'], $request->file('file'));

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$summary['rows']} baris diimpor, {$summary['unmatched']} belum cocok dengan akun terhubung."]);

        return to_route('admin.broker-rebates.index', ['period' => $validated['period']]);
    }
}
