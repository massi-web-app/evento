<?php

declare(strict_types=1);

namespace Modules\Events\Queries;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Modules\Events\Enums\EventFormat;
use Modules\Events\Enums\EventStatus;
use Modules\Events\Models\Event;

final class PublicEventListQuery
{

    private const int PER_PAGE = 20;
    public function paginate(
        ?int $cityId = null,
        ?int $categoryId = null,
        ?EventFormat $format = null,
        ?CarbonImmutable $from = null,
        ?CarbonImmutable $until = null,
        ?string $search = null,
    ): CursorPaginator {
        return Event::query()
            ->where('status', EventStatus::Published)
            ->where('starts_at', '>', now())
            ->when($cityId !== null,
                fn (Builder $q): Builder => $q->where('city_id', $cityId))
            ->when($categoryId !== null,
                fn (Builder $q): Builder => $q->where('category_id', $categoryId))
            ->when($format !== null,
                fn (Builder $q): Builder => $q->where('format', $format))
            ->when($from !== null,
                fn (Builder $q): Builder => $q->where('starts_at', '>=', $from))
            ->when($until !== null,
                fn (Builder $q): Builder => $q->where('starts_at', '<=', $until))
            ->when($search !== null && $search !== '',
                fn (Builder $q): Builder => $q->where('title', 'like', '%' . addcslashes((string) $search, '%_\\') . '%'))
            ->orderBy('starts_at')
            ->orderBy('id')                       // tie-breaker — لازمهٔ cursor پایدار
            ->cursorPaginate(self::PER_PAGE);
    }


}
