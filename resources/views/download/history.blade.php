@extends('layouts.app')

@section('title', 'Download History')

@section('content')
<div class="px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Download History</h1>
                <p class="text-gray-600">Recent downloads from the server</p>
            </div>
            <div class="flex gap-3 mt-4 sm:mt-0">
                <a href="{{ route('download.index') }}" class="btn-primary text-white px-4 py-2 rounded-lg font-medium">
                    New Download
                </a>
                <button 
                    onclick="window.location.reload()" 
                    class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-50"
                >
                    Refresh
                </button>
            </div>
        </div>

        @if(empty($downloads))
            <!-- Empty State -->
            <div class="card p-12 text-center">
                <svg class="w-20 h-20 text-gray-400 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-xl font-medium text-gray-900 mb-2">No Downloads Yet</h3>
                <p class="text-gray-600 mb-6">
                    You haven't downloaded any files yet. Start by entering a video URL.
                </p>
                <a href="{{ route('download.index') }}" class="btn-primary text-white px-6 py-3 rounded-lg font-medium">
                    Download Your First Video
                </a>
            </div>
        @else
            <!-- Downloads List -->
            <div class="space-y-4">
                @foreach($downloads as $download)
                    <div class="card p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <!-- File Icon -->
                                    <div class="flex-shrink-0">
                                        @php
                                            $extension = pathinfo($download['filename'], PATHINFO_EXTENSION);
                                            $isVideo = in_array($extension, ['mp4', 'webm', 'avi', 'mov', 'mkv']);
                                            $isAudio = in_array($extension, ['mp3', 'wav', 'm4a', 'aac']);
                                        @endphp
                                        
                                        @if($isVideo)
                                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @elseif($isAudio)
                                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-medium text-gray-900 truncate">
                                            {{ $download['filename'] }}
                                        </h3>
                                        <div class="flex items-center gap-4 text-sm text-gray-500 mt-1">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $download['created_at'] }}
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                {{ number_format($download['size'] / 1024 / 1024, 1) }} MB
                                            </span>
                                            <span class="inline-block bg-gray-100 text-gray-800 text-xs font-medium px-2 py-1 rounded uppercase">
                                                {{ $extension }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <a 
                                    href="{{ asset('storage/downloads/' . $download['filename']) }}" 
                                    download="{{ $download['filename'] }}"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Download
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination Info -->
            @if(count($downloads) >= 10)
                <div class="mt-8 text-center">
                    <p class="text-gray-600 mb-4">
                        Showing the {{ count($downloads) }} most recent downloads
                    </p>
                    <p class="text-sm text-gray-500">
                        Files are automatically deleted after 24 hours to save storage space
                    </p>
                </div>
            @endif
        @endif

        <!-- Information Cards -->
        <div class="grid md:grid-cols-2 gap-6 mt-12">
            <div class="card p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">File Storage</h3>
                </div>
                <ul class="text-sm text-gray-600 space-y-2">
                    <li>• Files are stored temporarily on our servers</li>
                    <li>• Automatic deletion after 24 hours</li>
                    <li>• Download immediately for permanent storage</li>
                    <li>• No personal data is collected or stored</li>
                </ul>
            </div>
            
            <div class="card p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Privacy & Security</h3>
                </div>
                <ul class="text-sm text-gray-600 space-y-2">
                    <li>• All downloads are processed securely</li>
                    <li>• No tracking or analytics on downloads</li>
                    <li>• Files are not scanned or monitored</li>
                    <li>• Respect copyright and fair use policies</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection