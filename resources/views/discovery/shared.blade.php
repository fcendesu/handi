<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keşif Detayları</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <!-- Customer Details -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold mb-6">{{ $discovery->customer_name }}</h1>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="font-medium text-gray-700">Keşif Detayı</h3>
                            <p class="mt-2 text-gray-600">{{ $discovery->discovery }}</p>
                        </div>
                    </div>
                </div>

                <!-- Cost Summary -->
                <div class="mb-8 bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-medium mb-4">Masraf Detayları</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Toplam:</span>
                            <span class="font-medium">{{ number_format($discovery->total_cost, 2) }} TL</span>
                        </div>
                    </div>
                </div>

                @if($discovery->images)
                    <div class="mb-8">
                        <h3 class="text-lg font-medium mb-4">Fotoğraflar</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($discovery->images as $image)
                                <img src="{{ asset('storage/' . $image) }}"
                                     class="w-full h-40 object-cover rounded-lg shadow-sm">
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
