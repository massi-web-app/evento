<?php

namespace Modules\Orders\Providers;

use Modules\Orders\Contracts\CapacityCounter;
use Modules\Orders\Services\InMemoryCapacityCounter;
use Modules\Orders\Services\RedisCapacityCounter;
use Nwidart\Modules\Support\ModuleServiceProvider;

class OrdersServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Orders';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'orders';

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

    public function register(): void
    {

        parent::register();
        $this->app->singleton(CapacityCounter::class, function (): CapacityCounter {
            return $this->app->environment('testing')
                ? new InMemoryCapacityCounter()
                : new RedisCapacityCounter();
        });
    }
}
