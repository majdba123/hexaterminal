<?php

namespace App\Services;

use App\Models\Team;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TeamService
{
    public function index()
    {
        return Team::all();
    }

    public function store(array $data, $photo = null, $cv = null)
    {
        DB::beginTransaction();
        try {
            $payload = $data;

            if ($photo) {
                $photoName = Str::random(32).'.'.$photo->getClientOriginalExtension();
                $photoPath = $photo->storeAs('team_photos', $photoName, 'public');
                if (!$photoPath) {
                    throw new \Exception('Failed to store photo file');
                }
                $payload['photo'] = url('api/storage/' . $photoPath);
            }

            if ($cv) {
                $cvName = Str::random(32).'.'.$cv->getClientOriginalExtension();
                $cvPath = $cv->storeAs('team_cvs', $cvName, 'public');
                if (!$cvPath) {
                    throw new \Exception('Failed to store CV file');
                }
                $payload['cv_file'] = url('api/storage/' . $cvPath);
            }

            $team = Team::create($payload);
            DB::commit();
            return $team;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TeamService store failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function show(Team $team)
    {
        return $team;
    }

    public function update(Team $team, array $data, $photo = null, $cv = null)
    {
        DB::beginTransaction();
        try {
            $payload = $data;

            if ($photo) {
                // Delete old photo
                if ($team->photo && str_contains($team->photo, 'api/storage/')) {
                    $old = str_replace('api/storage/', '', parse_url($team->photo, PHP_URL_PATH));
                    $old = ltrim($old, '/');
                    Storage::disk('public')->delete($old);
                }
                // Store new photo using Laravel's storeAs (reliable)
                $photoName = Str::random(32).'.'.$photo->getClientOriginalExtension();
                $photoPath = $photo->storeAs('team_photos', $photoName, 'public');
                if (!$photoPath) {
                    throw new \Exception('Failed to store photo file');
                }
                $payload['photo'] = url('api/storage/' . $photoPath);
            }

            if ($cv) {
                // Delete old CV
                if ($team->cv_file && str_contains($team->cv_file, 'api/storage/')) {
                    $oldcv = str_replace('api/storage/', '', parse_url($team->cv_file, PHP_URL_PATH));
                    $oldcv = ltrim($oldcv, '/');
                    Storage::disk('public')->delete($oldcv);
                }
                // Store new CV
                $cvName = Str::random(32).'.'.$cv->getClientOriginalExtension();
                $cvPath = $cv->storeAs('team_cvs', $cvName, 'public');
                if (!$cvPath) {
                    throw new \Exception('Failed to store CV file');
                }
                $payload['cv_file'] = url('api/storage/' . $cvPath);
            }

            $team->update($payload);
            DB::commit();
            return $team;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TeamService update failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(Team $team)
    {
        try {
            if ($team->photo) {
                $old = str_replace('api/storage/', '', parse_url($team->photo, PHP_URL_PATH));
                Storage::disk('public')->delete($old);
            }
            if ($team->cv_file) {
                $oldcv = str_replace('api/storage/', '', parse_url($team->cv_file, PHP_URL_PATH));
                Storage::disk('public')->delete($oldcv);
            }
            $team->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('TeamService delete failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
