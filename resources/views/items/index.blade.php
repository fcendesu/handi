<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Items - {{ config('app.name', 'Laravel') }}</title>
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
                        <h2 class="text-2xl font-semibold text-gray-800">Items</h2>
                        <a href="{{ route('items.create') }}"
                           class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Add New Item
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($items as $item)
                            <div class="bg-white p-6 rounded-lg shadow-md">
                                <h3 class="text-lg font-semibold mb-2">{{ $item->item }}</h3>
                                <p class="text-gray-600 mb-2">Brand: {{ $item->brand }}</p>
                                <p class="text-gray-600 mb-4">Price: ${{ number_format($item->price, 2) }}</p>
                                <div class="flex space-x-2">
                                    <a href="{{ route('items.edit', $item->id) }}"
                                       class="bg-yellow-500 text-white px-3 py-1 rounded-md hover:bg-yellow-600">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('items.destroy', $item->id) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600"
                                                onclick="return confirm('Are you sure you want to delete this item?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
