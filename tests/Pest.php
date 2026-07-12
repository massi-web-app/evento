<?php

declare(strict_types=1);


use Tests\TestCase;

pest()->extend(TestCase::class)->in(
    'Feature',
    '../Modules/*/tests/Feature',
);

require_once __DIR__ . '/../Modules/Events/tests/Helpers.php';
require_once __DIR__ . '/../Modules/Orders/tests/Helpers.php';
