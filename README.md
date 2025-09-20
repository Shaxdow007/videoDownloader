# 📹 Laravel Video & Audio Downloader

A comprehensive web application built with Laravel for downloading videos and audio from various platforms like YouTube, Vimeo, Twitter, and more.

## 🚀 Features

- **Multi-Platform Support**: Download from YouTube, Vimeo, Twitter, Instagram, TikTok, and more
- **Multiple Formats**: Support for MP4, MP3, WebM, AVI, MOV, and other formats
- **Quality Selection**: Choose from available video/audio quality options
- **Background Processing**: Queue downloads for better performance
- **Auto Cleanup**: Automatically delete files after 24 hours
- **Modern UI**: Beautiful, responsive interface with Alpine.js
- **Rate Limiting**: Built-in protection against abuse
- **Security First**: Input validation, filename sanitization, and more

## 📋 Requirements

- PHP 8.2 or higher
- Composer
- Laravel 12.x
- SQLite/MySQL/PostgreSQL (for queue and session storage)
- Redis (optional, for better queue performance)

## 🛠️ Installation

### 1. Clone and Install Dependencies

```bash
git clone <your-repo-url>
cd video-downloader
composer install
npm install && npm run build
```

### 2. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure Environment Variables

Edit your `.env` file with the following important settings:

```env
# Basic Laravel Configuration
APP_NAME="Video Downloader"
APP_URL=http://localhost:8000

# Database (SQLite is fine for development)
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Queue Configuration (use database for simplicity)
QUEUE_CONNECTION=database

# Video Downloader Configuration
RAPIDAPI_KEY=your_rapidapi_key_here
RAPIDAPI_HOST=youtube-mp36.p.rapidapi.com
DOWNLOADER_USE_EXTERNAL_API=true
```

### 4. Database Setup

```bash
# Create SQLite database file
touch database/database.sqlite

# Run migrations
php artisan migrate

# Create storage link
php artisan storage:link
```

### 5. Create Required Directories

```bash
mkdir -p storage/app/public/downloads
chmod -R 775 storage/
```

## 🔧 Configuration Options

### External API Integration (Recommended)

The most reliable method uses external APIs from RapidAPI:

1. Sign up at [RapidAPI](https://rapidapi.com/)
2. Subscribe to a video downloader API (e.g., "YouTube Downloader")
3. Add your API key to `.env`:

```env
RAPIDAPI_KEY=your_api_key_here
RAPIDAPI_HOST=youtube-mp36.p.rapidapi.com
DOWNLOADER_USE_EXTERNAL_API=true
```

### yt-dlp Integration (Optional)

For advanced users who want to use yt-dlp:

```bash
# Install yt-dlp
pip install yt-dlp

# Enable in .env
DOWNLOADER_USE_YTDLP=true
YTDLP_BINARY_PATH=yt-dlp
```

### Direct Parsing (Fallback)

Basic HTML parsing is included as a fallback, but it's unreliable:

```env
DOWNLOADER_USE_DIRECT_PARSING=true
DOWNLOADER_VERIFY_SSL=false  # Only for development
```

## 🚀 Running the Application

### Development Server

```bash
# Start the Laravel development server
php artisan serve

# Start the queue worker (in another terminal)
php artisan queue:work

# Optional: Start the scheduler (for file cleanup)
# Add this to your crontab:
# * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Production Deployment

1. **Web Server Configuration**: Configure Apache/Nginx to point to the `public` directory
2. **Queue Workers**: Set up supervisor or systemd to manage queue workers
3. **Scheduler**: Add the Laravel scheduler to crontab
4. **File Permissions**: Ensure proper permissions for storage directories
5. **SSL Configuration**: Enable SSL verification in production

```bash
# Production optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --no-dev --optimize-autoloader
```

## 📁 Project Structure

```
app/
├── Console/
│   ├── Commands/
│   │   └── CleanupDownloadsCommand.php
│   └── Kernel.php
├── Http/
│   └── Controllers/
│       └── DownloadController.php
├── Jobs/
│   └── ProcessDownloadJob.php
└── Services/
    └── VideoDownloadService.php

config/
└── downloader.php

resources/
└── views/
    ├── layouts/
    │   └── app.blade.php
    └── download/
        ├── index.blade.php
        ├── results.blade.php
        ├── status.blade.php
        └── history.blade.php

routes/
└── web.php
```

## 🎯 Usage

### Basic Usage

1. Visit the homepage
2. Paste a video URL (YouTube, Vimeo, etc.)
3. Click "Get Download Options"
4. Select your preferred format and quality
5. Download the file

### Background Processing

For large files, enable background processing:

1. Check "Process in background"
2. Submit the URL
3. You'll be redirected to a status page
4. The page will automatically update when processing is complete

### API Endpoints

- `GET /` - Homepage
- `POST /fetch` - Process video URL
- `POST /download` - Download file
- `GET /status/{jobId}` - Check job status
- `GET /history` - Download history
- `GET /api/job-status/{jobId}` - JSON job status

## 🔧 Artisan Commands

```bash
# Manual cleanup of old files
php artisan downloader:cleanup

# Dry run (see what would be deleted)
php artisan downloader:cleanup --dry-run

# Force cleanup without confirmation
php artisan downloader:cleanup --force

# Custom retention period
php artisan downloader:cleanup --hours=12
```

## ⚖️ Legal Considerations

**IMPORTANT**: This tool is for educational and personal use only.

- ✅ Download content you own or have permission to download
- ✅ Use for personal, fair use purposes
- ❌ Don't download copyrighted content without permission
- ❌ Don't use for commercial purposes without proper licensing
- ❌ Don't violate platform terms of service

### Recommended Disclaimer

Add this to your website:

> "This tool is for personal, fair use only. Download only content you have the rights to. We are not responsible for any copyright infringement. Users are solely responsible for complying with applicable laws and platform terms of service."

## 🛡️ Security Features

- **Input Validation**: All URLs are validated and sanitized
- **Filename Sanitization**: Prevents directory traversal attacks
- **Rate Limiting**: Protects against abuse
- **File Size Limits**: Prevents storage abuse
- **Auto Cleanup**: Reduces legal and storage risks
- **SSL Verification**: Configurable SSL certificate verification
- **Domain Filtering**: Optional whitelist/blacklist of domains

## 📊 Monitoring & Logging

The application logs various activities:

- Successful downloads
- Failed attempts
- Cleanup activities
- API errors
- Security events

Logs are stored in `storage/logs/` and can be configured via the `DOWNLOADER_LOG_*` environment variables.

## 🚨 Troubleshooting

### Common Issues

**"No downloadable content found"**
- The video might be private or geo-blocked
- Try a different API or method
- Check if the platform is supported

**"API request failed"**
- Verify your RapidAPI key is correct
- Check if you have remaining API quota
- Ensure the API endpoint is correct

**"Queue jobs not processing"**
- Make sure `php artisan queue:work` is running
- Check queue configuration in `.env`
- Verify database migrations are complete

**"Files not downloading"**
- Check storage permissions
- Verify the storage link exists
- Ensure sufficient disk space

### Debug Mode

Enable debug mode for detailed error information:

```env
APP_DEBUG=true
LOG_LEVEL=debug
DOWNLOADER_LOGGING=true
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## ⚠️ Disclaimer

This software is provided "as is" without warranty of any kind. The developers are not responsible for any misuse of this tool or any copyright infringement that may result from its use. Users are solely responsible for ensuring their use complies with applicable laws and platform terms of service.

## 🙏 Acknowledgments

- Laravel Framework
- Alpine.js for frontend interactivity
- Tailwind CSS for styling
- Guzzle HTTP for API requests
- Symfony components for HTML parsing
- yt-dlp project for inspiration

---

**Happy downloading! 🎉**

Remember to use this tool responsibly and respect content creators' rights.