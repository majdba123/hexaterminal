<?php

namespace App\Services;

use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReviewService
{
    public function index()
    {
        return Review::orderBy('review_date', 'desc')->get();
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            if (empty($data['review_date'])) {
                $data['review_date'] = Carbon::now();
            }
            if (! isset($data['is_approved'])) {
                $data['is_approved'] = false;
            }
            $review = Review::create($data);
            DB::commit();
            return $review;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ReviewService store failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function show($id)
    {
        return Review::find($id);
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();
        try {
            $review = Review::find($id);
            if (! $review) return null;
            $review->update($data);
            DB::commit();
            return $review;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ReviewService update failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $review = Review::find($id);
            if (! $review) return null;
            $review->delete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ReviewService delete failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
