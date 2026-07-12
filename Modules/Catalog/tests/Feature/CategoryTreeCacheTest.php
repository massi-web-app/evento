<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Services\CategoryReadService;
use Modules\Catalog\Services\CategoryTreeService;

uses(RefreshDatabase::class);

it('serves the tree from cache on the second call', function (): void {
    $treeService = app(CategoryTreeService::class);
    $concerts = $treeService->create('کنسرت');
    $treeService->create('موسیقی سنتی', $concerts);

    $read = app(CategoryReadService::class);
    $first = $read->tree();

    DB::enableQueryLog();
    $second = $read->tree();

    expect(DB::getQueryLog())->toBeEmpty()
        ->and($second)->toBe($first)
        ->and($second[0]['children'][0]['slug'])->toBe($first[0]['children'][0]['slug']);
});

it('flushes the cache when the tree changes', function (): void {
    $treeService = app(CategoryTreeService::class);
    $treeService->create('کنسرت');

    $read = app(CategoryReadService::class);
    expect($read->tree())->toHaveCount(1);

    $treeService->create('تئاتر');   // event → listener → forget

    expect($read->tree())->toHaveCount(2);
});

it('excludes inactive categories from the public tree', function (): void {
    $treeService = app(CategoryTreeService::class);
    $c = $treeService->create('کنسرت');
    $c->forceFill(['is_active' => false])->save();
    app(CategoryReadService::class)->forget();   // تا مسیر ادمینیِ deactivate ساخته شود، دستی

    expect(app(CategoryReadService::class)->tree())->toHaveCount(0);
});
