<?php

namespace Modules\Settings\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Settings\Contracts\SettingsReader;
use Modules\Settings\Services\SettingsService;
use Nwidart\Modules\Support\ModuleServiceProvider;

class SettingsServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Settings';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'settings';

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
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }

    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
        $this->app->singleton(SettingsReader::class, SettingsService::class);

    }
}
