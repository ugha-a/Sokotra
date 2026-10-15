<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Fraud\ResolveFraudFlag;
use App\Enums\FraudFlagStatus;
use App\Http\Controllers\Controller;
use App\Models\FraudFlag;
use App\Models\Signal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FraudFlagController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->validate(['status' => ['nullable', Rule::enum(FraudFlagStatus::class)]])['status'] ?? FraudFlagStatus::Open->value;

        $flags = FraudFlag::query()->where('status', $status)->latest('id')->paginate(20)->withQueryString();
        $users = User::query()->with('traderProfile:id,user_id,display_name')->whereIn('id', $flags->getCollection()->flatMap(fn (FraudFlag $flag) => $flag->subject_user_ids)->unique())->get()->keyBy('id');
        $signals = Signal::query()->with('instrument:id,symbol')->whereIn('id', $flags->getCollection()->flatMap(fn (FraudFlag $flag) => $flag->signal_ids ?? [])->unique())->get()->keyBy('id');

        return Inertia::render('admin/fraud-flags/index', [
            'status' => $status,
            'statuses' => array_map(fn (FraudFlagStatus $case) => ['value' => $case->value, 'label' => $case->label()], FraudFlagStatus::cases()),
            'flags' => $flags->through(fn (FraudFlag $flag) => [
                'id' => $flag->id,
                'type' => $flag->type->value,
                'type_label' => $flag->type->label(),
                'status' => $flag->status->value,
                'status_label' => $flag->status->label(),
                'users' => array_map(fn (int $id) => [
                    'id' => $id,
                    'name' => $users[$id]->traderProfile->display_name ?? $users[$id]->name ?? "#{$id}",
                    'email' => $users[$id]->email ?? null,
                ], $flag->subject_user_ids),
                'signals' => array_map(fn (int $id) => [
                    'id' => $id,
                    'label' => isset($signals[$id]) ? "{$signals[$id]->instrument->symbol} {$signals[$id]->direction->label()} · ".$signals[$id]->published_at->toIso8601String() : "#{$id}",
                ], $flag->signal_ids ?? []),
                'details' => $flag->details,
                'notes' => $flag->notes,
                'created_at' => $flag->created_at?->toIso8601String(),
            ]),
        ]);
    }

    public function confirm(Request $request, FraudFlag $flag, ResolveFraudFlag $resolve): RedirectResponse
    {
        $resolve->confirm($this->admin($request), $flag, $this->notes($request));

        return back();
    }

    public function dismiss(Request $request, FraudFlag $flag, ResolveFraudFlag $resolve): RedirectResponse
    {
        $resolve->dismiss($this->admin($request), $flag, $this->notes($request));

        return back();
    }

    private function notes(Request $request): ?string
    {
        return $request->validate(['notes' => ['nullable', 'string', 'max:255']])['notes'] ?? null;
    }

    private function admin(Request $request): User
    {
        /** @var User */
        return $request->user();
    }
}
