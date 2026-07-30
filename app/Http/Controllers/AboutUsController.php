<?php

namespace App\Http\Controllers;
//kk
use App\Models\About_Us;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\AboutUsService;

class AboutUsController extends Controller
{
    protected $service;
    private const CACHE_TTL = 3600;

    public function __construct(AboutUsService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $aboutUs = Cache::remember('api_about_us', self::CACHE_TTL, function () {
            return $this->service->index();
        });

        return response()->json(['status' => 'success', 'data' => $aboutUs]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'company_description' => 'required|string',
            'website_url' => 'nullable|url',
            'foundation_date' => 'nullable|date',
            'contact_email' => 'nullable|email',
            'facebook_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'github_url' => 'nullable|url',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->except(['company_logo']);
            $file = $request->file('company_logo');

            $result = $this->service->store($data, $file);
            if (is_array($result) && isset($result['error']) && $result['error'] === 'exists') {
                return response()->json(['status' => 'error', 'message' => 'About Us record already exists. Use update instead.'], 409);
            }

            $this->clearCache();

            return response()->json(['status' => 'success', 'data' => $result, 'logo_url' => $result->company_logo ?? null], 201);
        } catch (\Exception $e) {
            Log::error('About Us creation failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'About Us creation failed'], 500);
        }
    }

    public function show()
    {
        $aboutUs = Cache::remember('api_about_us_show', self::CACHE_TTL, function () {
            return $this->service->show();
        });

        if (!$aboutUs) return response()->json(['status' => 'error', 'message' => 'About Us record not found'], 404);
        return response()->json(['status' => 'success', 'data' => $aboutUs]);
    }

    public function update(Request $request)
    {
        $aboutUs = About_Us::first();
        if (!$aboutUs) {
            return response()->json(['status' => 'error', 'message' => 'About Us record not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'company_name' => 'sometimes|string|max:255',
            'company_description' => 'sometimes|string',
            'website_url' => 'nullable|url',
            'foundation_date' => 'nullable|date',
            'contact_email' => 'nullable|email',
            'facebook_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'github_url' => 'nullable|url',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->except(['company_logo']);
            $file = $request->file('company_logo');
            $result = $this->service->update($data, $file);
            if (!$result) return response()->json(['status' => 'error', 'message' => 'About Us record not found'], 404);

            $this->clearCache();

            return response()->json(['status' => 'success', 'data' => $result, 'logo_url' => $data['company_logo'] ?? $result->company_logo]);
        } catch (\Exception $e) {
            Log::error('About Us update failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'About Us update failed'], 500);
        }
    }

    public function destroy()
    {
        try {
            $res = $this->service->destroy();
            if (!$res) return response()->json(['status' => 'error', 'message' => 'About Us record not found'], 404);

            $this->clearCache();

            return response()->json(['status' => 'success', 'message' => 'About Us record deleted successfully']);
        } catch (\Exception $e) {
            Log::error('About Us deletion failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'About Us deletion failed'], 500);
        }
    }

    private function clearCache()
    {
        Cache::forget('api_about_us');
        Cache::forget('api_about_us_show');
    }
}
