<?php

namespace Modules\Events\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Events\Contracts\SellableTicketTypes;
use Modules\Events\Models\Event;
use Modules\Events\Policies\EventPolicy;
use Modules\Events\Services\DatabaseSellableTicketTypes;
use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class EventsServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Events';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'events';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     *
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }

    public function boot(): void
    {
        Gate::policy(Event::class, EventPolicy::class);
        parent::boot();
    }

    public function register(): void
    {
        parent::register();
        $this->app->singleton(
            SellableTicketTypes::class,
            DatabaseSellableTicketTypes::class
        );

    }
}
