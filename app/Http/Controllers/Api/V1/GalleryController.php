<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CmsGallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 10), 50);

        $galleries = CmsGallery::published()
            ->with(['category', 'author:id,full_name'])
            ->withCount('items')
            ->orderByDesc('published_at')
            ->paginate($limit);

        return response()->json([
            'ok' => true,
            'data' => $galleries->items(),
            'meta' => [
                'current_page' => $galleries->currentPage(),
                'last_page' => $galleries->lastPage(),
                'total' => $galleries->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $gallery = CmsGallery::published()
            ->with(['category', 'author:id,full_name', 'items'])
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'ok' => true,
            'data' => $gallery,
        ]);
    }
}
