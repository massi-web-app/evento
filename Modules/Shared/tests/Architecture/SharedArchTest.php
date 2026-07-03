<?php

declare(strict_types=1);

arch('strict types everywhere')
    ->expect('Modules\Shared')
    ->toUseStrictTypes();

arch('value objects are final and immutable')
    ->expect('Modules\Shared\ValueObjects')
    ->toBeFinal()
    ->toBeReadonly();

arch('shared kernel is framework-light')
    ->expect('Modules\Shared\ValueObjects')
    ->not->toUse([
        'Illuminate\Database',
        'Illuminate\Http',
    ]);

arch('no debug leftovers')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();
