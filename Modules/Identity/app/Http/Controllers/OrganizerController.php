<?php

declare(strict_types=1);

namespace Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Identity\Http\Requests\RegisterOrganizerRequest;
use Modules\Identity\Http\Resources\OrganizerResource;
use Modules\Identity\Services\OrganizerRegistrationService;

final class OrganizerController extends Controller
{
    public function store(
        RegisterOrganizerRequest $request,
        OrganizerRegistrationService $service,
    ): JsonResponse {
        $organizer = $service->register(
            user: $request->user(),
            brandName: $request->brandName(),
            type: $request->type(),
            bio: $request->bio(),
        );

        return OrganizerResource::make($organizer)
            ->response()
            ->setStatusCode(201);
    }
}
