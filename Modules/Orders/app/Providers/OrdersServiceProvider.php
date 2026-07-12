<?php

namespace Modules\Orders\Providers;

use Modules\Orders\Console\ExpireOverdueHoldsCommand;
use Modules\Orders\Contracts\CapacityCounter;
use Modules\Orders\Contracts\PaidOrderReader;
use Modules\Orders\Contracts\PaymentGateway;
use Modules\Orders\Services\DatabasePaidOrderReader;
use Modules\Orders\Services\Gateways\FakeGateway;
use Modules\Orders\Services\InMemoryCapacityCounter;
use Modules\Orders\Services\RedisCapacityCounter;
use Nwidart\Modules\Support\ModuleServiceProvider;
use RuntimeException;

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
        if ($this->app->runningInConsole()) {
            $this->commands([ExpireOverdueHoldsCommand::class]);
        }

        $this->app->singleton(PaidOrderReader::class, DatabasePaidOrderReader::class);

        $this->app->singleton(PaymentGateway::class, function (): PaymentGateway {
            return match (config('orders.gateway', 'fake')) {
                'fake' => new FakeGateway(),
                default => throw new RuntimeException('Unknown payment gateway configured.'),
            };
        });
        $this->app->singleton(CapacityCounter::class, function (): CapacityCounter {
            return $this->app->environment('testing')
                ? new InMemoryCapacityCounter()
                : new RedisCapacityCounter();
        });
    }
}
