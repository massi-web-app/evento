<?php

declare(strict_types=1);

namespace Modules\Catalog\Events;

use Modules\Catalog\Models\Category;
use Modules\Shared\Events\DomainEvent;

final readonly class CategoryTreeChanged extends DomainEvent
{
    public function __construct(
        public  string $categorySlug,
        public  string $action,   // created | moved | updated | deactivated
    ) {
        parent::__construct();
    }

}
