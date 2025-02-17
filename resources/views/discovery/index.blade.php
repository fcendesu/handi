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
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 00-1.414 1.414l2 2a1 1 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                    <button @click="show = false" class="text-green-700 hover:text-green-500">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 011.414 0L10 8.586l4.293-4.293a1 1 111.414 1.414L11.414 10l4.293 4.293a1 1 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
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

                    </div>

                    <form action="{{ route('discovery.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                        @csrf

                        <div class="space-y-8">
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
                                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 011.414 0L10 8.586l4.293-4.293a1 1 111.414 1.414L11.414 10l4.293 4.293a1 1 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
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

                            <!-- Cost Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                                    <label for="completion_time" class="block text-sm font-medium text-gray-700 mb-2">Completion Time (days)</label>
                                    <input type="number" name="completion_time" id="completion_time"
                                           class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                           min="1" value="{{ old('completion_time') }}">
                                    @error('completion_time')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="offer_valid_until" class="block text-sm font-medium text-gray-700 mb-2">Offer Valid Until</label>
                                    <input type="date" name="offer_valid_until" id="offer_valid_until"
                                           class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                           value="{{ old('offer_valid_until') }}">
                                    @error('offer_valid_until')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="service_cost" class="block text-sm font-medium text-gray-700 mb-2">Service Cost</label>
                                    <input type="number" name="service_cost" id="service_cost"
                                           class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                           min="0" step="0.01" value="{{ old('service_cost', 0) }}">
                                    @error('service_cost')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="transportation_cost" class="block text-sm font-medium text-gray-700 mb-2">Transportation Cost</label>
                                    <input type="number" name="transportation_cost" id="transportation_cost"
                                           class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                           min="0" step="0.01" value="{{ old('transportation_cost', 0) }}">
                                    @error('transportation_cost')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="labor_cost" class="block text-sm font-medium text-gray-700 mb-2">Labor Cost</label>
                                    <input type="number" name="labor_cost" id="labor_cost"
                                           class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                           min="0" step="0.01" value="{{ old('labor_cost', 0) }}">
                                    @error('labor_cost')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="extra_fee" class="block text-sm font-medium text-gray-700 mb-2">Extra Fee</label>
                                    <input type="number" name="extra_fee" id="extra_fee"
                                           class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                           min="0" step="0.01" value="{{ old('extra_fee', 0) }}">
                                    @error('extra_fee')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="discount_rate" class="block text-sm font-medium text-gray-700 mb-2">Discount Rate (%)</label>
                                    <input type="number" name="discount_rate" id="discount_rate"
                                           class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                           min="0" max="100" step="0.01" value="{{ old('discount_rate', 0) }}">
                                    @error('discount_rate')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="discount_amount" class="block text-sm font-medium text-gray-700 mb-2">Discount Amount</label>
                                    <input type="number" name="discount_amount" id="discount_amount"
                                           class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                           min="0" step="0.01" value="{{ old('discount_amount', 0) }}">
                                    @error('discount_amount')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
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

                            <!-- Payment Method -->
                            <div>
                                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                <input type="text" name="payment_method" id="payment_method"
                                       class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                       value="{{ old('payment_method') }}">
                                @error('payment_method')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Image Upload with Preview -->
                            <div x-data="imageUploader()">
                                <div class="flex justify-between items-center mb-2">
                                    <label for="images" class="block text-sm font-medium text-gray-700">Images</label>
                                    <button type="button"
                                            @click="clearAllImages()"
                                            x-show="previews.length > 0"
                                            class="text-sm text-red-600 hover:text-red-800">
                                        Clear All
                                    </button>
                                </div>
                                <input type="file"
                                       name="images[]"
                                       id="images"
                                       multiple
                                       accept="image/*"
                                       @change="previewImages($event)"
                                       class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                @error('images.*')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                <!-- Image Preview Grid -->
                                <div x-show="previews.length > 0" class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    <template x-for="(preview, index) in previews" :key="index">
                                        <div class="relative">
                                            <img :src="preview" class="h-40 w-full object-cover rounded-lg">
                                            <button type="button"
                                                    @click="removeImage(index)"
                                                    class="absolute top-2 right-2 bg-red-600 text-red-500 rounded-full p-2 shadow-xl hover:bg-gray-900 hover:text-red-600 border-2 border-red-500">
                                                <svg class="h-2 w-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end space-x-3">
                                <button type="submit"
                                        class="bg-blue-500 text-white px-6 py-3 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-lg font-medium">
                                    Create Discovery
                                </button>
                            </div>
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

    function imageUploader() {
        return {
            previews: [],
            fileInput: null,

            previewImages(event) {
                this.fileInput = event.target;
                this.previews = [];

                Array.from(event.target.files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.previews.push(e.target.result);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            },

            removeImage(index) {
                // Remove from previews array
                this.previews.splice(index, 1);

                // Remove from file input
                const dt = new DataTransfer();
                const files = Array.from(this.fileInput.files);
                files.splice(index, 1);
                files.forEach(file => dt.items.add(file));
                this.fileInput.files = dt.files;

                // Clear input if no files left
                if (files.length === 0) {
                    this.fileInput.value = '';
                }
            },

            clearAllImages() {
                this.previews = [];
                this.fileInput.value = '';
            }
        }
    }
    </script>
</body>
</html>
