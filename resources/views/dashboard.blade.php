<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>İşler</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <x-navigation />

    <!-- Main Content -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">İşler</h2>

                    <!-- Discovery Lists -->
                    <div class="space-y-8">
                        <!-- In Progress -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <span class="h-3 w-3 bg-yellow-400 rounded-full mr-2"></span>
                                Sürmekte ({{ $discoveries['in_progress']->count() }})
                            </h3>
                            @if($discoveries['in_progress']->isNotEmpty())
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($discoveries['in_progress'] as $discovery)
                                        <div class="border rounded-lg p-4 bg-yellow-50">
                                            <div class="flex justify-between items-start mb-2">
                                                <h4 class="font-medium">{{ $discovery->customer_name }}</h4>
                                                <span class="text-sm text-gray-500">{{ $discovery->created_at->format('M d, Y') }}</span>
                                            </div>
                                            <p class="text-sm text-gray-600 mb-2">{{ Str::limit($discovery->discovery, 100) }}</p>
                                            <a href="{{ route('discovery.show', $discovery) }}" class="text-blue-600 hover:text-blue-800 text-sm">Ayrıntıları Görüntüle →</a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 italic">Sürmekte olan iş yok</p>
                            @endif
                        </div>

                        <!-- Pending -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <span class="h-3 w-3 bg-blue-400 rounded-full mr-2"></span>
                                Beklemede ({{ $discoveries['pending']->count() }})
                            </h3>
                            @if($discoveries['pending']->isNotEmpty())
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($discoveries['pending'] as $discovery)
                                        <div class="border rounded-lg p-4 bg-blue-50">
                                            <div class="flex justify-between items-start mb-2">
                                                <h4 class="font-medium">{{ $discovery->customer_name }}</h4>
                                                <span class="text-sm text-gray-500">{{ $discovery->created_at->format('M d, Y') }}</span>
                                            </div>
                                            <p class="text-sm text-gray-600 mb-2">{{ Str::limit($discovery->discovery, 100) }}</p>
                                            <a href="{{ route('discovery.show', $discovery) }}" class="text-blue-600 hover:text-blue-800 text-sm">Ayrıntıları Görüntüle →</a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 italic">Beklemede olan iş yok</p>
                            @endif
                        </div>

                        <!-- Completed -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <span class="h-3 w-3 bg-green-400 rounded-full mr-2"></span>
                                Tamamlandı ({{ $discoveries['completed']->count() }})
                            </h3>
                            @if($discoveries['completed']->isNotEmpty())
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($discoveries['completed'] as $discovery)
                                        <div class="border rounded-lg p-4 bg-green-50">
                                            <div class="flex justify-between items-start mb-2">
                                                <h4 class="font-medium">{{ $discovery->customer_name }}</h4>
                                                <span class="text-sm text-gray-500">{{ $discovery->created_at->format('M d, Y') }}</span>
                                            </div>
                                            <p class="text-sm text-gray-600 mb-2">{{ Str::limit($discovery->discovery, 100) }}</p>
                                            <a href="{{ route('discovery.show', $discovery) }}" class="text-blue-600 hover:text-blue-800 text-sm">Ayrıntıları Görüntüle →</a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 italic">Tamamlanmış iş yok</p>
                            @endif
                        </div>

                        <!-- Cancelled -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <span class="h-3 w-3 bg-red-400 rounded-full mr-2"></span>
                                İptal Edildi ({{ $discoveries['cancelled']->count() }})
                            </h3>
                            @if($discoveries['cancelled']->isNotEmpty())
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($discoveries['cancelled'] as $discovery)
                                        <div class="border rounded-lg p-4 bg-red-50">
                                            <div class="flex justify-between items-start mb-2">
                                                <h4 class="font-medium">{{ $discovery->customer_name }}</h4>
                                                <span class="text-sm text-gray-500">{{ $discovery->created_at->format('M d, Y') }}</span>
                                            </div>
                                            <p class="text-sm text-gray-600 mb-2">{{ Str::limit($discovery->discovery, 100) }}</p>
                                            <a href="{{ route('discovery.show', $discovery) }}" class="text-blue-600 hover:text-blue-800 text-sm">Ayrıntıları Görüntüle →</a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 italic">İptal Edilmiş iş yok</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
