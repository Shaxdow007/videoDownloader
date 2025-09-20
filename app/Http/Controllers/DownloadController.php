<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDownloadJob;
use App\Services\VideoDownloadService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DownloadController extends Controller
{
    protected VideoDownloadService $videoService;

    public function __construct(VideoDownloadService $videoService)
    {
        $this->videoService = $videoService;
    }

    /**
     * Show the main homepage with URL input form
     */
    public function index()
    {
        return view('download.index');
    }

    /**
     * Process the submitted URL and fetch video information
     */
    public function fetch(Request $request)
    {
        // Validate the input URL
        $validator = Validator::make($request->all(), [
            'url' => 'required|url|max:2048',
        ], [
            'url.required' => 'Please enter a valid URL.',
            'url.url' => 'Please enter a valid URL format.',
            'url.max' => 'URL is too long.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $url = $request->input('url');

        try {
            // Check if we should process immediately or queue it
            if ($request->has('queue') && $request->boolean('queue')) {
                // Queue the job for background processing
                $jobId = Str::uuid()->toString();
                ProcessDownloadJob::dispatch($url, $jobId);
                
                return redirect()->route('download.status', ['jobId' => $jobId])
                    ->with('success', 'Your download is being processed. Please wait...');
            } else {
                // Process immediately (for quick previews)
                $videoInfo = $this->videoService->getVideoInfo($url);
                
                if (empty($videoInfo['formats'])) {
                    return back()
                        ->withErrors(['url' => 'No downloadable content found at this URL.'])
                        ->withInput();
                }

                return view('download.results', [
                    'videoInfo' => $videoInfo,
                    'originalUrl' => $url
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Download fetch error: ' . $e->getMessage(), [
                'url' => $url,
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withErrors(['url' => 'Failed to process the URL. Please try again or contact support.'])
                ->withInput();
        }
    }

    /**
     * Handle the actual file download
     */
    public function download(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'format_url' => 'required|url',
            'filename' => 'required|string|max:255',
            'format' => 'required|string|in:mp4,mp3,webm,avi,mov',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $formatUrl = $request->input('format_url');
            $filename = $request->input('filename');
            $format = $request->input('format');
            
            // Sanitize filename
            $safeFilename = $this->sanitizeFilename($filename, $format);
            
            // Download the file
            $filePath = $this->videoService->downloadFile($formatUrl, $safeFilename);
            
            if (!$filePath) {
                return back()
                    ->withErrors(['download' => 'Failed to download the file. Please try again.'])
                    ->withInput();
            }

            // Store download history (optional)
            $this->storeDownloadHistory($request->input('url'), $safeFilename);

            // Return file download response
            return response()->download(
                storage_path('app/public/downloads/' . $filePath),
                $safeFilename,
                [
                    'Content-Type' => $this->getMimeType($format),
                    'Content-Disposition' => 'attachment; filename="' . $safeFilename . '"'
                ]
            )->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('Download error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withErrors(['download' => 'Download failed. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Show download status for queued jobs
     */
    public function status(Request $request, string $jobId)
    {
        // In a real implementation, you'd check the job status from your queue/database
        // For now, we'll show a simple status page
        return view('download.status', [
            'jobId' => $jobId
        ]);
    }

    /**
     * Show download history
     */
    public function history()
    {
        // In a real implementation, you'd fetch from database
        // For now, we'll show recent files from storage
        $downloads = $this->getRecentDownloads();
        
        return view('download.history', [
            'downloads' => $downloads
        ]);
    }

    /**
     * Sanitize filename to prevent directory traversal and invalid characters
     */
    private function sanitizeFilename(string $filename, string $extension): string
    {
        // Remove or replace invalid characters
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $filename);
        
        // Remove multiple underscores
        $filename = preg_replace('/_+/', '_', $filename);
        
        // Ensure it doesn't start with dots or dashes
        $filename = ltrim($filename, '.-');
        
        // Limit length
        $filename = substr($filename, 0, 200);
        
        // Add extension if not present
        if (!str_ends_with(strtolower($filename), '.' . $extension)) {
            $filename .= '.' . $extension;
        }
        
        return $filename ?: 'download_' . time() . '.' . $extension;
    }

    /**
     * Get MIME type based on file extension
     */
    private function getMimeType(string $format): string
    {
        $mimeTypes = [
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
            'webm' => 'video/webm',
            'avi' => 'video/x-msvideo',
            'mov' => 'video/quicktime',
        ];

        return $mimeTypes[$format] ?? 'application/octet-stream';
    }

    /**
     * Store download history (implement based on your needs)
     */
    private function storeDownloadHistory(string $url, string $filename): void
    {
        // You can implement this to store in database:
        // DownloadHistory::create([
        //     'url' => $url,
        //     'filename' => $filename,
        //     'downloaded_at' => now(),
        //     'ip_address' => request()->ip(),
        // ]);
    }

    /**
     * Get recent downloads from storage
     */
    private function getRecentDownloads(): array
    {
        $downloads = [];
        $downloadPath = storage_path('app/public/downloads');
        
        if (is_dir($downloadPath)) {
            $files = glob($downloadPath . '/*');
            $files = array_filter($files, 'is_file');
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            foreach (array_slice($files, 0, 10) as $file) {
                $downloads[] = [
                    'filename' => basename($file),
                    'size' => filesize($file),
                    'created_at' => date('Y-m-d H:i:s', filemtime($file))
                ];
            }
        }
        
        return $downloads;
    }
}