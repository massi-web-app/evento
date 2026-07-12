<?php

declare(strict_types=1);

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Catalog\Services\CategoryReadService;

final class CategoryController extends Controller
{
    public function tree(CategoryReadService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->tree()
        ])->header('Cache-Control', 'public', 'max-age=300');

    }

}
