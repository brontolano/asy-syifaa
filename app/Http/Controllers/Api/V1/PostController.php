<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CmsPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CmsPost::published()
            ->with(['category', 'tags', 'author:id,full_name'])
            ->orderByDesc('published_at');

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $request->tag));
        }

        $limit = min((int) $request->get('limit', 10), 50);
        $posts = $query->paginate($limit);

        return response()->json([
            'ok' => true,
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $post = CmsPost::published()
            ->with(['category', 'tags', 'author:id,full_name'])
            ->where('slug', $slug)
            ->firstOrFail();

        $post->increment('views_count');

        return response()->json([
            'ok' => true,
            'data' => $post,
        ]);
    }
}
