<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class TeamMemberSeedImageSynchronizer
{
    public function sync(string $source, string $targetPath): string
    {
        $filename = basename($source);
        $sourcePath = base_path('database/seeders/assets/team/'.$filename);
        $storagePath = ltrim($targetPath, '/');

        if (! File::exists($sourcePath)) {
            throw new \RuntimeException("The approved team member image [{$source}] is missing.");
        }

        $disk = Storage::disk('public');
        $contents = File::get($sourcePath);

        if (! $disk->exists($storagePath) || $disk->get($storagePath) !== $contents) {
            $disk->put($storagePath, $contents);
        }

        return $storagePath;
    }
}
