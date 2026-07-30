<?php

namespace App\Services;

use App\Models\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ServicesService
{
    public function index()
    {
        return Services::with('projects')->get();
    }

    public function store(array $data, $image = null)
    {
        DB::beginTransaction();
        try {
            $payload = $data;
            if ($image) {
                $imageName = Str::random(32).'.'.$image->getClientOriginalExtension();
                $imagePath = $image->storeAs('services', $imageName, 'public');
                if (!$imagePath) {
                    throw new \Exception('Failed to store service image');
                }
                $payload['image_path'] = url('api/storage/' . $imagePath);
            }
            $service = Services::create($payload);
            DB::commit();
            return $service;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ServicesService store failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function show($id)
    {
        return Services::with(['projects' => function ($q) {
            $q->with(['images', 'features'])->latest('updated_at');
        }])->find($id);
    }

    public function update($id, array $data, $image = null)
    {
        DB::beginTransaction();
        try {
            $service = Services::find($id);
            if (! $service) return null;
            $payload = $data;
            if ($image) {
                if ($service->image_path && str_contains($service->image_path, 'api/storage/')) {
                    $old = str_replace('api/storage/', '', parse_url($service->image_path, PHP_URL_PATH));
                    $old = ltrim($old, '/');
                    Storage::disk('public')->delete($old);
                }
                $imageName = Str::random(32).'.'.$image->getClientOriginalExtension();
                $imagePath = $image->storeAs('services', $imageName, 'public');
                if (!$imagePath) {
                    throw new \Exception('Failed to store service image');
                }
                $payload['image_path'] = url('api/storage/' . $imagePath);
            }
            $service->update($payload);
            DB::commit();
            return $service;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ServicesService update failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $service = Services::find($id);
            if (! $service) return null;
            if ($service->image_path) {
                $old = str_replace('/storage/', '', parse_url($service->image_path, PHP_URL_PATH));
                Storage::disk('public')->delete($old);
            }
            $service->delete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ServicesService delete failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
