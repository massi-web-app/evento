<?php

declare(strict_types=1);

namespace Modules\Catalog\Services;
use Illuminate\Contracts\Cache\Repository as Cache;
use Modules\Catalog\Models\Category;

final readonly class CategoryReadService
{
    private const string CACHE_KEY = 'catalog:category:tree';
    private const int TTL_SECONDS = 86400;


    public function __construct(
        private Cache $cache,
    )
    {
    }

    public function tree(): array
    {
        return $this->cache->remember(
            self::CACHE_KEY,
            self::TTL_SECONDS,
            fn (): array => $this->buildTree(),
        );
    }

    public function forget(): void
    {
        $this->cache->forget(self::CACHE_KEY);
    }

    private function buildTree(): array
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('depth')
            ->orderBy('sort_order')
            ->get(['id', 'parent_id', 'name', 'slug', 'icon_url']);

        // یک query، مونتاژ در حافظه — بدون recursion دیتابیسی
        $byParent = $categories->groupBy('parent_id');

        $build = function (?int $parentId) use (&$build, $byParent): array {
            return $byParent->get($parentId, collect())
                ->map(fn (Category $c): array => [
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'icon_url' => $c->icon_url,
                    'children' => $build($c->id),
                ])
                ->values()
                ->all();
        };

        return $build(null);
    }


}
