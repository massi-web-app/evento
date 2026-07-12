<?php

declare(strict_types=1);

namespace Modules\Catalog\Services;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Catalog\Events\CategoryTreeChanged;
use Modules\Catalog\Exceptions\CategoryCycleException;
use Modules\Catalog\Models\Category;

final class CategoryTreeService
{

    private const int MAX_DEPTH = 3;


    public function create(string $name, ?Category $parent = null, ?string $icon = null): Category
    {
        return DB::transaction(function () use ($name, $parent, $icon): Category {
            $category = Category::query()->create([
                'name' => $name,
                'slug' => $this->uniqueSlug($name),
                'parent_id' => $parent?->id,
                'icon_url' => $icon,
                'depth' => $parent !== null ? $parent->depth + 1 : 0,
            ]);

            if ($category->depth > self::MAX_DEPTH) {
                throw CategoryCycleException::tooDeep(self::MAX_DEPTH);
            }

            $category->forceFill([
                'path' => ($parent?->path ?? '/') . $category->id . '/',
            ])->save();

            event(new CategoryTreeChanged($category->slug, 'created'));

            return $category;
        });

    }

    public function move(Category $category, ?Category $newParent): void
    {
        if ($newParent !== null && str_starts_with($newParent->path, $category->path)) {
            throw CategoryCycleException::wouldCreateCycle($category->slug);
        }

        DB::transaction(function () use ($category, $newParent): void {

            $oldPath = $category->path;
            $newDepth = $newParent !== null ? $newParent->depth + 1 : 0;

            $category->forceFill([
                'parent_id' => $newParent?->id,
                'depth' => $newDepth,
                'path' => ($newParent?->path ?? '/') . $category->id . '/',
            ])->save();

            $depthDelta = $newDepth - (int) substr_count($oldPath, '/') + 2;

            Category::query()
                ->where('path', 'like', $oldPath . '%')
                ->whereKeyNot($category->id)
                ->update([
                    'path' => DB::raw("CONCAT('" . $category->path . "', SUBSTRING(path, " . (strlen($oldPath) + 1) . '))'),
                    'depth' => DB::raw('depth + (' . $depthDelta . ')'),
                ]);
        });

        event(new CategoryTreeChanged($category->slug, 'moved'));

    }

    public function descendantsOf(Category $category): Collection
    {
        return Category::query()
            ->where('path', 'like', $category->path . '%')
            ->whereKeyNot($category->id)
            ->orderBy('path')
            ->get();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: Str::lower(Str::random(8));
        $slug = $base;

        for ($i = 2; Category::query()->where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }



}
