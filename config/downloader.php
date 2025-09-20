<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Video Downloader Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the video downloader service.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | External API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for external video download APIs (RapidAPI, etc.)
    |
    */
    'external_api' => [
        'enabled' => env('DOWNLOADER_USE_EXTERNAL_API', true),
        'rapidapi_key' => env('RAPIDAPI_KEY'),
        'rapidapi_host' => env('RAPIDAPI_HOST', 'youtube-mp36.p.rapidapi.com'),
        'timeout' => env('DOWNLOADER_API_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | yt-dlp Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for yt-dlp command line tool integration
    |
    */
    'ytdlp' => [
        'enabled' => env('DOWNLOADER_USE_YTDLP', false),
        'binary_path' => env('YTDLP_BINARY_PATH', 'yt-dlp'),
        'timeout' => env('YTDLP_TIMEOUT', 60),
        'max_filesize' => env('YTDLP_MAX_FILESIZE', '500M'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Direct Parsing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for direct HTML parsing (fallback method)
    |
    */
    'direct_parsing' => [
        'enabled' => env('DOWNLOADER_USE_DIRECT_PARSING', true),
        'user_agent' => env('DOWNLOADER_USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'),
        'timeout' => env('DOWNLOADER_DIRECT_TIMEOUT', 30),
        'verify_ssl' => env('DOWNLOADER_VERIFY_SSL', false), // Set to true in production
    ],

    /*
    |--------------------------------------------------------------------------
    | File Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for downloaded file storage and management
    |
    */
    'storage' => [
        'disk' => env('DOWNLOADER_STORAGE_DISK', 'public'),
        'directory' => env('DOWNLOADER_STORAGE_DIRECTORY', 'downloads'),
        'max_filesize' => env('DOWNLOADER_MAX_FILESIZE', 100 * 1024 * 1024), // 100MB in bytes
        'allowed_extensions' => ['mp4', 'mp3', 'webm', 'avi', 'mov', 'm4a', 'wav', 'aac'],
        'cleanup_hours' => env('DOWNLOADER_CLEANUP_HOURS', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for background job processing
    |
    */
    'queue' => [
        'connection' => env('DOWNLOADER_QUEUE_CONNECTION', 'default'),
        'queue' => env('DOWNLOADER_QUEUE_NAME', 'downloads'),
        'timeout' => env('DOWNLOADER_QUEUE_TIMEOUT', 300), // 5 minutes
        'max_tries' => env('DOWNLOADER_MAX_TRIES', 3),
        'retry_delay' => env('DOWNLOADER_RETRY_DELAY', 60), // 1 minute
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for rate limiting download requests
    |
    */
    'rate_limiting' => [
        'enabled' => env('DOWNLOADER_RATE_LIMITING', true),
        'max_requests_per_minute' => env('DOWNLOADER_MAX_REQUESTS_PER_MINUTE', 10),
        'max_requests_per_hour' => env('DOWNLOADER_MAX_REQUESTS_PER_HOUR', 100),
        'max_concurrent_downloads' => env('DOWNLOADER_MAX_CONCURRENT', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security-related configuration options
    |
    */
    'security' => [
        'allowed_domains' => env('DOWNLOADER_ALLOWED_DOMAINS') ? explode(',', env('DOWNLOADER_ALLOWED_DOMAINS')) : [],
        'blocked_domains' => env('DOWNLOADER_BLOCKED_DOMAINS') ? explode(',', env('DOWNLOADER_BLOCKED_DOMAINS')) : [],
        'max_url_length' => env('DOWNLOADER_MAX_URL_LENGTH', 2048),
        'sanitize_filenames' => env('DOWNLOADER_SANITIZE_FILENAMES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for download activity logging
    |
    */
    'logging' => [
        'enabled' => env('DOWNLOADER_LOGGING', true),
        'log_channel' => env('DOWNLOADER_LOG_CHANNEL', 'daily'),
        'log_successful_downloads' => env('DOWNLOADER_LOG_SUCCESS', true),
        'log_failed_downloads' => env('DOWNLOADER_LOG_FAILURES', true),
        'log_user_agents' => env('DOWNLOADER_LOG_USER_AGENTS', false),
        'log_ip_addresses' => env('DOWNLOADER_LOG_IPS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for caching video information
    |
    */
    'cache' => [
        'enabled' => env('DOWNLOADER_CACHE_ENABLED', true),
        'ttl' => env('DOWNLOADER_CACHE_TTL', 3600), // 1 hour
        'prefix' => env('DOWNLOADER_CACHE_PREFIX', 'downloader'),
        'store' => env('DOWNLOADER_CACHE_STORE', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Platforms
    |--------------------------------------------------------------------------
    |
    | List of supported video platforms and their configurations
    |
    */
    'platforms' => [
        'youtube' => [
            'enabled' => true,
            'domains' => ['youtube.com', 'youtu.be', 'm.youtube.com'],
            'api_priority' => 1,
        ],
        'vimeo' => [
            'enabled' => true,
            'domains' => ['vimeo.com'],
            'api_priority' => 2,
        ],
        'twitter' => [
            'enabled' => true,
            'domains' => ['twitter.com', 'x.com', 't.co'],
            'api_priority' => 3,
        ],
        'instagram' => [
            'enabled' => true,
            'domains' => ['instagram.com'],
            'api_priority' => 4,
        ],
        'tiktok' => [
            'enabled' => true,
            'domains' => ['tiktok.com'],
            'api_priority' => 5,
        ],
        'facebook' => [
            'enabled' => true,
            'domains' => ['facebook.com', 'fb.watch'],
            'api_priority' => 6,
        ],
        'dailymotion' => [
            'enabled' => true,
            'domains' => ['dailymotion.com'],
            'api_priority' => 7,
        ],
        'twitch' => [
            'enabled' => true,
            'domains' => ['twitch.tv'],
            'api_priority' => 8,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Feature flags for enabling/disabling specific functionality
    |
    */
    'features' => [
        'background_processing' => env('DOWNLOADER_BACKGROUND_PROCESSING', true),
        'download_history' => env('DOWNLOADER_DOWNLOAD_HISTORY', true),
        'thumbnail_extraction' => env('DOWNLOADER_THUMBNAILS', true),
        'format_selection' => env('DOWNLOADER_FORMAT_SELECTION', true),
        'quality_selection' => env('DOWNLOADER_QUALITY_SELECTION', true),
        'batch_downloads' => env('DOWNLOADER_BATCH_DOWNLOADS', false),
        'playlist_support' => env('DOWNLOADER_PLAYLIST_SUPPORT', false),
        'subtitle_extraction' => env('DOWNLOADER_SUBTITLES', false),
    ],
];