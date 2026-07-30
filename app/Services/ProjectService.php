<?php

namespace App\Services;

use App\Models\Projects;
use App\Models\Imag_Progect;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessProjectImage;

class ProjectService
{
    public function listAll()
    {
        return Projects::with(['service', 'images', 'features'])->get();
    }

    public function find(int $id)
    {
        return Projects::with(['service', 'images', 'features'])->find($id);
    }

    public function create(array $data, array $files = [], array $features = [])
    {
        DB::beginTransaction();
        try {
            $projectData = $data;
            $project = Projects::create($projectData);

            foreach ($files as $file) {
                $tmpName = Str::random(40) . '.' . $file->getClientOriginalExtension();
                $tmpPath = storage_path('app/uploads/' . $tmpName);
                $file->move(dirname($tmpPath), basename($tmpPath));

                ProcessProjectImage::dispatch($project->id, $tmpPath);
            }

            if (! empty($features)) {
                foreach ($features as $feature) {
                    $project->features()->create(['feature_text' => $feature]);
                }
            }

            DB::commit();
            return $project->load(['images', 'features']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(Projects $project, array $data, array $files = [], array $deletedImageIds = [], array $features = [])
    {
        DB::beginTransaction();
        try {
            if (! empty($deletedImageIds)) {
                foreach ($deletedImageIds as $id) {
                    $img = Imag_Progect::find($id);
                    if ($img) {
                        $this->deleteFileByUrl($img->image_path);
                        $img->delete();
                    }
                }
            }

            foreach ($files as $file) {
                $tmpName = Str::random(40) . '.' . $file->getClientOriginalExtension();
                $tmpPath = storage_path('app/uploads/' . $tmpName);
                $file->move(dirname($tmpPath), basename($tmpPath));
                ProcessProjectImage::dispatch($project->id, $tmpPath);
            }

            if (! empty($features)) {
                $project->features()->delete();
                foreach ($features as $feature) {
                    $project->features()->create(['feature_text' => $feature]);
                }
            }

            $project->update($data);
            DB::commit();
            return $project->fresh(['images', 'features']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(Projects $project)
    {
        DB::beginTransaction();
        try {
            foreach ($project->images as $image) {
                $this->deleteFileByUrl($image->image_path);
                $image->delete();
            }
            $project->delete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function deleteFileByUrl($fileUrl)
    {
        try {
            $filePath = parse_url($fileUrl, PHP_URL_PATH);
            $filePath = str_replace('/storage/', '', $filePath);
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
                return true;
            }
        } catch (\Exception $e) {
            // ignore
        }
        return false;
    }
}
