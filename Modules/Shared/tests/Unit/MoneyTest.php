<?php

declare(strict_types=1);


use Modules\Shared\ValueObjects\Money;

it('creates IRR money via named constructor',function (){

    $money = Money::irr(500_000);

    expect($money->amount)->toBe(500_000)
        ->and($money->currency)->toBe('IRR');
});

