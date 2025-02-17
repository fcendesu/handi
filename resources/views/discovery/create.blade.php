<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Discovery - {{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <x-navigation />

    <!-- Success Message -->
    @if (session('success'))
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
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
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 111.414 1.414L11.414 10l4.293 4.293a1 1 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">Create New Discovery</h2>
                        <a href="{{ route('discovery') }}"
                           class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            Back to Discoveries
                        </a>
                    </div>

                    <form action="{{ route('discovery.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                        @csrf

                        <!-- Customer Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-2">Customer Name</label>
                                <input type="text" name="customer_name" id="customer_name"
                                       class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                       value="{{ old('customer_name') }}" required>
                                @error('customer_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="text" name="customer_phone" id="customer_phone"
                                       class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                       value="{{ old('customer_phone') }}" required>
                                @error('customer_phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" name="customer_email" id="customer_email"
                                       class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                       value="{{ old('customer_email') }}" required>
                                @error('customer_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Discovery Details -->
                        <div>
                            <label for="discovery" class="block text-sm font-medium text-gray-700 mb-2">Discovery Details</label>
                            <textarea name="discovery" id="discovery" rows="4"
                                      class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                      required>{{ old('discovery') }}</textarea>
                            @error('discovery')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="todo_list" class="block text-sm font-medium text-gray-700 mb-2">Todo List</label>
                            <textarea name="todo_list" id="todo_list" rows="4"
                                      class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">{{ old('todo_list') }}</textarea>
                            @error('todo_list')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Item Selection -->
                        <div x-data="itemSelector()" class="space-y-4">
                            <div class="flex justify-between items-center">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Add Items</label>
                                <div class="relative w-64">
                                    <input type="text"
                                           x-model="searchQuery"
                                           @input.debounce.300ms="searchItems()"
                                           placeholder="Search items..."
                                           class="bg-gray-100 w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">

                                    <!-- Search Results Dropdown -->
                                    <div x-show="searchResults.length > 0"
                                         class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg border border-gray-200">
                                        <ul class="max-h-60 overflow-auto">
                                            <template x-for="item in searchResults" :key="item.id">
                                                <li class="p-3 hover:bg-gray-50 cursor-pointer" @click="addItem(item)">
                                                    <div class="flex justify-between items-center">
                                                        <div>
                                                            <span x-text="item.item" class="font-medium"></span>
                                                            <span class="text-sm text-gray-500" x-text="' - ' + item.brand"></span>
                                                        </div>
                                                        <span class="text-gray-600" x-text="item.price"></span>
                                                    </div>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Selected Items List -->
                            <div x-show="selectedItems.length > 0" class="mt-4">
                                <div class="bg-white rounded-lg border border-gray-200">
                                    <ul class="divide-y divide-gray-200">
                                        <template x-for="(item, index) in selectedItems" :key="index">
                                            <li class="p-4">
                                                <div class="grid grid-cols-12 gap-4 items-center">
                                                    <div class="col-span-5">
                                                        <p class="font-medium" x-text="item.item"></p>
                                                        <p class="text-sm text-gray-500" x-text="item.brand"></p>
                                                    </div>
                                                    <div class="col-span-2">
                                                        <label class="block text-xs text-gray-500 mb-1">Quantity</label>
                                                        <input type="number"
                                                               x-model="item.quantity"
                                                               min="1"
                                                               class="bg-gray-100 w-20 rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-2 py-1">
                                                        <input type="hidden" :name="'items['+index+'][id]'" :value="item.id">
                                                        <input type="hidden" :name="'items['+index+'][quantity]'" :value="item.quantity">
                                                    </div>
                                                    <div class="col-span-4">
                                                        <label class="block text-xs text-gray-500 mb-1">Custom Price (optional)</label>
                                                        <input type="number"
                                                               x-model="item.custom_price"
                                                               :placeholder="'Default: ' + item.price"
                                                               step="0.01"
                                                               class="bg-gray-100 w-32 rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-2 py-1">
                                                        <input type="hidden" :name="'items['+index+'][custom_price]'" :value="item.custom_price">
                                                    </div>
                                                    <div class="col-span-1 text-right">
                                                        <button type="button"
                                                                @click="removeItem(index)"
                                                                class="text-red-600 hover:text-red-800">
                                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 111.414 1.414L11.414 10l4.293 4.293a1 1 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="note_to_customer" class="block text-sm font-medium text-gray-700 mb-2">Note to Customer</label>
                            <textarea name="note_to_customer" id="note_to_customer" rows="3"
                                      class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">{{ old('note_to_customer') }}</textarea>
                        </div>

                        <div>
                            <label for="note_to_handi" class="block text-sm font-medium text-gray-700 mb-2">Internal Note</label>
                            <textarea name="note_to_handi" id="note_to_handi" rows="3"
                                      class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">{{ old('note_to_handi') }}</textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-3 ">
                            <button type="submit"
                                    class="bg-blue-500 text-white px-6 py-3 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-lg font-medium">
                                Create Discovery
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function itemSelector() {
        return {
            searchQuery: '',
            searchResults: [],
            selectedItems: [],

            async searchItems() {
                if (this.searchQuery.length < 2) {
                    this.searchResults = [];
                    return;
                }

                try {
                    const response = await fetch(`/items/search-for-discovery?query=${encodeURIComponent(this.searchQuery)}`);
                    const data = await response.json();
                    this.searchResults = data.items;
                } catch (error) {
                    console.error('Error searching items:', error);
                    this.searchResults = [];
                }
            },

            addItem(item) {
                if (!this.selectedItems.find(i => i.id === item.id)) {
                    this.selectedItems.push({
                        ...item,
                        quantity: 1,
                        custom_price: null
                    });
                }
                this.searchQuery = '';
                this.searchResults = [];
            },

            removeItem(index) {
                this.selectedItems.splice(index, 1);
            }
        }
    }
    </script>
</body>
</html>
