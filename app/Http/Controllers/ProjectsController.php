<?php

namespace App\Http\Controllers;

use App\Services\ProjectService;
use App\Models\Projects;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProjectsController extends Controller
{
    protected $service;
    private const CACHE_TTL = 1800; // 30 minutes

    public function __construct(ProjectService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        // Server-side pagination when ?page= is present
        if ($request->has('page')) {
            $perPage = min((int) $request->input('per_page', 12), 100);
            $page = (int) $request->input('page', 1);

            $paginated = Projects::with(['service', 'images', 'features'])
                ->latest('updated_at')
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
        $projects = Cache::remember('api_projects_all', self::CACHE_TTL, function () {
            return $this->service->listAll();
        });

        return response()->json(['status' => 'success', 'data' => $projects]);
    }

    public function store(StoreProjectRequest $request)
    {
        try {
            $data = $request->validated();
            $files = $request->file('images', []);
            $features = $request->input('features', []);

            $project = $this->service->create($data, $files, $features);

            $this->clearCache();

            return response()->json(['status' => 'success', 'data' => $project], 201);
        } catch (\Exception $e) {
            Log::error('Project creation failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Project creation failed'], 500);
        }
    }

    public function show($id)
    {
        $project = Cache::remember('api_project_' . $id, self::CACHE_TTL, function () use ($id) {
            return $this->service->find($id);
        });

        if (!$project) {
            return response()->json(['status' => 'error', 'message' => 'Project not found'], 404);
        }
        return response()->json(['status' => 'success', 'data' => $project]);
    }

    public function update(UpdateProjectRequest $request, $id)
    {
        $project = Projects::find($id);
        if (!$project) {
            return response()->json(['status' => 'error', 'message' => 'Project not found'], 404);
        }

        try {
            $data = $request->validated();
            $files = $request->file('images', []);
            $deleted = $request->input('deleted_images', []);
            $features = $request->input('features', []);

            $updated = $this->service->update($project, $data, $files, $deleted, $features);

            $this->clearCache($id);

            return response()->json(['status' => 'success', 'data' => $updated]);
        } catch (\Exception $e) {
            Log::error('Project update failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Project update failed'], 500);
        }
    }

    public function destroy($id)
    {
        $project = Projects::with('images')->find($id);
        if (!$project) {
            return response()->json(['status' => 'error', 'message' => 'Project not found'], 404);
        }

        try {
            $this->service->delete($project);

            $this->clearCache($id);

            return response()->json(['status' => 'success', 'message' => 'Project deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Project deletion failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Project deletion failed'], 500);
        }
    }

    private function clearCache($id = null)
    {
        Cache::forget('api_projects_all');
        if ($id) {
            Cache::forget('api_project_' . $id);
        }
    }
}
