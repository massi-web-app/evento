<?php

it('omits the currency column in without_currency mode', function (): void {
    // مدل ساختگی سبک روی جدول order_items واقعی سنگین است؛
    // سادگی: همان OrderItem — چون قرارداد دقیقاً برای او شکست
    $item = new \Modules\Orders\Models\OrderItem();
    $cast = new \Modules\Shared\Casts\MoneyCast('without_currency');

    $columns = $cast->set($item, 'unit_amount_snapshot', \Modules\Shared\ValueObjects\Money::irr(1000), []);

    expect($columns)->toBe(['unit_amount_snapshot' => 1000])
        ->and($columns)->not->toHaveKey('currency');
});
