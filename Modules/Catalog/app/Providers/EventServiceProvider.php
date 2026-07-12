<?php

namespace Modules\Catalog\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Catalog\Events\CategoryTreeChanged;
use Modules\Catalog\Listeners\FlushCategoryTreeCache;
use Modules\Events\Models\Event;
use Modules\Events\Policies\EventPolicy;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        CategoryTreeChanged::class => [
            FlushCategoryTreeCache::class,
        ]
    ];


    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void
    {
    }

    public function boot(): void
    {
        Gate::policy(Event::class, EventPolicy::class);
        parent::boot();
    }
}
