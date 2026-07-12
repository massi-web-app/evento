<?php

declare(strict_types=1);

namespace Modules\Catalog\Exceptions;

use RuntimeException;

final class CategoryCycleException extends RuntimeException
{

    public static function wouldCreateCycle(string $slug): self
    {
        return new self("Moving category [{$slug}] under its own descendant would create a cycle.");
    }

    public static function tooDeep(int $maxDepth): self
    {
        return new self("Category tree cannot exceed {$maxDepth} levels.");
    }
}
