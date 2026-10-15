<?php

namespace App\Http\Controllers\Trader;

use App\Actions\Signal\CancelSignal;
use App\Actions\Signal\PublishSignal;
use App\Enums\OrderType;
use App\Enums\SignalDirection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Trader\PublishSignalRequest;
use App\Models\Instrument;
use App\Models\Signal;
use App\Models\Subscription;
use App\Models\TraderProfile;
use App\Models\User;
use App\Support\Settings;
use App\Support\Signal\SignalQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SignalController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = SignalQuery::filters($request);

        return Inertia::render('trader/signals/index', [
            'filters' => $filters,
            'statusOptions' => SignalQuery::statusOptions(),
            'signals' => SignalQuery::paginate(
                Signal::query()->where('trader_id', $this->user($request)->id),
                $filters,
                $request,
            ),
        ]);
    }

    public function create(Request $request, PublishSignal $publish): Response
    {
        $trader = $this->user($request);

        return Inertia::render('trader/signals/create', [
            'instruments' => Instrument::query()->where('is_active', true)->orderBy('category')->orderBy('symbol')->get()
                ->map(fn (Instrument $instrument) => [
                    'id' => $instrument->id,
                    'symbol' => $instrument->symbol,
                    'name' => $instrument->name,
                    'digits' => $instrument->digits,
                    'pip_size' => $instrument->pip_size,
                    'spread_pips' => $instrument->spread_pips,
                    'min_sl_pips' => $instrument->min_sl_pips,
                ])->values()->all(),
            'directions' => SignalDirection::options(),
            'orderTypes' => OrderType::options(),
            'rules' => [
                'min_rr' => Settings::get('signal.min_rr'),
                'max_active' => Settings::get('signal.max_active_per_trader'),
                'open_count' => Signal::open()->where('trader_id', $trader->id)->count(),
                'pending_expiry_hours' => Settings::get('signal.pending_expiry_hours'),
                'max_duration_days' => Settings::get('signal.max_duration_days'),
                'min_analysis_chars' => Settings::get('signal.min_analysis_chars'),
                'chart_max_mb' => Settings::get('signal.chart_max_mb'),
            ],
            'canPublishPremium' => (TraderProfile::where('user_id', $trader->id)->first()?->rank->can_sell_premium ?? false)
                || Subscription::query()->where('trader_id', $trader->id)->currentlyActive()->exists(),
            'blockReason' => $publish->blockReason($trader),
            'riskDisclaimer' => Settings::get('content.risk_disclaimer'),
        ]);
    }

    public function store(PublishSignalRequest $request, PublishSignal $publish): RedirectResponse
    {
        $publish->handle(
            $this->user($request),
            $request->safe()->except('chart_image'),
            $request->file('chart_image'),
            $request->ip(),
            $request->userAgent(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Sinyal dipublish dan mulai dinilai otomatis.']);

        return to_route('trader.signals.index');
    }

    public function cancel(Request $request, Signal $signal, CancelSignal $cancel): RedirectResponse
    {
        Gate::authorize('cancel', $signal);

        $cancel->handle($signal);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Sinyal dibatalkan.']);

        return to_route('trader.signals.index');
    }

    private function user(Request $request): User
    {
        /** @var User */
        return $request->user();
    }
}
