<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SystemSeedImageSynchronizer
{
    public function sync(string $path): string
    {
        $sourcePath = base_path('database/seeders/assets/'.$path);
        $storagePath = ltrim($path, '/');

        if (! File::exists($sourcePath)) {
            throw new \RuntimeException("The approved system image [{$path}] is missing.");
        }

        $disk = Storage::disk('public');
        $contents = File::get($sourcePath);

        if (! $disk->exists($storagePath) || $disk->get($storagePath) !== $contents) {
            $disk->put($storagePath, $contents);
        }

        return $storagePath;
    }

    /** @param list<string> $paths
     *  @return list<string>
     */
    public function syncMany(array $paths): array
    {
        return array_map(fn (string $path): string => $this->sync($path), $paths);
    }
}
