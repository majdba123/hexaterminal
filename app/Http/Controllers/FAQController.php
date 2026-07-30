<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FAQController extends Controller
{
    private const CACHE_TTL = 3600;

    public function index(Request $request)
    {
        try {
            // Server-side pagination when ?page= is present
            if ($request->has('page')) {
                $perPage = min((int) $request->input('per_page', 12), 100);
                $page = (int) $request->input('page', 1);

                $paginated = FAQ::paginate($perPage, ['*'], 'page', $page);

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
            $faqs = Cache::remember('api_faqs_all', self::CACHE_TTL, function () {
                return FAQ::all();
            });

            return response()->json([
                'status' => 'success',
                'data' => $faqs,
                'count' => $faqs->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch FAQs: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch FAQs'], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:1000',
            'answer' => 'required|string|max:5000'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $faq = FAQ::create([
                'question' => $request->question,
                'answer' => $request->answer
            ]);
            DB::commit();

            $this->clearCache();

            return response()->json(['status' => 'success', 'data' => $faq, 'message' => 'FAQ created successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('FAQ creation failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'FAQ creation failed'], 500);
        }
    }

    public function show($id)
    {
        try {
            $faq = Cache::remember('api_faq_' . $id, self::CACHE_TTL, function () use ($id) {
                return FAQ::find($id);
            });

            if (!$faq) {
                return response()->json(['status' => 'error', 'message' => 'FAQ not found'], 404);
            }

            return response()->json(['status' => 'success', 'data' => $faq]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch FAQ: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch FAQ'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'sometimes|string|max:1000',
            'answer' => 'sometimes|string|max:5000'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $faq = FAQ::find($id);
            if (!$faq) {
                return response()->json(['status' => 'error', 'message' => 'FAQ not found'], 404);
            }

            $faq->update($validator->validated());
            DB::commit();

            $this->clearCache($id);

            return response()->json(['status' => 'success', 'data' => $faq, 'message' => 'FAQ updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('FAQ update failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'FAQ update failed'], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $faq = FAQ::find($id);
            if (!$faq) {
                return response()->json(['status' => 'error', 'message' => 'FAQ not found'], 404);
            }

            $faq->delete();
            DB::commit();

            $this->clearCache($id);

            return response()->json(['status' => 'success', 'message' => 'FAQ deleted successfully', 'deleted_id' => $id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('FAQ deletion failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'FAQ deletion failed'], 500);
        }
    }

    private function clearCache($id = null)
    {
        Cache::forget('api_faqs_all');
        if ($id) {
            Cache::forget('api_faq_' . $id);
        }
    }
}
