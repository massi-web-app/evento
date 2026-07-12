<?php

declare(strict_types=1);

namespace Modules\Catalog\Listeners;

use Modules\Catalog\Events\CategoryTreeChanged;
use Modules\Catalog\Services\CategoryReadService;

final readonly class FlushCategoryTreeCache
{

    public function __construct(
        private CategoryReadService $readService,
    )
    {
    }

    public function handle(CategoryTreeChanged $event): void
    {
        $this->readService->forget();
    }

}
