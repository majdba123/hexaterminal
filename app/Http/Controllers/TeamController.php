<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Services\TeamService;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    private const CACHE_TTL = 3600; // 1 hour

    public function index(Request $request)
    {
        // Server-side pagination when ?page= is present
        if ($request->has('page')) {
            $perPage = min((int) $request->input('per_page', 12), 100);
            $page = (int) $request->input('page', 1);

            $paginated = Team::paginate($perPage, ['*'], 'page', $page);

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
        $teams = Cache::remember('api_teams_all', self::CACHE_TTL, function () {
            $service = app(TeamService::class);
            return $service->index();
        });

        return response()->json(['status' => 'success', 'data' => $teams]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:teams,email',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'github_url' => 'nullable|url',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'specialization' => 'required|string',
            'position' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->except(['photo', 'cv_file']);
            $photo = $request->file('photo');
            $cv = $request->file('cv_file');
            $service = app(TeamService::class);
            $team = $service->store($data, $photo, $cv);

            $this->clearCache();

            return response()->json(['status' => 'success', 'data' => $team, 'photo_url' => $team->photo ?? null, 'cv_file_url' => $team->cv_file ?? null], 201);
        } catch (\Exception $e) {
            Log::error('Team member creation failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Team member creation failed'], 500);
        }
    }

    public function show(Team $team)
    {
        $data = Cache::remember('api_team_' . $team->id, self::CACHE_TTL, function () use ($team) {
            return $team;
        });

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function update(Request $request, Team $team)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|unique:teams,email,' . $team->id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'github_url' => 'nullable|url',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'specialization' => 'sometimes|string',
            'position' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->except(['photo', 'cv_file']);
            $photo = $request->file('photo');
            $cv = $request->file('cv_file');
            $service = app(TeamService::class);
            $team = $service->update($team, $data, $photo, $cv);

            $this->clearCache($team->id);

            return response()->json(['status' => 'success', 'data' => $team, 'photo_url' => $team->photo ?? null, 'cv_file_url' => $team->cv_file ?? null]);
        } catch (\Exception $e) {
            Log::error('Team member update failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Team member update failed'], 500);
        }
    }

    public function destroy(Team $team)
    {
        try {
            $id = $team->id;
            $service = app(TeamService::class);
            $service->delete($team);

            $this->clearCache($id);

            return response()->json(['status' => 'success', 'message' => 'Team member deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Team member deletion failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Team member deletion failed'], 500);
        }
    }

    private function clearCache($id = null)
    {
        Cache::forget('api_teams_all');
        if ($id) {
            Cache::forget('api_team_' . $id);
        }
    }
}
