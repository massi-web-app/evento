<?php

declare(strict_types=1);

/**
 * قانون طلایی ۱: هیچ ماژولی به internals ماژول دیگر دست نمی‌زند.
 * لایه‌های PUBLIC هر ماژول: Contracts, DTOs, Events, Enums, Exceptions.
 * همهٔ اینها به‌جز Shared که عمداً برای همه باز است.
 */
$modules = []; // با اضافه شدن هر ماژول، به این لیست اضافه می‌شود

foreach ($modules as $module) {
    arch("{$module}: models never cross the boundary")
        ->expect("Modules\\{$module}\\Models")
        ->toOnlyBeUsedIn("Modules\\{$module}");

    arch("{$module}: repositories are internal")
        ->expect("Modules\\{$module}\\Repositories")
        ->toOnlyBeUsedIn("Modules\\{$module}");

    arch("{$module}: services are internal")
        ->expect("Modules\\{$module}\\Services")
        ->toOnlyBeUsedIn("Modules\\{$module}");

    arch("{$module}: http layer is internal")
        ->expect("Modules\\{$module}\\Http")
        ->toOnlyBeUsedIn("Modules\\{$module}");
}
