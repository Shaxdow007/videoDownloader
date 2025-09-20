@extends('layouts.app')

@section('title', 'Download Options - ' . ($videoInfo['title'] ?? 'Video'))

@section('content')
<div class="px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('download.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Home
            </a>
        </div>

        <!-- Video Information -->
        <div class="card p-6 mb-8">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Thumbnail -->
                @if(!empty($videoInfo['thumbnail']))
                    <div class="lg:w-1/3">
                        <img 
                            src="{{ $videoInfo['thumbnail'] }}" 
                            alt="Video thumbnail"
                            class="w-full h-48 lg:h-auto object-cover rounded-lg"
                            onerror="this.style.display='none'"
                        >
                    </div>
                @endif
                
                <!-- Video Details -->
                <div class="lg:w-2/3">
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">
                        {{ $videoInfo['title'] ?? 'Unknown Title' }}
                    </h1>
                    
                    <div class="space-y-3 text-sm text-gray-600">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                            <span class="font-medium">Source:</span>
                            <span class="ml-2 break-all">{{ $originalUrl }}</span>
                        </div>
                        
                        @if(!empty($videoInfo['duration']))
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-medium">Duration:</span>
                                <span class="ml-2">{{ gmdate('H:i:s', $videoInfo['duration']) }}</span>
                            </div>
                        @endif
                        
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="font-medium">Available formats:</span>
                            <span class="ml-2">{{ count($videoInfo['formats']) }}</span>
                        </div>
                        
                        @if(!empty($videoInfo['source']))
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="font-medium">Method:</span>
                                <span class="ml-2 capitalize">{{ str_replace('_', ' ', $videoInfo['source']) }}</span>
                            </div>
                        @endif
                    </div>
                    
                    @if(!empty($videoInfo['warning']))
                        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <p class="text-sm text-yellow-800">{{ $videoInfo['warning'] }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Download Options -->
        <div class="space-y-6">
            <h2 class="text-2xl font-bold text-gray-900">Available Download Options</h2>
            
            @if(empty($videoInfo['formats']))
                <div class="card p-8 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Download Options Found</h3>
                    <p class="text-gray-600">
                        We couldn't find any downloadable content for this URL. 
                        The video might be private, geo-blocked, or not supported.
                    </p>
                    <a href="{{ route('download.index') }}" class="inline-block mt-4 btn-primary text-white px-6 py-2 rounded-lg">
                        Try Another URL
                    </a>
                </div>
            @else
                <!-- Group formats by type -->
                @php
                    $videoFormats = collect($videoInfo['formats'])->filter(function($format) {
                        return ($format['type'] ?? 'video') === 'video';
                    });
                    
                    $audioFormats = collect($videoInfo['formats'])->filter(function($format) {
                        return ($format['type'] ?? 'video') === 'audio';
                    });
                @endphp
                
                <!-- Video Formats -->
                @if($videoFormats->count() > 0)
                    <div class="card p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Video Formats
                        </h3>
                        
                        <div class="grid gap-4">
                            @foreach($videoFormats as $format)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <span class="inline-block bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded uppercase">
                                                    {{ $format['ext'] ?? 'mp4' }}
                                                </span>
                                                
                                                @if(!empty($format['quality']) && $format['quality'] !== 'unknown')
                                                    <span class="inline-block bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                        {{ $format['quality'] }}{{ is_numeric($format['quality']) ? 'p' : '' }}
                                                    </span>
                                                @endif
                                                
                                                @if(!empty($format['filesize']))
                                                    <span class="text-sm text-gray-500">
                                                        {{ number_format($format['filesize'] / 1024 / 1024, 1) }} MB
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <p class="text-sm text-gray-600">
                                                Format ID: {{ $format['format_id'] ?? 'N/A' }}
                                            </p>
                                        </div>
                                        
                                        <form action="{{ route('download.file') }}" method="POST" class="flex-shrink-0">
                                            @csrf
                                            <input type="hidden" name="url" value="{{ $originalUrl }}">
                                            <input type="hidden" name="format_url" value="{{ $format['url'] }}">
                                            <input type="hidden" name="filename" value="{{ Str::slug($videoInfo['title'] ?? 'video') }}.{{ $format['ext'] ?? 'mp4' }}">
                                            <input type="hidden" name="format" value="{{ $format['ext'] ?? 'mp4' }}">
                                            
                                            <button 
                                                type="submit" 
                                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                            >
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                Download
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <!-- Audio Formats -->
                @if($audioFormats->count() > 0)
                    <div class="card p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                            </svg>
                            Audio Formats
                        </h3>
                        
                        <div class="grid gap-4">
                            @foreach($audioFormats as $format)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-green-300 transition-colors">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <span class="inline-block bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded uppercase">
                                                    {{ $format['ext'] ?? 'mp3' }}
                                                </span>
                                                
                                                @if(!empty($format['quality']) && $format['quality'] !== 'unknown')
                                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                        {{ $format['quality'] }}{{ is_numeric($format['quality']) ? ' kbps' : '' }}
                                                    </span>
                                                @endif
                                                
                                                @if(!empty($format['filesize']))
                                                    <span class="text-sm text-gray-500">
                                                        {{ number_format($format['filesize'] / 1024 / 1024, 1) }} MB
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <p class="text-sm text-gray-600">
                                                Format ID: {{ $format['format_id'] ?? 'N/A' }}
                                            </p>
                                        </div>
                                        
                                        <form action="{{ route('download.file') }}" method="POST" class="flex-shrink-0">
                                            @csrf
                                            <input type="hidden" name="url" value="{{ $originalUrl }}">
                                            <input type="hidden" name="format_url" value="{{ $format['url'] }}">
                                            <input type="hidden" name="filename" value="{{ Str::slug($videoInfo['title'] ?? 'audio') }}.{{ $format['ext'] ?? 'mp3' }}">
                                            <input type="hidden" name="format" value="{{ $format['ext'] ?? 'mp3' }}">
                                            
                                            <button 
                                                type="submit" 
                                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
                                            >
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                Download
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
        
        <!-- Additional Actions -->
        <div class="flex flex-col sm:flex-row gap-4 mt-8">
            <a href="{{ route('download.index') }}" class="btn-primary text-white text-center px-6 py-3 rounded-lg font-medium">
                Download Another Video
            </a>
            <a href="{{ route('download.history') }}" class="border border-gray-300 text-gray-700 text-center px-6 py-3 rounded-lg font-medium hover:bg-gray-50">
                View Download History
            </a>
        </div>
    </div>
</div>
@endsection