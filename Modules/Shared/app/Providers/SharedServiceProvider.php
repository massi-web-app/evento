<?php

declare(strict_types=1);

namespace Modules\Shared\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Shared\Contracts\Clock;
use Modules\Shared\Support\SystemClock;

class SharedServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void {
        $this->app->singleton(Clock::class, SystemClock::class);

    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }
}
