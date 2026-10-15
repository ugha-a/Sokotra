<?php

namespace App\Http\Controllers;

use App\Models\Signal;
use App\Support\Settings;
use App\Support\Signal\SignalQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SignalFeedController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = SignalQuery::filters($request);

        return Inertia::render('signals/index', [
            'filters' => $filters,
            'statusOptions' => SignalQuery::statusOptions(),
            'instrumentOptions' => SignalQuery::instrumentOptions(),
            'signals' => SignalQuery::paginate(Signal::query(), $filters, $request),
            'riskDisclaimer' => Settings::get('content.risk_disclaimer'),
        ]);
    }

    public function chart(Signal $signal): StreamedResponse
    {
        Gate::authorize('viewDetails', $signal);

        abort_unless($signal->chart_image_path && Storage::disk('local')->exists($signal->chart_image_path), 404);

        return Storage::disk('local')->response($signal->chart_image_path, headers: [
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
