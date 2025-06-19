<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mülk Yönetimi - İşler</title>
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
                    <div class="flex justify-between items-center mb-6">                        <h1 class="text-3xl font-bold text-gray-900">Mülk Yönetimi</h1>
                        @can('create', App\Models\Property::class)
                            <a href="{{ route('properties.create') }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                Yeni Mülk Ekle
                            </a>
                        @endcan
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                        @if($properties->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Mülk Adı
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Sahip Adı
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                E-posta
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Telefon
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Adres
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Şehir
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Harita Konumu
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                İşlemler
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($properties as $property)
                                            <tr class="hover:bg-gray-50">                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $property->name }}</div>
                                                    @if($property->notes)
                                                        <div class="text-sm text-gray-500">{{ Str::limit($property->notes, 50) }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $property->owner_name ?? 'Belirtilmemiş' }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $property->owner_email ?? '-' }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $property->owner_phone ?? '-' }}
                                                    </div>
                                                </td>                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-900">{{ $property->full_address }}</div>
                                                    @if($property->district)
                                                        <div class="text-sm text-gray-500">{{ $property->district }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        {{ $property->city }}
                                                    </span>
                                                </td>                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    @if($property->latitude && $property->longitude)
                                                        <span class="text-green-600">✓ Mevcut</span>
                                                    @else
                                                        <span class="text-gray-400">Ayarlanmamış</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex space-x-2">                                                        @can('view', $property)
                                                            <a href="{{ route('properties.show', $property) }}" 
                                                               class="text-blue-600 hover:text-blue-900">Görüntüle</a>
                                                        @endcan
                                                        @can('update', $property)
                                                            <a href="{{ route('properties.edit', $property) }}" 
                                                               class="text-indigo-600 hover:text-indigo-900">Düzenle</a>
                                                        @endcan
                                                        @can('delete', $property)
                                                            <form action="{{ route('properties.destroy', $property) }}" 
                                                                  method="POST" 
                                                                  class="inline"
                                                                  onsubmit="return confirm('Bu mülkü silmek istediğinizden emin misiniz?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                                    Sil
                                                                </button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                                {{ $properties->links() }}
                            </div>
                        @else                            <div class="text-center py-12">
                                <div class="text-gray-500 text-lg mb-4">Hiç mülk bulunamadı</div>
                                <p class="text-gray-400 mb-6">Başlamak için ilk mülkünüzü oluşturun</p>
                                @can('create', App\Models\Property::class)
                                    <a href="{{ route('properties.create') }}" 
                                       class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                        İlk Mülkü Ekle
                                    </a>
                                @endcan
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
