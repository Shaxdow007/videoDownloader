<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Process;

class VideoDownloadService
{
    protected Client $httpClient;
    protected array $config;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 30,
            'verify' => false, // Only for development - use proper SSL in production
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]
        ]);

        $this->config = [
            'rapidapi_key' => env('RAPIDAPI_KEY'),
            'rapidapi_host' => env('RAPIDAPI_HOST', 'youtube-mp36.p.rapidapi.com'),
            'use_external_api' => env('USE_EXTERNAL_API', true),
            'use_ytdlp' => env('USE_YTDLP', false),
        ];
    }

    /**
     * Get video information and available formats
     * 
     * @param string $url The video URL
     * @return array Video information with available formats
     * @throws \Exception
     */
    public function getVideoInfo(string $url): array
    {
        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL format');
        }

        // Try external API first (most reliable)
        if ($this->config['use_external_api'] && $this->config['rapidapi_key']) {
            try {
                return $this->getVideoInfoFromAPI($url);
            } catch (\Exception $e) {
                \Log::warning('External API failed, falling back to direct parsing', [
                    'error' => $e->getMessage(),
                    'url' => $url
                ]);
            }
        }

        // Try yt-dlp if available
        if ($this->config['use_ytdlp']) {
            try {
                return $this->getVideoInfoFromYtDlp($url);
            } catch (\Exception $e) {
                \Log::warning('yt-dlp failed, falling back to direct parsing', [
                    'error' => $e->getMessage(),
                    'url' => $url
                ]);
            }
        }

        // Fallback to direct HTML parsing (least reliable)
        return $this->getVideoInfoFromDirectParsing($url);
    }

    /**
     * Get video info using external API (RapidAPI example)
     * 
     * IMPORTANT: This is the RECOMMENDED approach for production
     */
    private function getVideoInfoFromAPI(string $url): array
    {
        try {
            // Example using a YouTube downloader API from RapidAPI
            $response = $this->httpClient->post('https://youtube-mp36.p.rapidapi.com/dl', [
                'headers' => [
                    'X-RapidAPI-Key' => $this->config['rapidapi_key'],
                    'X-RapidAPI-Host' => $this->config['rapidapi_host'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'id' => $this->extractVideoId($url)
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!$data || isset($data['error'])) {
                throw new \Exception('API returned error: ' . ($data['error'] ?? 'Unknown error'));
            }

            // Transform API response to our standard format
            return $this->transformAPIResponse($data, $url);

        } catch (GuzzleException $e) {
            throw new \Exception('API request failed: ' . $e->getMessage());
        }
    }

    /**
     * Get video info using yt-dlp command line tool
     * 
     * NOTE: Requires yt-dlp to be installed on the server
     */
    private function getVideoInfoFromYtDlp(string $url): array
    {
        // Check if yt-dlp is available
        $checkProcess = new Process(['which', 'yt-dlp']);
        $checkProcess->run();
        
        if (!$checkProcess->isSuccessful()) {
            throw new \Exception('yt-dlp is not installed or not available in PATH');
        }

        // Get video information
        $process = new Process([
            'yt-dlp',
            '--dump-json',
            '--no-playlist',
            $url
        ]);

        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception('yt-dlp failed: ' . $process->getErrorOutput());
        }

        $output = $process->getOutput();
        $data = json_decode($output, true);

        if (!$data) {
            throw new \Exception('Failed to parse yt-dlp output');
        }

        return $this->transformYtDlpResponse($data);
    }

    /**
     * Direct HTML parsing approach (LEAST RELIABLE - for educational purposes)
     * 
     * WARNING: This method is unreliable and may break frequently.
     * Most modern video sites use JavaScript and complex obfuscation.
     */
    private function getVideoInfoFromDirectParsing(string $url): array
    {
        try {
            $response = $this->httpClient->get($url);
            $html = $response->getBody()->getContents();
            
            $crawler = new Crawler($html);
            
            // Try to extract basic information
            $title = $this->extractTitle($crawler, $url);
            $formats = $this->extractVideoFormats($crawler, $html, $url);
            
            if (empty($formats)) {
                // Try alternative extraction methods
                $formats = $this->extractAlternativeFormats($html, $url);
            }

            return [
                'title' => $title,
                'url' => $url,
                'thumbnail' => $this->extractThumbnail($crawler),
                'duration' => null,
                'formats' => $formats,
                'source' => 'direct_parsing',
                'warning' => 'Direct parsing is unreliable and may not work for all sites.'
            ];

        } catch (GuzzleException $e) {
            throw new \Exception('Failed to fetch page content: ' . $e->getMessage());
        }
    }

    /**
     * Download file from URL to storage
     */
    public function downloadFile(string $fileUrl, string $filename): ?string
    {
        try {
            // Ensure downloads directory exists
            if (!Storage::disk('public')->exists('downloads')) {
                Storage::disk('public')->makeDirectory('downloads');
            }

            // Generate unique filename to avoid conflicts
            $uniqueFilename = time() . '_' . $filename;
            $filePath = 'downloads/' . $uniqueFilename;

            // Download the file
            $response = $this->httpClient->get($fileUrl, [
                'timeout' => 300, // 5 minutes timeout for large files
                'stream' => true
            ]);

            // Save to storage
            $stream = $response->getBody();
            Storage::disk('public')->put($filePath, $stream->getContents());

            return $uniqueFilename;

        } catch (\Exception $e) {
            \Log::error('File download failed', [
                'url' => $fileUrl,
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Extract video ID from various platforms
     */
    private function extractVideoId(string $url): string
    {
        // YouTube
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\n?#]+)/', $url, $matches)) {
            return $matches[1];
        }
        
        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        
        // For other platforms, return the full URL
        return $url;
    }

    /**
     * Transform API response to standard format
     */
    private function transformAPIResponse(array $data, string $originalUrl): array
    {
        // This structure depends on your chosen API
        // Example structure for a typical video downloader API:
        return [
            'title' => $data['title'] ?? 'Unknown Title',
            'url' => $originalUrl,
            'thumbnail' => $data['thumbnail'] ?? null,
            'duration' => $data['duration'] ?? null,
            'formats' => $this->formatAPIFormats($data['formats'] ?? []),
            'source' => 'external_api'
        ];
    }

    /**
     * Transform yt-dlp response to standard format
     */
    private function transformYtDlpResponse(array $data): array
    {
        $formats = [];
        
        foreach ($data['formats'] ?? [] as $format) {
            if (isset($format['url'])) {
                $formats[] = [
                    'format_id' => $format['format_id'],
                    'url' => $format['url'],
                    'ext' => $format['ext'],
                    'quality' => $format['height'] ?? $format['abr'] ?? 'unknown',
                    'filesize' => $format['filesize'] ?? null,
                    'type' => isset($format['vcodec']) && $format['vcodec'] !== 'none' ? 'video' : 'audio'
                ];
            }
        }

        return [
            'title' => $data['title'] ?? 'Unknown Title',
            'url' => $data['webpage_url'] ?? $data['url'],
            'thumbnail' => $data['thumbnail'] ?? null,
            'duration' => $data['duration'] ?? null,
            'formats' => $formats,
            'source' => 'yt_dlp'
        ];
    }

    /**
     * Format API formats to standard structure
     */
    private function formatAPIFormats(array $formats): array
    {
        $standardFormats = [];
        
        foreach ($formats as $format) {
            $standardFormats[] = [
                'format_id' => $format['format_id'] ?? uniqid(),
                'url' => $format['url'],
                'ext' => $format['ext'] ?? 'mp4',
                'quality' => $format['quality'] ?? 'unknown',
                'filesize' => $format['filesize'] ?? null,
                'type' => $format['type'] ?? 'video'
            ];
        }
        
        return $standardFormats;
    }

    /**
     * Extract title from HTML
     */
    private function extractTitle(Crawler $crawler, string $url): string
    {
        // Try different selectors for title
        $selectors = [
            'meta[property="og:title"]',
            'meta[name="twitter:title"]',
            'title',
            'h1'
        ];

        foreach ($selectors as $selector) {
            try {
                $element = $crawler->filter($selector)->first();
                if ($element->count() > 0) {
                    $title = $element->attr('content') ?? $element->text();
                    if (!empty(trim($title))) {
                        return trim($title);
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return 'Unknown Title';
    }

    /**
     * Extract thumbnail from HTML
     */
    private function extractThumbnail(Crawler $crawler): ?string
    {
        $selectors = [
            'meta[property="og:image"]',
            'meta[name="twitter:image"]',
            'link[rel="image_src"]'
        ];

        foreach ($selectors as $selector) {
            try {
                $element = $crawler->filter($selector)->first();
                if ($element->count() > 0) {
                    $thumbnail = $element->attr('content') ?? $element->attr('href');
                    if (!empty(trim($thumbnail))) {
                        return trim($thumbnail);
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * Extract video formats from HTML (very basic and unreliable)
     */
    private function extractVideoFormats(Crawler $crawler, string $html, string $url): array
    {
        $formats = [];

        // Try to find video tags
        try {
            $crawler->filter('video source, video')->each(function (Crawler $node) use (&$formats) {
                $src = $node->attr('src');
                if ($src) {
                    $formats[] = [
                        'format_id' => 'direct_video',
                        'url' => $src,
                        'ext' => pathinfo(parse_url($src, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'mp4',
                        'quality' => 'unknown',
                        'type' => 'video'
                    ];
                }
            });
        } catch (\Exception $e) {
            // Continue with other methods
        }

        // Try to find audio tags
        try {
            $crawler->filter('audio source, audio')->each(function (Crawler $node) use (&$formats) {
                $src = $node->attr('src');
                if ($src) {
                    $formats[] = [
                        'format_id' => 'direct_audio',
                        'url' => $src,
                        'ext' => pathinfo(parse_url($src, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'mp3',
                        'quality' => 'unknown',
                        'type' => 'audio'
                    ];
                }
            });
        } catch (\Exception $e) {
            // Continue
        }

        return $formats;
    }

    /**
     * Try alternative extraction methods (regex patterns, etc.)
     */
    private function extractAlternativeFormats(string $html, string $url): array
    {
        $formats = [];

        // This is very site-specific and fragile
        // You would need to implement different patterns for different sites
        
        // Example patterns (these likely won't work on modern sites):
        $patterns = [
            '/(?:src|url)["\']\s*:\s*["\']([^"\']*\.(?:mp4|webm|avi|mov|mp3|m4a))["\']/',
            '/["\']([^"\']*\.(?:mp4|webm|avi|mov|mp3|m4a))["\']/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $html, $matches)) {
                foreach ($matches[1] as $match) {
                    if (filter_var($match, FILTER_VALIDATE_URL)) {
                        $ext = pathinfo(parse_url($match, PHP_URL_PATH), PATHINFO_EXTENSION);
                        $formats[] = [
                            'format_id' => 'regex_' . uniqid(),
                            'url' => $match,
                            'ext' => $ext ?: 'mp4',
                            'quality' => 'unknown',
                            'type' => in_array($ext, ['mp3', 'm4a', 'wav']) ? 'audio' : 'video'
                        ];
                    }
                }
            }
        }

        return array_unique($formats, SORT_REGULAR);
    }
}