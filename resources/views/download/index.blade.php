@extends('layouts.app')

@section('title', 'Video & Audio Downloader - Download from YouTube, Vimeo, Twitter and more')

@section('content')
<div class="px-4 py-8">
    <!-- Hero Section -->
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-4">
            Download Videos & Audio
        </h1>
        <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
            Paste any video URL and download it in multiple formats. 
            Fast, free, and works with most popular video platforms.
        </p>
    </div>

    <!-- Main Download Form -->
    <div class="max-w-4xl mx-auto">
        <div class="card p-8 mb-8">
            <form 
                action="{{ route('download.fetch') }}" 
                method="POST" 
                x-data="downloadForm()"
                @submit="onSubmit"
                class="space-y-6"
            >
                @csrf
                
                <!-- URL Input -->
                <div>
                    <label for="url" class="block text-sm font-medium text-gray-700 mb-2">
                        Video URL
                    </label>
                    <div class="relative">
                        <input 
                            type="url" 
                            id="url" 
                            name="url" 
                            value="{{ old('url') }}"
                            placeholder="https://www.youtube.com/watch?v=..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg"
                            required
                            x-model="url"
                        >
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg x-show="!loading" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">
                        Supported: YouTube, Vimeo, Twitter, Instagram, TikTok, and many more
                    </p>
                </div>

                <!-- Processing Options -->
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="queue" 
                            name="queue" 
                            value="1"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            x-model="useQueue"
                        >
                        <label for="queue" class="ml-2 text-sm text-gray-700">
                            Process in background (recommended for large files)
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <button 
                        type="submit" 
                        class="flex-1 btn-primary text-white font-semibold py-3 px-6 rounded-lg text-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="loading || !url"
                        x-text="loading ? 'Processing...' : 'Get Download Options'"
                    >
                        Get Download Options
                    </button>
                    
                    <button 
                        type="button" 
                        @click="clearForm"
                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Clear
                    </button>
                </div>

                <!-- Loading Indicator -->
                <div x-show="loading" x-cloak class="text-center py-8">
                    <div class="spinner mx-auto mb-4"></div>
                    <p class="text-gray-600" x-text="loadingMessage">Processing your request...</p>
                </div>
            </form>
        </div>

        <!-- Quick Examples -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Try these examples:</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <h4 class="font-medium text-gray-700">YouTube</h4>
                    <button 
                        @click="fillExample('https://www.youtube.com/watch?v=dQw4w9WgXcQ')"
                        class="text-left text-sm text-blue-600 hover:text-blue-800 hover:underline block"
                    >
                        https://www.youtube.com/watch?v=dQw4w9WgXcQ
                    </button>
                </div>
                <div class="space-y-2">
                    <h4 class="font-medium text-gray-700">Vimeo</h4>
                    <button 
                        @click="fillExample('https://vimeo.com/148751763')"
                        class="text-left text-sm text-blue-600 hover:text-blue-800 hover:underline block"
                    >
                        https://vimeo.com/148751763
                    </button>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div class="grid md:grid-cols-3 gap-6 mt-12">
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Multiple Formats</h3>
                <p class="text-gray-600">Download in MP4, MP3, WebM, and more formats with various quality options.</p>
            </div>
            
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Fast & Reliable</h3>
                <p class="text-gray-600">High-speed downloads with reliable processing using external APIs.</p>
            </div>
            
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Privacy First</h3>
                <p class="text-gray-600">Files are automatically deleted after 24 hours. No data collection.</p>
            </div>
        </div>
    </div>
</div>

<script>
function downloadForm() {
    return {
        url: '',
        loading: false,
        useQueue: false,
        loadingMessage: 'Processing your request...',
        
        onSubmit(event) {
            this.loading = true;
            this.loadingMessage = this.useQueue ? 
                'Queueing your download for background processing...' : 
                'Fetching video information...';
        },
        
        clearForm() {
            this.url = '';
            this.useQueue = false;
            this.loading = false;
        },
        
        fillExample(exampleUrl) {
            this.url = exampleUrl;
        }
    }
}
</script>
@endsection