<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DownloadHistory extends Model
{
    use HasFactory;

    protected $table = 'download_history';

    protected $fillable = [
        'url',
        'title',
        'filename',
        'format',
        'quality',
        'filesize',
        'platform',
        'source',
        'ip_address',
        'user_agent',
        'session_id',
        'metadata',
        'status',
        'error_message',
        'downloaded_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'filesize' => 'integer',
        'downloaded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope to get completed downloads only
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get recent downloads
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get downloads by platform
     */
    public function scopeByPlatform(Builder $query, string $platform): Builder
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope to get downloads by IP address
     */
    public function scopeByIp(Builder $query, string $ip): Builder
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFilesizeAttribute(): string
    {
        if (!$this->filesize) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->filesize;
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the platform from URL if not set
     */
    public function getPlatformAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        // Extract platform from URL
        $host = parse_url($this->url, PHP_URL_HOST);
        
        if (str_contains($host, 'youtube.com') || str_contains($host, 'youtu.be')) {
            return 'youtube';
        } elseif (str_contains($host, 'vimeo.com')) {
            return 'vimeo';
        } elseif (str_contains($host, 'twitter.com') || str_contains($host, 'x.com')) {
            return 'twitter';
        } elseif (str_contains($host, 'instagram.com')) {
            return 'instagram';
        } elseif (str_contains($host, 'tiktok.com')) {
            return 'tiktok';
        } elseif (str_contains($host, 'facebook.com')) {
            return 'facebook';
        }

        return 'unknown';
    }

    /**
     * Get download statistics
     */
    public static function getStats(int $days = 30): array
    {
        $query = static::where('created_at', '>=', now()->subDays($days));
        
        return [
            'total_downloads' => $query->count(),
            'successful_downloads' => $query->where('status', 'completed')->count(),
            'failed_downloads' => $query->where('status', 'failed')->count(),
            'total_size' => $query->where('status', 'completed')->sum('filesize'),
            'by_platform' => $query->selectRaw('platform, COUNT(*) as count')
                ->groupBy('platform')
                ->pluck('count', 'platform')
                ->toArray(),
            'by_format' => $query->where('status', 'completed')
                ->selectRaw('format, COUNT(*) as count')
                ->groupBy('format')
                ->pluck('count', 'format')
                ->toArray(),
            'by_source' => $query->selectRaw('source, COUNT(*) as count')
                ->groupBy('source')
                ->pluck('count', 'source')
                ->toArray(),
        ];
    }

    /**
     * Clean up old records
     */
    public static function cleanup(int $days = 30): int
    {
        return static::where('created_at', '<', now()->subDays($days))->delete();
    }

    /**
     * Record a new download attempt
     */
    public static function recordDownload(array $data): static
    {
        return static::create(array_merge($data, [
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
        ]));
    }
}