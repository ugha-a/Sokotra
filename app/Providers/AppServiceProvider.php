<?php

namespace App\Providers;

use App\Services\Payments\PaymentGateway;
use App\Services\Payments\SimulatedPaymentGateway;
use App\Services\PriceFeed\PriceFeed;
use App\Services\PriceFeed\SimulatedPriceFeed;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
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
