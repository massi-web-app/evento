<?php

declare(strict_types=1);
namespace Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Events\Enums\EventFormat;
use Modules\Events\Http\Requests\ListEventsRequest;
use Modules\Events\Http\Resources\PublicEventResource;
use Modules\Events\Queries\PublicEventListQuery;

final class PublicEventController extends Controller
{
    public function index(ListEventsRequest $request, PublicEventListQuery $query): AnonymousResourceCollection
    {
        $format = $request->validated('format');
        $from = $request->validated('from');
        $until = $request->validated('until');

        $events = $query->paginate(
            cityId: $request->validated('city_id') !== null ? (int) $request->validated('city_id') : null,
            categoryId: $request->validated('category_id') !== null ? (int) $request->validated('category_id') : null,
            format: $format !== null ? EventFormat::from((int) $format) : null,
            from: $from !== null ? CarbonImmutable::parse((string) $from) : null,
            until: $until !== null ? CarbonImmutable::parse((string) $until) : null,
            search: $request->validated('q'),
        );

        return PublicEventResource::collection($events);
    }

}
