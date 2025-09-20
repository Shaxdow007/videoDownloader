<?php

use App\Http\Controllers\DownloadController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

// Main application routes
Route::get('/', [DownloadController::class, 'index'])->name('download.index');
Route::post('/fetch', [DownloadController::class, 'fetch'])->name('download.fetch');
Route::post('/download', [DownloadController::class, 'download'])->name('download.file');
Route::get('/status/{jobId}', [DownloadController::class, 'status'])->name('download.status');
Route::get('/history', [DownloadController::class, 'history'])->name('download.history');

// API routes for AJAX requests
Route::prefix('api')->group(function () {
    Route::get('/job-status/{jobId}', function (string $jobId) {
        $jobData = Cache::get('download_job_' . $jobId);
        return response()->json($jobData ?: ['status' => 'not_found']);
    })->name('api.job.status');
    
    Route::get('/recent-jobs', function () {
        $recentJobs = Cache::get('recent_download_jobs', []);
        return response()->json(array_values($recentJobs));
    })->name('api.jobs.recent');
});
