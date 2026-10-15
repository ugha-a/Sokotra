<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Payroll\ApprovePayroll;
use App\Actions\Payroll\GeneratePayrollDraft;
use App\Actions\Payroll\HoldPayrollItem;
use App\Enums\PayrollPeriodStatus;
use App\Http\Controllers\Controller;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Support\BusinessCalendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PayrollController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/payroll/index', [
            'periods' => PayrollPeriod::query()->withCount([
                'items',
                'items as eligible_count' => fn ($query) => $query->where('eligible', true),
            ])->orderByDesc('month')->get()->map(fn (PayrollPeriod $period) => [
                'id' => $period->id,
                'month' => $period->month,
                'label' => $period->label(),
                'status' => $period->status->value,
                'status_label' => $period->status->label(),
                'total_amount' => $period->total_amount,
                'items_count' => $period->getAttribute('items_count'),
                'eligible_count' => $period->getAttribute('eligible_count'),
                'generated_at' => $period->generated_at?->toIso8601String(),
                'approved_at' => $period->approved_at?->toIso8601String(),
            ])->values()->all(),
            'defaultMonth' => BusinessCalendar::now()->subMonthNoOverflow()->format('Y-m'),
        ]);
    }

    public function generate(Request $request, GeneratePayrollDraft $generate): RedirectResponse
    {
        $month = $request->validate(['month' => ['required', 'date_format:Y-m']], [], ['month' => 'bulan'])['month'];
        $period = $generate->handle($month);
        Inertia::flash('toast', ['type' => 'success', 'message' => "Draft payroll {$period->label()} dibuat."]);

        return to_route('admin.payroll.show', $period);
    }

    public function show(PayrollPeriod $period): Response
    {
        return Inertia::render('admin/payroll/show', [
            'period' => [
                'id' => $period->id,
                'month' => $period->month,
                'label' => $period->label(),
                'status' => $period->status->value,
                'status_label' => $period->status->label(),
                'total_amount' => $period->total_amount,
                'generated_at' => $period->generated_at?->toIso8601String(),
                'approved_at' => $period->approved_at?->toIso8601String(),
            ],
            'items' => $period->items()
                ->with(['user:id,name,email', 'user.traderProfile:id,user_id,display_name,slug', 'rank:id,name,color'])
                ->orderByDesc('eligible')->orderByDesc('total_amount')
                ->get()
                ->map(fn (PayrollItem $item) => [
                    ...$item->summary(),
                    'trader' => [
                        'name' => $item->user->traderProfile->display_name ?? $item->user->name,
                        'email' => $item->user->email,
                    ],
                    'rank' => $item->rank ? ['name' => $item->rank->name, 'color' => $item->rank->color] : null,
                ])->values()->all(),
            'canApprove' => $period->status === PayrollPeriodStatus::Draft,
        ]);
    }

    public function approve(Request $request, PayrollPeriod $period, ApprovePayroll $approve): RedirectResponse
    {
        $approve->handle($this->admin($request), $period);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Payroll disetujui dan gaji dikreditkan ke wallet.']);

        return back();
    }

    public function hold(Request $request, PayrollItem $item, HoldPayrollItem $hold): RedirectResponse
    {
        $note = $request->validate(['note' => ['required', 'string', 'max:255']], [], ['note' => 'catatan'])['note'];
        $hold->hold($this->admin($request), $item, $note);

        return back();
    }

    public function release(Request $request, PayrollItem $item, HoldPayrollItem $hold): RedirectResponse
    {
        $hold->release($this->admin($request), $item);

        return back();
    }

    private function admin(Request $request): User
    {
        /** @var User */
        return $request->user();
    }
}
