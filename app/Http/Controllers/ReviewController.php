<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    private const CACHE_TTL = 1800;

    /**
     * True when the request carries a valid Sanctum token for an admin user.
     * The index/show routes are public, so admin status is resolved from the
     * bearer token on demand — anonymous visitors only ever see approved reviews.
     */
    private function isAdminRequest(): bool
    {
        $user = auth('sanctum')->user();

        return $user instanceof User && (int) $user->type === 1;
    }

    public function index(Request $request)
    {
        try {
            $isAdmin = $this->isAdminRequest();

            // Server-side pagination when ?page= is present
            if ($request->has('page')) {
                $perPage = min((int) $request->input('per_page', 12), 100);
                $page = (int) $request->input('page', 1);

                // Admins (moderation UI) see everything; the public sees approved only.
                $query = $isAdmin ? Review::query() : Review::approved();

                $paginated = $query
                    ->orderBy('review_date', 'desc')
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
                    ],
                ]);
            }

            if ($isAdmin) {
                // Moderation view — never cached, includes pending reviews.
                $reviews = Review::orderBy('review_date', 'desc')->get();
            } else {
                // Return approved reviews only (cached — used by homepage)
                $reviews = Cache::remember('api_reviews_all', self::CACHE_TTL, function () {
                    return Review::approved()->orderBy('review_date', 'desc')->get();
                });
            }

            return response()->json([
                'status' => 'success',
                'data' => $reviews,
                'count' => $reviews->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch reviews: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Failed to fetch reviews'], 500);
        }
    }

    public function store(Request $request)
    {
        // Public endpoint: is_approved is deliberately NOT accepted from the
        // request — submitters must never be able to approve their own review.
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'content' => 'required|string|max:2000',
            'rating' => 'required|integer|between:1,5',
            'review_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $reviewData = $validator->validated();
            if (empty($reviewData['review_date'])) {
                $reviewData['review_date'] = Carbon::now();
            }
            $reviewData['is_approved'] = false;

            $review = Review::create($reviewData);
            DB::commit();

            Cache::forget('api_reviews_all');

            return response()->json(['status' => 'success', 'data' => $review, 'message' => 'Review created successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Review creation failed: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Review creation failed'], 500);
        }
    }

    public function show($id)
    {
        try {
            if ($this->isAdminRequest()) {
                $review = Review::find($id);
            } else {
                // Public: approved reviews only; pending ones 404 for anonymous users.
                $review = Cache::remember('api_review_'.$id, self::CACHE_TTL, function () use ($id) {
                    return Review::approved()->find($id);
                });
            }

            if (! $review) {
                return response()->json(['status' => 'error', 'message' => 'Review not found'], 404);
            }

            return response()->json(['status' => 'success', 'data' => $review]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch review: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Failed to fetch review'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'content' => 'sometimes|string|max:2000',
            'rating' => 'sometimes|integer|between:1,5',
            'review_date' => 'sometimes|date',
            'is_approved' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $review = Review::find($id);
            if (! $review) {
                return response()->json(['status' => 'error', 'message' => 'Review not found'], 404);
            }

            $review->update($validator->validated());
            DB::commit();

            Cache::forget('api_reviews_all');
            Cache::forget('api_review_'.$id);

            return response()->json(['status' => 'success', 'data' => $review, 'message' => 'Review updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Review update failed: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Review update failed'], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $review = Review::find($id);
            if (! $review) {
                return response()->json(['status' => 'error', 'message' => 'Review not found'], 404);
            }

            $review->delete();
            DB::commit();

            Cache::forget('api_reviews_all');
            Cache::forget('api_review_'.$id);

            return response()->json(['status' => 'success', 'message' => 'Review deleted successfully', 'deleted_id' => $id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Review deletion failed: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Review deletion failed'], 500);
        }
    }
}
