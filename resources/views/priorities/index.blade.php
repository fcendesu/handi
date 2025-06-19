<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Öncelik Yönetimi</title>
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
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">Öncelik Rozetleri</h2>
                        <a href="{{ route('priorities.create') }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Yeni Öncelik Ekle
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <strong>İpucu:</strong> Öncelik rozetleri keşiflerinizi organize etmek için kullanılır. 
                            Seviye numarası ne kadar yüksekse, öncelik o kadar kritik kabul edilir. 
                            Renkleri özelleştirerek görsel olarak ayrım yapabilirsiniz.
                        </p>
                    </div>

                    @if(count($priorities) == 0)
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Öncelik Yok</h3>
                            <p class="mt-1 text-sm text-gray-500">İlk öncelik rozetinizi oluşturarak başlayın.</p>
                            <div class="mt-6">
                                <a href="{{ route('priorities.create') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                    Yeni Öncelik Ekle
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($priorities as $priority)
                                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-white" 
                                              style="background-color: {{ $priority->color }};">
                                            Seviye {{ $priority->level }}
                                        </span>
                                        @if($priority->is_default)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Varsayılan
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $priority->name }}</h3>
                                    
                                    @if($priority->description)
                                        <p class="text-sm text-gray-600 mb-4">{{ $priority->description }}</p>
                                    @endif
                                    
                                    <div class="mb-4">
                                        <div class="flex items-center text-sm text-gray-500">
                                            <span class="mr-2">Renk:</span>
                                            <div class="w-6 h-6 rounded-full border border-gray-300" 
                                                 style="background-color: {{ $priority->color }};"></div>
                                            <span class="ml-2">{{ $priority->color }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="text-xs text-gray-500 mb-4">
                                        {{ $priority->discoveries()->count() }} keşifte kullanılıyor
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        <a href="{{ route('priorities.edit', $priority) }}" 
                                           class="flex-1 bg-yellow-500 hover:bg-yellow-700 text-white text-sm font-bold py-2 px-4 rounded text-center">
                                            Düzenle
                                        </a>
                                        
                                        @if($priority->discoveries()->count() == 0)
                                            <form action="{{ route('priorities.destroy', $priority) }}" method="POST" class="flex-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="w-full bg-red-500 hover:bg-red-700 text-white text-sm font-bold py-2 px-4 rounded"
                                                        onclick="return confirm('Bu önceliği silmek istediğinizden emin misiniz?')">
                                                    Sil
                                                </button>
                                            </form>
                                        @else
                                            <button disabled 
                                                    class="flex-1 bg-gray-300 text-gray-500 text-sm font-bold py-2 px-4 rounded cursor-not-allowed"
                                                    title="Bu öncelik keşiflerde kullanıldığı için silinemez">
                                                Sil
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
