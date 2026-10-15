<?php

namespace App\Http\Controllers\Public;

use App\Actions\Broker\OpenBrokerAccount;
use App\Http\Controllers\Controller;
use App\Models\Broker;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BrokerController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('public/brokers', [
            'brokers' => Broker::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get()
                ->map(fn (Broker $broker) => [
                    'id' => $broker->id,
                    'name' => $broker->name,
                    'slug' => $broker->slug,
                    'logo_url' => $broker->logo_url,
                    'regulator_license_no' => $broker->regulator_license_no,
                    'website_url' => $broker->website_url,
                    'description' => $broker->description,
                    'account_types' => $broker->account_types,
                ])->values()->all(),
        ]);
    }

    public function open(Request $request, Broker $broker, OpenBrokerAccount $open): RedirectResponse
    {
        abort_unless($broker->is_active, 404);

        /** @var User $user */
        $user = $request->user();

        return redirect()->away($open->handle($user, $broker));
    }
}
