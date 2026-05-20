<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\PostResource;
use App\Models\Alert;
use App\Models\Event;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function alerts(Request $request): JsonResponse
    {
        $paginator = Alert::query()
            ->with(['user:id,username,avatar_url', 'zone:id,name'])
            ->whereNull('expires_at')
            ->orWhere('expires_at', '>', now())
            ->latest()
            ->paginate(perPage: 20, page: (int) $request->query('page', 1));

        return $this->paginate($paginator, AlertResource::collection(collect($paginator->items())));
    }

    public function events(Request $request): JsonResponse
    {
        $paginator = Event::query()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->orderBy('starts_at')
            ->paginate(perPage: 20, page: (int) $request->query('page', 1));

        return $this->paginate($paginator, EventResource::collection(collect($paginator->items())));
    }

    public function posts(Request $request): JsonResponse
    {
        $paginator = Post::query()
            ->with(['user:id,username,avatar_url', 'zone:id,name'])
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->paginate(perPage: 20, page: (int) $request->query('page', 1));

        return $this->paginate($paginator, PostResource::collection(collect($paginator->items())));
    }

    private function paginate($paginator, $data): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }
}
