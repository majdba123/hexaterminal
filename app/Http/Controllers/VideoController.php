<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\VideoService;

class VideoController extends Controller
{
    private const CACHE_TTL = 1800;

    public function index(Request $request)
    {
        try {
            // Server-side pagination when ?page= is present
            if ($request->has('page')) {
                $perPage = min((int) $request->input('per_page', 12), 100);
                $page = (int) $request->input('page', 1);

                $paginated = Video::orderBy('created_at', 'desc')
                    ->paginate($perPage, ['*'], 'page', $page);

                return response()->json([
                    'status' => 'success',
                    'data' => $paginated->items(),
                    'pagination' => [
                        'current_page' => $paginated->currentPage(),
                        'last_page' => $paginated->lastPage(),
                        'per_page' => $paginated->perPage(),
                        'total' => $paginated->total(),
                        'from' => $paginated->firstItem(),
                        'to' => $paginated->lastItem(),
                    ]
                ]);
            }

            // Return all (cached — used by homepage)
            $videos = Cache::remember('api_videos_all', self::CACHE_TTL, function () {
                $service = app(VideoService::class);
                return $service->index();
            });

            return response()->json(['status' => 'success', 'data' => $videos, 'count' => is_countable($videos) ? count($videos) : 0]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch videos: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch videos'], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'location' => 'required|string|max:255',
            'video_url' => 'required|url|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $service = app(VideoService::class);
            $video = $service->store($request->only(['title', 'description', 'location', 'video_url']));

            Cache::forget('api_videos_all');

            return response()->json(['status' => 'success', 'data' => $video, 'message' => 'Video created successfully'], 201);
        } catch (\Exception $e) {
            Log::error('Video creation failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Video creation failed'], 500);
        }
    }

    public function show($id)
    {
        try {
            $video = Cache::remember('api_video_' . $id, self::CACHE_TTL, function () use ($id) {
                $service = app(VideoService::class);
                return $service->show($id);
            });

            if (!$video) return response()->json(['status' => 'error', 'message' => 'Video not found'], 404);
            return response()->json(['status' => 'success', 'data' => $video]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch video: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch video'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'location' => 'sometimes|string|max:255',
            'video_url' => 'sometimes|url|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $service = app(VideoService::class);
            $video = $service->update($id, $validator->validated());
            if (!$video) return response()->json(['status' => 'error', 'message' => 'Video not found'], 404);

            Cache::forget('api_videos_all');
            Cache::forget('api_video_' . $id);

            return response()->json(['status' => 'success', 'data' => $video, 'message' => 'Video updated successfully']);
        } catch (\Exception $e) {
            Log::error('Video update failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Video update failed'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $service = app(VideoService::class);
            $res = $service->delete($id);
            if (!$res) return response()->json(['status' => 'error', 'message' => 'Video not found'], 404);

            Cache::forget('api_videos_all');
            Cache::forget('api_video_' . $id);

            return response()->json(['status' => 'success', 'message' => 'Video deleted successfully', 'deleted_id' => $id]);
        } catch (\Exception $e) {
            Log::error('Video deletion failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Video deletion failed'], 500);
        }
    }
}
