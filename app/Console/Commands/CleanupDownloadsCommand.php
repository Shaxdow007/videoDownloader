<?php

namespace App\Console\Commands;

use App\Jobs\CleanupDownloadsJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupDownloadsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'downloader:cleanup 
                            {--force : Force cleanup without confirmation}
                            {--hours= : Custom hours to keep files (default from config)}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old downloaded files to free up storage space';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Starting download cleanup...');

        $downloadPath = storage_path('app/public/downloads');
        $maxAge = $this->option('hours') ? 
            $this->option('hours') * 3600 : 
            config('downloader.storage.cleanup_hours', 24) * 3600;
        
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if (!is_dir($downloadPath)) {
            $this->warn('Downloads directory does not exist: ' . $downloadPath);
            return 0;
        }

        $files = glob($downloadPath . '/*');
        $eligibleFiles = [];
        $totalSize = 0;

        // Find files eligible for cleanup
        foreach ($files as $file) {
            if (is_file($file)) {
                $fileAge = time() - filemtime($file);
                
                if ($fileAge > $maxAge) {
                    $fileSize = filesize($file);
                    $eligibleFiles[] = [
                        'path' => $file,
                        'name' => basename($file),
                        'size' => $fileSize,
                        'age' => $fileAge,
                        'age_hours' => round($fileAge / 3600, 1)
                    ];
                    $totalSize += $fileSize;
                }
            }
        }

        if (empty($eligibleFiles)) {
            $this->info('✅ No files need cleanup. All files are within the retention period.');
            return 0;
        }

        // Display summary
        $this->newLine();
        $this->info("📊 Cleanup Summary:");
        $this->table(
            ['File', 'Size', 'Age (hours)'],
            array_map(function ($file) {
                return [
                    $file['name'],
                    $this->formatBytes($file['size']),
                    $file['age_hours']
                ];
            }, array_slice($eligibleFiles, 0, 10)) // Show first 10 files
        );

        if (count($eligibleFiles) > 10) {
            $this->info('... and ' . (count($eligibleFiles) - 10) . ' more files');
        }

        $this->newLine();
        $this->info("Files to delete: " . count($eligibleFiles));
        $this->info("Total space to free: " . $this->formatBytes($totalSize));
        $this->info("Max age: " . round($maxAge / 3600, 1) . " hours");

        if ($dryRun) {
            $this->warn('🔍 DRY RUN: No files were actually deleted.');
            return 0;
        }

        // Confirm deletion
        if (!$force && !$this->confirm('Do you want to proceed with the cleanup?')) {
            $this->info('Cleanup cancelled.');
            return 0;
        }

        // Perform cleanup
        $deletedCount = 0;
        $deletedSize = 0;
        $errors = [];

        $progressBar = $this->output->createProgressBar(count($eligibleFiles));
        $progressBar->start();

        foreach ($eligibleFiles as $file) {
            try {
                if (unlink($file['path'])) {
                    $deletedCount++;
                    $deletedSize += $file['size'];
                } else {
                    $errors[] = 'Failed to delete: ' . $file['name'];
                }
            } catch (\Exception $e) {
                $errors[] = 'Error deleting ' . $file['name'] . ': ' . $e->getMessage();
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        if ($deletedCount > 0) {
            $this->info("✅ Successfully deleted {$deletedCount} files");
            $this->info("💾 Freed up " . $this->formatBytes($deletedSize) . " of storage space");
        }

        if (!empty($errors)) {
            $this->warn("⚠️  Some errors occurred:");
            foreach ($errors as $error) {
                $this->error($error);
            }
        }

        // Log the cleanup activity
        \Log::info('Download cleanup completed', [
            'deleted_files' => $deletedCount,
            'freed_space' => $deletedSize,
            'errors' => count($errors),
            'max_age_hours' => $maxAge / 3600
        ]);

        return 0;
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
}