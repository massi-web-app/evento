<?php

namespace Modules\Identity\Providers;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Modules\Identity\Contracts\OrganizerReader;
use Modules\Identity\Contracts\PermissionChecker;
use Modules\Identity\Services\CachedPermissionChecker;
use Modules\Identity\Services\DatabaseOrganizerReader;
use Modules\Identity\Services\DatabasePermissionChecker;
use Nwidart\Modules\Support\ModuleServiceProvider;

class IdentityServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Identity';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'identity';

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

        $this->app->singleton(OrganizerReader::class, DatabaseOrganizerReader::class);

        $this->app->singleton(PermissionChecker::class, function ($app): PermissionChecker {
            return new CachedPermissionChecker(
                inner: new DatabasePermissionChecker,
                cache: $app->make(CacheRepository::class),
            );
        });
    }
}
