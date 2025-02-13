<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Add Item - {{ config('app.name', 'Laravel') }}</title>
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
            <!-- Success Message -->
            @if (session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm"
                     x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 3000)">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ session('success') }}
                        </div>
                        <button @click="show = false" class="text-green-700 hover:text-green-500">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Add New Item</h2>

                    <form method="POST" action="{{ route('items.store') }}" class="space-y-8">
                        @csrf

                        <div class="mb-6">
                            <label for="item" class="block text-sm font-medium text-gray-700 mb-2">Item Name</label>
                            <input type="text" name="item" id="item"
                                class="bg-gray-100 mt-1 block rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                value="{{ old('item') }}" required>
                            @error('item')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="brand" class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                            <input type="text" name="brand" id="brand"
                                class="bg-gray-100 mt-1 block rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                value="{{ old('brand') }}" required>
                            @error('brand')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-8">
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                            <input type="number" step="0.01" name="price" id="price"
                                class="bg-gray-100 mt-1 block rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                value="{{ old('price') }}" required>
                            @error('price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-start mt-10">
                            <button type="submit"
                                class="bg-blue-500 text-white px-6 py-3 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-lg font-medium">
                                Add Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Alpine.js for the auto-dismiss functionality -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
