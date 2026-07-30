<?php

namespace App\Services;

use App\Models\Video;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VideoService
{
    public function index()
    {
        return Video::orderBy('created_at', 'desc')->get();
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $video = Video::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'location' => $data['location'],
                'video_url' => $data['video_url']
            ]);
            DB::commit();
            return $video;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('VideoService store failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function show($id)
    {
        return Video::find($id);
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();
        try {
            $video = Video::find($id);
            if (! $video) return null;
            $video->update($data);
            DB::commit();
            return $video;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('VideoService update failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $video = Video::find($id);
            if (! $video) return null;
            $video->delete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('VideoService delete failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
