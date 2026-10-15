<?php

namespace App\Providers;

use App\Services\Payments\MidtransPaymentGateway;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\SimulatedPaymentGateway;
use App\Services\PriceFeed\PriceFeed;
use App\Services\PriceFeed\SimulatedPriceFeed;
use App\Services\PriceFeed\TwelveDataPriceFeed;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PriceFeed::class, function (): PriceFeed {
            $driver = config('services.price_feed.driver');

            if ($driver === 'simulated' && $this->app->isProduction()) {
                throw new InvalidArgumentException('The simulated price feed must not be used in production.');
            }

            return match ($driver) {
                'simulated' => new SimulatedPriceFeed((array) config('services.price_feed.simulated.base_prices')),
                'twelvedata' => new TwelveDataPriceFeed(
                    (string) config('services.price_feed.twelvedata.key'),
                    (string) config('services.price_feed.twelvedata.base_url'),
                ),
                default => throw new InvalidArgumentException("Unknown price feed driver [{$driver}]."),
            };
        });

        $this->app->singleton(PaymentGateway::class, function (): PaymentGateway {
            $driver = config('services.payments.driver');

            if ($driver === 'simulated' && $this->app->isProduction()) {
                throw new InvalidArgumentException('The simulated payment gateway must not be used in production.');
            }

            return match ($driver) {
                'simulated' => new SimulatedPaymentGateway,
                'midtrans' => new MidtransPaymentGateway(
                    (string) config('services.payments.midtrans.server_key'),
                    (bool) config('services.payments.midtrans.production'),
                ),
                default => throw new InvalidArgumentException("Unknown payment driver [{$driver}]."),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimits();
    }

    /**
     * Abuse protection for write-heavy member actions (docs/logic/compliance.md → Anti-Fraud & Audit).
     */
    protected function configureRateLimits(): void
    {
        $perUser = fn (int $perMinute) => fn (Request $request) => Limit::perMinute($perMinute)->by($request->user()?->id ?: $request->ip());

        RateLimiter::for('follows', $perUser(30));
        RateLimiter::for('signals', $perUser(10));
        RateLimiter::for('checkout', $perUser(10));
        RateLimiter::for('withdrawals', $perUser(5));
        RateLimiter::for('applications', $perUser(5));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
