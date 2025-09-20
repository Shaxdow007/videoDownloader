<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Video & Audio Downloader')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Custom Styles -->
    <style>
        [x-cloak] { display: none !important; }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('download.index') }}" class="text-white text-xl font-bold">
                        📹 Video Downloader
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('download.index') }}" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium">
                        Home
                    </a>
                    <a href="{{ route('download.history') }}" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium">
                        History
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Flash Messages -->
        @if (session('success'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-transition
                 x-init="setTimeout(() => show = false, 5000)"
                 class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                <span class="block sm:inline">{{ session('success') }}</span>
                <span @click="show = false" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>
        @endif

        @if ($errors->any())
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-transition
                 class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <span @click="show = false" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">
                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Legal Notice</h3>
                    <p class="text-gray-300 text-sm">
                        This tool is for personal, fair use only. Download only content you have the rights to. 
                        We are not responsible for any copyright infringement.
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Supported Sites</h3>
                    <ul class="text-gray-300 text-sm space-y-1">
                        <li>• YouTube</li>
                        <li>• Vimeo</li>
                        <li>• Twitter</li>
                        <li>• And many more...</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Privacy</h3>
                    <p class="text-gray-300 text-sm">
                        Files are automatically deleted after 24 hours. We don't store your personal data.
                    </p>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400 text-sm">
                <p>&copy; {{ date('Y') }} Video Downloader. Built with Laravel.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>