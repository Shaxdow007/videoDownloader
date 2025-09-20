<?php

namespace App\Jobs;

use App\Services\VideoDownloadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessDownloadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $url;
    public string $jobId;
    public int $timeout = 300; // 5 minutes
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(string $url, string $jobId)
    {
        $this->url = $url;
        $this->jobId = $jobId;
    }

    /**
     * Execute the job.
     */
    public function handle(VideoDownloadService $videoService): void
    {
        try {
            // Update job status to processing
            $this->updateJobStatus('processing', 'Fetching video information...');

            // Get video information
            $videoInfo = $videoService->getVideoInfo($this->url);

            if (empty($videoInfo['formats'])) {
                $this->updateJobStatus('failed', 'No downloadable content found.');
                return;
            }

            // Update status with results
            $this->updateJobStatus('completed', 'Video information retrieved successfully.', $videoInfo);

            Log::info('ProcessDownloadJob completed successfully', [
                'job_id' => $this->jobId,
                'url' => $this->url,
                'formats_count' => count($videoInfo['formats'])
            ]);

        } catch (\Exception $e) {
            Log::error('ProcessDownloadJob failed', [
                'job_id' => $this->jobId,
                'url' => $this->url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->updateJobStatus('failed', 'Failed to process video: ' . $e->getMessage());
            
            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessDownloadJob permanently failed', [
            'job_id' => $this->jobId,
            'url' => $this->url,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        $this->updateJobStatus('failed', 'Job failed after ' . $this->attempts() . ' attempts: ' . $exception->getMessage());
    }

    /**
     * Update job status in cache
     */
    private function updateJobStatus(string $status, string $message, array $data = []): void
    {
        $jobData = [
            'id' => $this->jobId,
            'url' => $this->url,
            'status' => $status, // pending, processing, completed, failed
            'message' => $message,
            'data' => $data,
            'updated_at' => now()->toISOString(),
            'attempts' => $this->attempts()
        ];

        // Store in cache for 1 hour
        Cache::put('download_job_' . $this->jobId, $jobData, 3600);

        // Also store in a list of recent jobs for the status page
        $recentJobs = Cache::get('recent_download_jobs', []);
        $recentJobs[$this->jobId] = $jobData;
        
        // Keep only last 100 jobs
        if (count($recentJobs) > 100) {
            $recentJobs = array_slice($recentJobs, -100, null, true);
        }
        
        Cache::put('recent_download_jobs', $recentJobs, 3600);
    }

    /**
     * Get unique tags for this job type
     */
    public function tags(): array
    {
        return ['download', 'video', parse_url($this->url, PHP_URL_HOST)];
    }
}

/**
 * Additional Job for handling file cleanup
 */
class CleanupDownloadsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries = 1;

    /**
     * Execute the cleanup job.
     */
    public function handle(): void
    {
        try {
            $downloadPath = storage_path('app/public/downloads');
            $maxAge = config('app.download_cleanup_hours', 24) * 3600; // Default 24 hours
            
            if (!is_dir($downloadPath)) {
                return;
            }

            $files = glob($downloadPath . '/*');
            $deletedCount = 0;
            $totalSize = 0;

            foreach ($files as $file) {
                if (is_file($file)) {
                    $fileAge = time() - filemtime($file);
                    
                    if ($fileAge > $maxAge) {
                        $fileSize = filesize($file);
                        
                        if (unlink($file)) {
                            $deletedCount++;
                            $totalSize += $fileSize;
                        }
                    }
                }
            }

            Log::info('Download cleanup completed', [
                'deleted_files' => $deletedCount,
                'freed_space' => $this->formatBytes($totalSize),
                'max_age_hours' => $maxAge / 3600
            ]);

        } catch (\Exception $e) {
            Log::error('Download cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get unique tags for this job type
     */
    public function tags(): array
    {
        return ['cleanup', 'maintenance'];
    }
}