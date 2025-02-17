<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Discovery Details - {{ config('app.name', 'Handi') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        function itemSelector(existingItems = []) {
            return {
                searchQuery: '',
                searchResults: [],
                selectedItems: existingItems.map(item => ({
                    id: item.id,
                    item: item.item,
                    brand: item.brand,
                    price: item.price,
                    quantity: item.pivot?.quantity || 1,
                    custom_price: item.pivot?.custom_price || item.price
                })),

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
                            custom_price: item.price
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
</head>
<body class="bg-gray-100">
    <x-navigation />

    @if (session('success'))
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
            <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4"
                 x-data="{ show: true }"
                 x-show="show"
                 x-init="setTimeout(() => show = false, 3000)">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">Discovery Details</h2>

                        <!-- Status Selector -->
                        <div x-data="{ open: false }" class="relative">
                            <!-- Status Selector Button -->
                            <button @click="open = !open"
                                    class="inline-flex items-center px-4 py-2 border rounded-md {{
                                        $discovery->status === 'pending' ? 'bg-blue-50 text-blue-700 border-blue-300' : (
                                            $discovery->status === 'in_progress' ? 'bg-yellow-50 text-yellow-700 border-yellow-300' : (
                                                $discovery->status === 'completed' ? 'bg-green-50 text-green-700 border-green-300' :
                                                'bg-red-50 text-red-700 border-red-300'
                                            )
                                        )
                                    }}">
                                <span class="mr-2">Status: {{ ucfirst(str_replace('_', ' ', $discovery->status)) }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open"
                                 @click.away="open = false"
                                 class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                <form action="{{ route('discovery.update-status', $discovery) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    @foreach(['pending', 'in_progress', 'completed', 'cancelled'] as $status)
                                        <button type="submit"
                                                name="status"
                                                value="{{ $status }}"
                                                class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100
                                                       {{ $discovery->status === $status ? 'bg-gray-50' : '' }}">
                                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                                        </button>
                                    @endforeach
                                </form>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('discovery.update', $discovery) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
    @csrf
    @method('PATCH')

    <!-- Add this near the top of the form -->
    <div class="mb-6 bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900">Total Cost</h3>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($discovery->total_cost, 2) }}</p>
    </div>

    <!-- Customer Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Customer Name</label>
            <input type="text" name="customer_name" value="{{ old('customer_name', $discovery->customer_name) }}"
                   class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
            @error('customer_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
            <input type="text" name="customer_phone" value="{{ old('customer_phone', $discovery->customer_phone) }}"
                   class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
            @error('customer_phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input type="email" name="customer_email" value="{{ old('customer_email', $discovery->customer_email) }}"
                   class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
            @error('customer_email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="col-span-full">
            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
            <textarea name="address" rows="3"
                      class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                      >{{ old('address', $discovery->address) }}</textarea>
        </div>
    </div>

    <!-- Discovery Details -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Discovery Details</label>
        <textarea name="discovery" rows="4" required
                  class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                  >{{ old('discovery', $discovery->discovery) }}</textarea>
    </div>

    <!-- Todo List -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Todo List</label>
        <textarea name="todo_list" rows="4"
                  class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                  >{{ old('todo_list', $discovery->todo_list) }}</textarea>
    </div>

    <!-- Item Selection -->
    <div x-data="itemSelector({{ json_encode($discovery->items) }})" class="space-y-4">
    <div class="flex justify-between items-center">
        <label class="block text-sm font-medium text-gray-700 mb-2">Items</label>
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
                                <span class="text-gray-600" x-text="'$' + item.price"></span>
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
                            <div class="col-span-4">
                                <p class="font-medium" x-text="item.item"></p>
                                <p class="text-sm text-gray-500" x-text="item.brand"></p>
                                <p class="text-sm text-gray-600" x-text="'Base Price: $' + item.price"></p>
                            </div>
                            <div class="col-span-3">
                                <label class="block text-xs text-gray-500 mb-1">Quantity</label>
                                <input type="number"
                                       x-model.number="item.quantity"
                                       min="1"
                                       class="bg-gray-100 w-20 rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-2 py-1">
                                <input type="hidden" :name="'items['+index+'][id]'" :value="item.id">
                                <input type="hidden" :name="'items['+index+'][quantity]'" :value="item.quantity">
                            </div>
                            <div class="col-span-4">
                                <label class="block text-xs text-gray-500 mb-1">Custom Price</label>
                                <input type="number"
                                       x-model.number="item.custom_price"
                                       step="0.01"
                                       class="bg-gray-100 w-32 rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-2 py-1">
                                <input type="hidden" :name="'items['+index+'][custom_price]'" :value="item.custom_price">
                                <p class="text-xs text-gray-500 mt-1" x-show="item.custom_price != item.price">
                                    Original price: $<span x-text="item.price"></span>
                                </p>
                            </div>
                            <div class="col-span-1 text-right">
                                <button type="button"
                                        @click="removeItem(index)"
                                        class="text-red-600 hover:text-red-800">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 011.414 0L10 8.586l4.293-4.293a1 1 111.414 1.414L11.414 10l4.293 4.293a1 1 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
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
            <label class="block text-sm font-medium text-gray-700 mb-2">Completion Time (days)</label>
            <input type="number" name="completion_time" value="{{ old('completion_time', $discovery->completion_time) }}"
                   class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                   min="1">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Offer Valid Until</label>
            <input type="date" name="offer_valid_until" value="{{ old('offer_valid_until', optional($discovery->offer_valid_until)->format('Y-m-d')) }}"
                   class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Service Cost</label>
            <input type="number" name="service_cost" value="{{ old('service_cost', $discovery->service_cost) }}"
                   class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                   min="0" step="0.01">
        </div>

        <!-- Add these in the Cost Information section -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Transportation Cost</label>
            <input type="number" name="transportation_cost"
                   value="{{ old('transportation_cost', $discovery->transportation_cost) }}"
                   class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                   min="0" step="0.01">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Labor Cost</label>
            <input type="number" name="labor_cost"
                   value="{{ old('labor_cost', $discovery->labor_cost) }}"
                   class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                   min="0" step="0.01">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Extra Fee</label>
            <input type="number" name="extra_fee"
                   value="{{ old('extra_fee', $discovery->extra_fee) }}"
                   class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                   min="0" step="0.01">
        </div>

        <!-- Add all other cost fields similarly -->

        <!-- Notes -->
        <div class="col-span-full">
            <label class="block text-sm font-medium text-gray-700 mb-2">Note to Customer</label>
            <textarea name="note_to_customer" rows="3"
                      class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                      >{{ old('note_to_customer', $discovery->note_to_customer) }}</textarea>
        </div>

        <div class="col-span-full">
            <label class="block text-sm font-medium text-gray-700 mb-2">Internal Note</label>
            <textarea name="note_to_handi" rows="3"
                      class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                      >{{ old('note_to_handi', $discovery->note_to_handi) }}</textarea>
        </div>
    </div>

    <!-- Add this after the Notes section in show.blade.php -->
    <div>
        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
        <input type="text" name="payment_method" id="payment_method"
               value="{{ old('payment_method', $discovery->payment_method) }}"
               class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
    </div>

    <!-- Current Images -->
    @if($discovery->images)
    <div>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Current Images</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($discovery->images as $image)
            <div class="relative group">
                <img src="{{ asset('storage/' . $image) }}" class="h-40 w-full object-cover rounded-lg">
                <button type="button"
                        name="remove_images[]"
                        value="{{ $image }}"
                        class="absolute top-2 right-2 bg-black text-red-500 rounded-full p-2 shadow-xl hover:bg-gray-900 hover:text-red-600 border-2 border-red-500">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Add New Images -->
    <div x-data="imageUploader()" class="space-y-4">
        <!-- Copy the image uploader component from create form -->
    </div>

    <!-- Submit Button -->
    <div class="flex justify-end space-x-3">
        <button type="submit"
                class="bg-blue-500 text-white px-6 py-3 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-lg font-medium">
            Update Discovery
        </button>
    </div>
</form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
