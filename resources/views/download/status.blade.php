@extends('layouts.app')

@section('title', 'Download Status')

@section('content')
<div class="px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('download.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Home
            </a>
        </div>

        <!-- Status Card -->
        <div 
            class="card p-8 text-center"
            x-data="jobStatus('{{ $jobId }}')"
            x-init="startPolling()"
        >
            <div x-show="status === 'pending' || status === 'processing'" class="text-blue-600">
                <div class="spinner mx-auto mb-6"></div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Processing Your Request</h2>
                <p class="text-gray-600 mb-4" x-text="message">Please wait while we process your video...</p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-blue-800">
                        <strong>Job ID:</strong> {{ $jobId }}
                    </p>
                    <p class="text-sm text-blue-800 mt-1">
                        This page will automatically update when processing is complete.
                    </p>
                </div>
            </div>

            <div x-show="status === 'completed'" x-cloak class="text-green-600">
                <svg class="w-16 h-16 mx-auto mb-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Processing Complete!</h2>
                <p class="text-gray-600 mb-6" x-text="message">Your video has been processed successfully.</p>
                
                <!-- Show download options if available -->
                <div x-show="data && data.formats" class="text-left">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Available Downloads:</h3>
                    <template x-for="format in (data.formats || [])" :key="format.format_id">
                        <div class="border border-gray-200 rounded-lg p-4 mb-3 hover:border-green-300 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="inline-block bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded uppercase mr-2" x-text="format.ext"></span>
                                    <span x-show="format.quality && format.quality !== 'unknown'" class="inline-block bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded" x-text="format.quality + (format.type === 'video' ? 'p' : ' kbps')"></span>
                                </div>
                                <form action="{{ route('download.file') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="url" x-bind:value="data.url">
                                    <input type="hidden" name="format_url" x-bind:value="format.url">
                                    <input type="hidden" name="filename" x-bind:value="(data.title || 'download') + '.' + format.ext">
                                    <input type="hidden" name="format" x-bind:value="format.ext">
                                    
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Download
                                    </button>
                                </form>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div x-show="status === 'failed'" x-cloak class="text-red-600">
                <svg class="w-16 h-16 mx-auto mb-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Processing Failed</h2>
                <p class="text-gray-600 mb-6" x-text="message">There was an error processing your request.</p>
                <a href="{{ route('download.index') }}" class="btn-primary text-white px-6 py-3 rounded-lg font-medium">
                    Try Again
                </a>
            </div>

            <div x-show="status === 'not_found'" x-cloak class="text-gray-600">
                <svg class="w-16 h-16 mx-auto mb-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Job Not Found</h2>
                <p class="text-gray-600 mb-6">
                    The job you're looking for doesn't exist or has expired.
                </p>
                <a href="{{ route('download.index') }}" class="btn-primary text-white px-6 py-3 rounded-lg font-medium">
                    Start New Download
                </a>
            </div>

            <!-- Progress Information -->
            <div x-show="status === 'processing'" class="mt-6 text-left bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">What's happening:</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>✓ URL validation completed</li>
                    <li class="flex items-center">
                        <div class="w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin mr-2"></div>
                        Fetching video information...
                    </li>
                    <li>⏳ Extracting download links</li>
                    <li>⏳ Preparing download options</li>
                </ul>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="mt-8 grid md:grid-cols-2 gap-6">
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">While You Wait</h3>
                <ul class="text-sm text-gray-600 space-y-2">
                    <li>• Processing time varies based on video length and server load</li>
                    <li>• This page will automatically refresh with updates</li>
                    <li>• You can bookmark this page to check status later</li>
                    <li>• Files are automatically deleted after 24 hours</li>
                </ul>
            </div>
            
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Need Help?</h3>
                <ul class="text-sm text-gray-600 space-y-2">
                    <li>• Make sure the URL is publicly accessible</li>
                    <li>• Some videos may not be available due to copyright</li>
                    <li>• Try a different URL if processing fails</li>
                    <li>• Contact support if issues persist</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function jobStatus(jobId) {
    return {
        status: 'pending',
        message: 'Initializing...',
        data: null,
        polling: null,
        
        startPolling() {
            this.checkStatus();
            this.polling = setInterval(() => {
                if (this.status === 'pending' || this.status === 'processing') {
                    this.checkStatus();
                } else {
                    this.stopPolling();
                }
            }, 2000); // Poll every 2 seconds
        },
        
        stopPolling() {
            if (this.polling) {
                clearInterval(this.polling);
                this.polling = null;
            }
        },
        
        async checkStatus() {
            try {
                const response = await fetch(`/api/job-status/${jobId}`);
                const data = await response.json();
                
                this.status = data.status || 'not_found';
                this.message = data.message || 'No status message available';
                this.data = data.data || null;
                
                if (this.status === 'completed' || this.status === 'failed') {
                    this.stopPolling();
                }
            } catch (error) {
                console.error('Failed to fetch job status:', error);
                this.status = 'failed';
                this.message = 'Failed to check job status. Please refresh the page.';
                this.stopPolling();
            }
        }
    }
}
</script>
@endsection