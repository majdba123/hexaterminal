<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ServiceSeedImageSynchronizer
{
    public const DIRECTORY = 'service-offerings';

    /** @var list<string> */
    private const SOURCES = [
        'images/custom-erp-crm-systems.png',
        'images/web-platforms-mobile-applications.png',
        'images/ecommerce-business-websites.png',
    ];

    public function sync(string $source): string
    {
        $filename = basename($source);
        $sourcePath = base_path('database/seeders/assets/services/'.$filename);
        $storagePath = self::DIRECTORY.'/'.$filename;

        if (! File::exists($sourcePath)) {
            throw new \RuntimeException("The approved service image [{$source}] is missing.");
        }

        $disk = Storage::disk('public');
        $contents = File::get($sourcePath);

        if (! $disk->exists($storagePath) || $disk->get($storagePath) !== $contents) {
            $disk->put($storagePath, $contents);
        }

        return $storagePath;
    }

    /** @return list<string> */
    public function syncAll(): array
    {
        return array_map(fn (string $source): string => $this->sync($source), self::SOURCES);
    }
}
