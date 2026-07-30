<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\Imag_Progect;

class ProcessProjectImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $projectId;
    public $tmpPath;

    public function __construct(int $projectId, string $tmpPath)
    {
        $this->projectId = $projectId;
        $this->tmpPath = $tmpPath;
    }

    public function handle()
    {
        try {
            if (! file_exists($this->tmpPath)) {
                return;
            }

            $ext = pathinfo($this->tmpPath, PATHINFO_EXTENSION);
            $name = uniqid('proj_', true) . '.' . $ext;
            $storagePath = 'projects/' . $name;

            Storage::disk('public')->put($storagePath, file_get_contents($this->tmpPath));

            // create DB record
            Imag_Progect::create([
                'project_id' => $this->projectId,
                'image_path' => url('api/storage/' . $storagePath)
            ]);

            @unlink($this->tmpPath);
        } catch (\Exception $e) {
            // log or handle if needed
        }
    }
}
