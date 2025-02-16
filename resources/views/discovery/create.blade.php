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
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
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
                            <!-- Input fields with updated styling -->
                            <div class="mb-6">
                                <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-2">Customer Name</label>
                                <input type="text" name="customer_name" id="customer_name"
                                       class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                       value="{{ old('customer_name') }}" required>
                                @error('customer_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label for="customer_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="text" name="customer_number" id="customer_number"
                                       class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                       value="{{ old('customer_number') }}" required>
                                @error('customer_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" name="customer_email" id="customer_email"
                                       class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                       value="{{ old('customer_email') }}" required>
                                @error('customer_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                                <select name="priority" id="priority"
                                        class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="0" {{ old('priority') == '0' ? 'selected' : '' }}>No Priority</option>
                                    <option value="1" {{ old('priority') == '1' ? 'selected' : '' }}>Primary</option>
                                </select>
                                @error('priority')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Discovery Details -->
                        <div class="space-y-6">
                            <div class="mb-6">
                                <label for="discovery" class="block text-sm font-medium text-gray-700 mb-2">Discovery Details</label>
                                <textarea name="discovery" id="discovery" rows="4"
                                          class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                          required>{{ old('discovery') }}</textarea>
                                @error('discovery')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label for="todolist" class="block text-sm font-medium text-gray-700 mb-2">Todo List</label>
                                <textarea name="todolist" id="todolist" rows="4"
                                          class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">{{ old('todolist') }}</textarea>
                                @error('todolist')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- After todolist section and before costs section -->
                        <div class="mb-6" x-data="itemSelector()">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Items</label>

                            <!-- Search Input -->
                            <div class="mb-4">
                                <input type="text"
                                       x-model="searchQuery"
                                       @input.debounce.300ms="searchItems"
                                       placeholder="Search items..."
                                       class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                            </div>

                            <!-- Search Results -->
                            <div class="mb-4" x-show="searchResults.length > 0">
                                <div class="bg-white border rounded-md shadow-sm max-h-60 overflow-y-auto">
                                    <template x-for="item in searchResults" :key="item.id">
                                        <div class="p-2 hover:bg-gray-50 cursor-pointer border-b"
                                             @click="addItem(item)">
                                            <div x-text="item.item + ' - ' + item.brand"></div>
                                            <div class="text-sm text-gray-500" x-text="'Price: ' + item.price"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Selected Items -->
                            <div class="border rounded-md p-4" x-show="selectedItems.length > 0">
                                <h4 class="font-medium mb-2">Selected Items</h4>
                                <div class="space-y-4">
                                    <template x-for="(item, index) in selectedItems" :key="index">
                                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded">
                                            <div class="flex-1">
                                                <div x-text="item.item + ' - ' + item.brand"></div>
                                                <div class="mt-2 flex items-center space-x-4">
                                                    <div class="flex items-center">
                                                        <label class="text-sm mr-2">Quantity:</label>
                                                        <input type="number"
                                                               x-model="item.quantity"
                                                               min="1"
                                                               class="w-20 bg-gray-100 rounded border-gray-300"
                                                               @input="updateItem(index)">
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <span class="text-sm text-gray-500">Original Price:</span>
                                                        <span class="text-sm text-gray-500" x-text="item.price"></span>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <label class="text-sm mr-2">Custom Price:</label>
                                                        <input type="number"
                                                               x-model="item.custom_price"
                                                               class="w-24 bg-gray-100 rounded border-gray-300"
                                                               @input="updateItem(index)">
                                                    </div>
                                                </div>
                                                <input type="hidden" :name="'items['+index+'][id]'" :value="item.id">
                                                <input type="hidden" :name="'items['+index+'][quantity]'" :value="item.quantity">
                                                <input type="hidden" :name="'items['+index+'][custom_price]'" :value="item.custom_price">
                                            </div>
                                            <button type="button"
                                                    @click="removeItem(index)"
                                                    class="text-red-500 hover:text-red-700">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Costs -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="mb-6">
                                <label for="service_cost" class="block text-sm font-medium text-gray-700 mb-2">Service Cost</label>
                                <input type="number" name="service_cost" id="service_cost"
                                       class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                       value="{{ old('service_cost', 0) }}"
                                       onfocus="if(this.value=='0'){this.value=''}">
                            </div>

                            <div class="mb-6">
                                <label for="transportation_cost" class="block text-sm font-medium text-gray-700 mb-2">Transportation Cost</label>
                                <input type="number" name="transportation_cost" id="transportation_cost"
                                       class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                       value="{{ old('transportation_cost', 0) }}"
                                       onfocus="if(this.value=='0'){this.value=''}">
                            </div>

                            <div class="mb-6">
                                <label for="labor_cost" class="block text-sm font-medium text-gray-700 mb-2">Labor Cost</label>
                                <input type="number" name="labor_cost" id="labor_cost"
                                       class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                       value="{{ old('labor_cost', 0) }}"
                                       onfocus="if(this.value=='0'){this.value=''}">
                            </div>
                        </div>

                        <!-- Additional Costs -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="mb-6">
                                <label for="extra_fee" class="block text-sm font-medium text-gray-700 mb-2">Extra Fee</label>
                                <input type="number" name="extra_fee" id="extra_fee"
                                       class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                       value="{{ old('extra_fee', 0) }}"
                                       onfocus="if(this.value=='0'){this.value=''}">
                            </div>

                            <div class="mb-6">
                                <label for="discount_amount" class="block text-sm font-medium text-gray-700 mb-2">Discount Amount</label>
                                <input type="number" name="discount_amount" id="discount_amount"
                                       class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                       value="{{ old('discount_amount', 0) }}"
                                       onfocus="if(this.value=='0'){this.value=''}">
                            </div>

                            <div class="mb-6">
                                <label for="discount_rate" class="block text-sm font-medium text-gray-700 mb-2">Discount Rate (%)</label>
                                <input type="number" name="discount_rate" id="discount_rate"
                                       class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                       value="{{ old('discount_rate', 0) }}"
                                       min="0" max="100"
                                       onfocus="if(this.value=='0'){this.value=''}">
                            </div>
                        </div>

                        <!-- Status and Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="mb-6">
                                <label for="completion_time" class="block text-sm font-medium text-gray-700 mb-2">Completion (Days)</label>
                                <input type="number" name="completion_time" id="completion_time"
                                       class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                       value="{{ old('completion_time') }}"
                                       min="1">
                            </div>

                            <div class="mb-6">
                                <label for="offer_valid_until" class="block text-sm font-medium text-gray-700 mb-2">Offer Valid Until</label>
                                <input type="date" name="offer_valid_until" id="offer_valid_until"
                                       class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                       value="{{ old('offer_valid_until') }}">
                            </div>
                        </div>

                        <!-- Status and Notes -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="mb-6">
                                <label for="note_to_customer" class="block text-sm font-medium text-gray-700 mb-2">Note to Customer</label>
                                <textarea name="note_to_customer" id="note_to_customer" rows="4"
                                          class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">{{ old('note_to_customer') }}</textarea>
                            </div>

                            <div class="mb-6">
                                <label for="note_to_handi" class="block text-sm font-medium text-gray-700 mb-2">Internal Note</label>
                                <textarea name="note_to_handi" id="note_to_handi" rows="4"
                                          class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">{{ old('note_to_handi') }}</textarea>
                            </div>
                        </div>

                        <!-- Image Preview and Selection -->
                        <div class="mb-6" x-data="imageHandler()">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Images</label>
                            <input type="file"
                                   name="images[]"
                                   multiple
                                   @change="previewImages($event)"
                                   class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2
                                          file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                                          file:text-sm file:font-semibold file:bg-blue-50
                                          file:text-blue-700 hover:file:bg-blue-100">

                            <!-- Image Preview Grid -->
                            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4" x-show="previews.length > 0">
                                <template x-for="(preview, index) in previews" :key="index">
                                    <div class="relative group">
                                        <img :src="preview.url"
                                             class="h-32 w-full object-cover rounded-lg border-2 border-gray-200">
                                        <button type="button"
                                                @click="removeImage(index)"
                                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1
                                                       opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
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

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        function imageHandler() {
            return {
                previews: [],

                previewImages(event) {
                    const files = event.target.files;
                    this.previews = [];

                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.previews.push({
                                    url: e.target.result,
                                    file: file
                                });
                            };
                            reader.readAsDataURL(file);
                        }
                    }
                },

                removeImage(index) {
                    this.previews.splice(index, 1);

                    // Reset the file input to reflect removed images
                    const input = document.querySelector('input[type="file"]');
                    const dt = new DataTransfer();

                    this.previews.forEach(preview => {
                        dt.items.add(preview.file);
                    });

                    input.files = dt.files;
                }
            }
        }

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
                        const response = await fetch(`/items/search-for-discovery?query=${this.searchQuery}`);
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
                            custom_price: item.price // Initialize custom price with original price
                        });
                    }
                    this.searchResults = [];
                    this.searchQuery = '';
                },

                removeItem(index) {
                    this.selectedItems.splice(index, 1);
                },

                updateItem(index) {
                    // Ensure valid values
                    this.selectedItems[index].quantity = Math.max(1, this.selectedItems[index].quantity);
                    if (this.selectedItems[index].custom_price === '') {
                        this.selectedItems[index].custom_price = this.selectedItems[index].price;
                    }
                }
            }
        }
    </script>
</body>
</html>
