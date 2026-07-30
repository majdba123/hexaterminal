<?php

namespace App\Http\Controllers;

use App\Models\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Services\ServicesService;
use Illuminate\Support\Facades\Log;

class ServicesController extends Controller
{
    protected $service;
    private const CACHE_TTL = 3600;

    public function __construct(ServicesService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        // Server-side pagination when ?page= is present
        if ($request->has('page')) {
            $perPage = min((int) $request->input('per_page', 12), 100);
            $page = (int) $request->input('page', 1);

            $paginated = Services::with('projects')->paginate($perPage, ['*'], 'page', $page);

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
        $services = Cache::remember('api_services_all', self::CACHE_TTL, function () {
            return $this->service->index();
        });

        return response()->json(['status' => 'success', 'data' => $services]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->except(['image']);
            $image = $request->file('image');
            $res = $this->service->store($data, $image);

            $this->clearCache();

            return response()->json(['status' => 'success', 'data' => $res, 'image_url' => $res->image_path ?? null], 201);
        } catch (\Exception $e) {
            Log::error('Service creation failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Service creation failed'], 500);
        }
    }

    public function show($id)
    {
        $res = Cache::remember('api_service_' . $id, self::CACHE_TTL, function () use ($id) {
            return $this->service->show($id);
        });

        if (!$res) return response()->json(['status' => 'error', 'message' => 'Service not found'], 404);
        return response()->json(['status' => 'success', 'data' => $res]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->except(['image']);
            $image = $request->file('image');
            $res = $this->service->update($id, $data, $image);
            if (!$res) return response()->json(['status' => 'error', 'message' => 'Service not found'], 404);

            $this->clearCache($id);

            return response()->json(['status' => 'success', 'data' => $res, 'image_url' => $res->image_path ?? null]);
        } catch (\Exception $e) {
            Log::error('Service update failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Service update failed'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $res = $this->service->delete($id);
            if (!$res) return response()->json(['status' => 'error', 'message' => 'Service not found'], 404);

            $this->clearCache($id);

            return response()->json(['status' => 'success', 'message' => 'Service deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Service deletion failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Service deletion failed'], 500);
        }
    }

    private function clearCache($id = null)
    {
        Cache::forget('api_services_all');
        if ($id) {
            Cache::forget('api_service_' . $id);
        }
    }
}
