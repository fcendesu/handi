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
                    custom_price: item.pivot?.custom_price || item.price,
                    is_existing: true
                })),

                async searchItems() {
                    if (this.searchQuery.length < 2) {
                        this.searchResults = [];
                        return;
                    }

                    try {
                        const response = await fetch(
                            `/items/search-for-discovery?query=${encodeURIComponent(this.searchQuery)}`);
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
                            custom_price: item.price,
                            is_existing: false
                        });
                    }
                    this.searchQuery = '';
                    this.searchResults = [];
                },

                removeItem(index) {
                    // Remove the item from the selectedItems array
                    this.selectedItems.splice(index, 1);
                }
            }
        }

        function imageUploader() {
            return {
                previews: [],
                fileInput: null,

                init() {
                    // Initialize with existing images if any
                    if (this.$el.dataset.existingImages) {
                        const existingImages = JSON.parse(this.$el.dataset.existingImages);
                        this.previews = existingImages.map(img => `/storage/${img}`);
                    }
                },

                previewImages(event) {
                    this.fileInput = event.target;
                    const newPreviews = [];

                    Array.from(event.target.files).forEach(file => {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                newPreviews.push(e.target.result);
                                this.previews = [...this.previews, e.target.result];
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                },

                removeImage(index, imagePath = null) {
                    if (imagePath) {
                        // If it's an existing image, mark it for removal
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'remove_images[]';
                        input.value = imagePath;
                        this.$el.appendChild(input);
                    } else {
                        // If it's a new image, remove from file input
                        const dt = new DataTransfer();
                        const files = Array.from(this.fileInput.files);
                        files.splice(index - (this.$el.dataset.existingImages ? JSON.parse(this.$el.dataset.existingImages)
                            .length : 0), 1);
                        files.forEach(file => dt.items.add(file));
                        this.fileInput.files = dt.files;
                    }
                    this.previews.splice(index, 1);
                },

                clearAllImages() {
                    this.previews = [];
                    if (this.fileInput) {
                        this.fileInput.value = '';
                    }
                    // Add hidden inputs to remove all existing images
                    if (this.$el.dataset.existingImages) {
                        JSON.parse(this.$el.dataset.existingImages).forEach(img => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'remove_images[]';
                            input.value = img;
                            this.$el.appendChild(input);
                        });
                    }
                }
            }
        }

        function paymentMethodSelector() {
            return {
                paymentMethods: [],
                selectedPaymentMethodId: '',

                async loadPaymentMethods() {
                    try {
                        const response = await fetch('/api/payment-methods');
                        const data = await response.json();
                        this.paymentMethods = data;
                    } catch (error) {
                        console.error('Error loading payment methods:', error);
                        this.paymentMethods = [];
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-100">
    <x-navigation />

    @if (session('success'))
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
            <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" x-data="{ show: true }"
                x-show="show" x-init="setTimeout(() => show = false, 3000)">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">Keşif Raporu</h2>
                        <div class="flex space-x-4">
                            <!-- Edit Toggle Button -->
                            <button type="button" x-data="{ editMode: false }"
                                @click="editMode = !editMode; $dispatch('toggle-edit-mode')"
                                class="inline-flex items-center px-4 py-2 border rounded-md"
                                :class="editMode ? 'bg-gray-100 text-gray-700 border-gray-300' :
                                    'bg-blue-50 text-blue-700 border-blue-300'">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                                <span x-text="editMode ? 'Düzenlemeyi İptal Et' : 'Düzenle'"></span>
                            </button>

                            <!-- Status Selector -->
                            <div x-data="{ open: false }" class="relative">
                                <!-- Status Selector Button -->
                                <button @click="open = !open"
                                    class="inline-flex items-center px-4 py-2 border rounded-md {{ $discovery->status === 'pending'
                                        ? 'bg-blue-50 text-blue-700 border-blue-300'
                                        : ($discovery->status === 'in_progress'
                                            ? 'bg-yellow-50 text-yellow-700 border-yellow-300'
                                            : ($discovery->status === 'completed'
                                                ? 'bg-green-50 text-green-700 border-green-300'
                                                : 'bg-red-50 text-red-700 border-red-300')) }}">
                                    <span class="mr-2">Durum:
                                        {{ $discovery->status === 'pending'
                                            ? 'Beklemede'
                                            : ($discovery->status === 'in_progress'
                                                ? 'Sürmekte'
                                                : ($discovery->status === 'completed'
                                                    ? 'Tamamlandı'
                                                    : 'İptal Edildi')) }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" @click.away="open = false"
                                    class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                    <form action="{{ route('discovery.update-status', $discovery) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        @php
                                            $statuses = [
                                                'pending' => 'Beklemede',
                                                'in_progress' => 'Sürmekte',
                                                'completed' => 'Tamamlandı',
                                                'cancelled' => 'İptal Edildi',
                                            ];
                                        @endphp
                                        @foreach ($statuses as $status => $label)
                                            <button type="submit" name="status" value="{{ $status }}"
                                                class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100
                                                           {{ $discovery->status === $status ? 'bg-gray-50' : '' }}">
                                                {{ $label }}
                                            </button>
                                        @endforeach
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('discovery.update', $discovery) }}" method="POST"
                        enctype="multipart/form-data" class="space-y-8" x-data="{ editMode: false }"
                        @toggle-edit-mode.window="editMode = !editMode">
                        @csrf
                        @method('PATCH')

                        <div class="flex items-center space-x-2 mb-4">
                            <input type="text" value="{{ $discovery->share_url }}"
                                class="bg-gray-50 px-4 py-2 rounded border text-sm flex-1" readonly>
                            <button type="button"
                                onclick="navigator.clipboard.writeText('{{ $discovery->share_url }}').then(() => alert('Link kopyalandı!'))"
                                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                                Paylaşım Linki Kopyala
                            </button>
                        </div>

                        <!-- Add this near the top of the form -->
                        <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900">Toplam Masraf</h3>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($discovery->total_cost, 2) }}
                            </p>
                        </div>

                        <!-- Customer Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Müşteri Adı</label>
                                <input type="text" name="customer_name"
                                    value="{{ old('customer_name', $discovery->customer_name) }}"
                                    :disabled="!editMode"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                    :class="{ 'bg-gray-50': !editMode }">
                                @error('customer_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Telefon Numarası</label>
                                <input type="text" name="customer_phone"
                                    value="{{ old('customer_phone', $discovery->customer_phone) }}"
                                    :disabled="!editMode"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                    :class="{ 'bg-gray-50': !editMode }">
                                @error('customer_phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" name="customer_email"
                                    value="{{ old('customer_email', $discovery->customer_email) }}"
                                    :disabled="!editMode"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                    :class="{ 'bg-gray-50': !editMode }">
                                @error('customer_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="col-span-full">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Adres</label>
                                <textarea name="address" rows="3" :disabled="!editMode"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                    :class="{ 'bg-gray-50': !editMode }">{{ old('address', $discovery->address) }}</textarea>
                            </div>
                        </div>

                        <!-- Discovery Details -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Keşif Detayı</label>
                            <textarea name="discovery" rows="4" required :disabled="!editMode"
                                class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                :class="{ 'bg-gray-50': !editMode }">{{ old('discovery', $discovery->discovery) }}</textarea>
                        </div>

                        <!-- Todo List -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Yapılacaklar Listesi</label>
                            <textarea name="todo_list" rows="4" :disabled="!editMode"
                                class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                :class="{ 'bg-gray-50': !editMode }">{{ old('todo_list', $discovery->todo_list) }}</textarea>
                        </div>

                        <!-- Priority Display -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Öncelik Seviyesi</label>
                            @php
                                $turkishPriorityLabels = [
                                    \App\Models\Discovery::PRIORITY_LOW => 'Yok',
                                    \App\Models\Discovery::PRIORITY_MEDIUM => 'Var',
                                    \App\Models\Discovery::PRIORITY_HIGH => 'Acil',
                                ];
                                $priorityLabel = $turkishPriorityLabels[$discovery->priority] ?? 'Yok';
                                $priorityColor =
                                    $discovery->priority == \App\Models\Discovery::PRIORITY_HIGH
                                        ? 'text-red-600 font-semibold'
                                        : ($discovery->priority == \App\Models\Discovery::PRIORITY_MEDIUM
                                            ? 'text-yellow-600 font-medium'
                                            : 'text-gray-600');
                            @endphp
                            <div
                                class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 px-4 py-2 {{ $priorityColor }}">
                                {{ $priorityLabel }}
                            </div>
                        </div>

                        <!-- Work Group Display -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">İş Grubu</label>
                            <div class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 px-4 py-2">
                                @if ($discovery->workGroup)
                                    <div class="flex items-center">
                                        <span
                                            class="font-medium text-gray-900">{{ $discovery->workGroup->name }}</span>
                                        @if ($discovery->workGroup->description)
                                            <span class="ml-2 text-sm text-gray-600">-
                                                {{ $discovery->workGroup->description }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-500 italic">Atanmış iş grubu yok</span>
                                @endif
                            </div>
                        </div>

                        <!-- Item Selection -->
                        <div x-data="itemSelector({{ json_encode($discovery->items) }})" class="space-y-4">
                            <div class="flex justify-between items-center">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Malzemeler</label>
                                <div class="relative w-64" x-show="editMode">
                                    <input type="text" x-model="searchQuery" @input.debounce.300ms="searchItems()"
                                        placeholder="Malzeme Arama..." :disabled="!editMode"
                                        class="bg-gray-100 w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">

                                    <!-- Search Results Dropdown -->
                                    <div x-show="searchResults.length > 0"
                                        class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg border border-gray-200">
                                        <ul class="max-h-60 overflow-auto">
                                            <template x-for="item in searchResults" :key="item.id">
                                                <li class="p-3 hover:bg-gray-50 cursor-pointer"
                                                    @click="addItem(item)">
                                                    <div class="flex justify-between items-center">
                                                        <div>
                                                            <span x-text="item.item" class="font-medium"></span>
                                                            <span class="text-sm text-gray-500"
                                                                x-text="' - ' + item.brand"></span>
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
                                                        <p class="text-sm text-gray-600"
                                                            x-text="'Malzeme Fiyatı: ' + item.price"></p>
                                                    </div>
                                                    <div class="col-span-2">
                                                        <label class="block text-xs text-gray-500 mb-1">Miktar</label>
                                                        <input type="number" x-model.number="item.quantity"
                                                            min="1" :disabled="!editMode"
                                                            class="bg-gray-100 w-20 rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-2 py-1">
                                                        <input type="hidden" :name="'items[' + index + '][id]'"
                                                            :value="item.id">
                                                        <input type="hidden" :name="'items[' + index + '][quantity]'"
                                                            :value="item.quantity">
                                                    </div>
                                                    <div class="col-span-4">
                                                        <label class="block text-xs text-gray-500 mb-1">Farklı
                                                            Fiyat</label>
                                                        <input type="number" x-model.number="item.custom_price"
                                                            step="0.01" :disabled="!editMode"
                                                            class="bg-gray-100 w-32 rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-2 py-1">
                                                        <input type="hidden"
                                                            :name="'items[' + index + '][custom_price]'"
                                                            :value="item.custom_price">
                                                    </div>
                                                    <div class="col-span-1 text-right">
                                                        <button type="button" @click="removeItem(index)"
                                                            x-show="editMode"
                                                            class="bg-red-100 text-red-600 hover:bg-red-200 hover:text-red-700 rounded-full p-2">
                                                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tamamlanma Süresi
                                    (gün)</label>
                                <input type="number" name="completion_time"
                                    value="{{ old('completion_time', $discovery->completion_time) }}"
                                    :disabled="!editMode"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                    :class="{ 'bg-gray-50': !editMode }" min="1">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Teklif Geçerlilik
                                    Tarihi</label>
                                <input type="date" name="offer_valid_until"
                                    value="{{ old('offer_valid_until', optional($discovery->offer_valid_until)->format('Y-m-d')) }}"
                                    :disabled="!editMode"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                    :class="{ 'bg-gray-50': !editMode }">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Servis Masrafı</label>
                                <input type="number" name="service_cost"
                                    value="{{ old('service_cost', $discovery->service_cost) }}"
                                    :disabled="!editMode"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                    :class="{ 'bg-gray-50': !editMode }" min="0" step="0.01">
                            </div>

                            <!-- Add these in the Cost Information section -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ulaşım Masrafı</label>
                                <input type="number" name="transportation_cost"
                                    value="{{ old('transportation_cost', $discovery->transportation_cost) }}"
                                    :disabled="!editMode"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                    :class="{ 'bg-gray-50': !editMode }" min="0" step="0.01">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">İşçilik Masrafı</label>
                                <input type="number" name="labor_cost"
                                    value="{{ old('labor_cost', $discovery->labor_cost) }}" :disabled="!editMode"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                    :class="{ 'bg-gray-50': !editMode }" min="0" step="0.01">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ekstra Masraflar</label>
                                <input type="number" name="extra_fee"
                                    value="{{ old('extra_fee', $discovery->extra_fee) }}" :disabled="!editMode"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                    :class="{ 'bg-gray-50': !editMode }" min="0" step="0.01">
                            </div>

                            <!-- Add all other cost fields similarly -->

                            <!-- Notes -->
                            <div class="col-span-full">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notlar</label>
                                <textarea name="note_to_customer" rows="3" :disabled="!editMode"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                    :class="{ 'bg-gray-50': !editMode }">{{ old('note_to_customer', $discovery->note_to_customer) }}</textarea>
                            </div>

                            <div class="col-span-full">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Özel Not</label>
                                <textarea name="note_to_handi" rows="3" :disabled="!editMode"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                    :class="{ 'bg-gray-50': !editMode }">{{ old('note_to_handi', $discovery->note_to_handi) }}</textarea>
                            </div>

                            <!-- Add this in the Cost Information section -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <!-- ...existing cost fields... -->

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">İndirim Oranı
                                        (%)</label>
                                    <input type="number" name="discount_rate"
                                        value="{{ old('discount_rate', $discovery->discount_rate) }}"
                                        :disabled="!editMode" min="0" max="100" step="0.01"
                                        class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                        :class="{ 'bg-gray-50': !editMode }">
                                    @error('discount_rate')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">İndirim Miktarı</label>
                                    <input type="number" name="discount_amount"
                                        value="{{ old('discount_amount', $discovery->discount_amount) }}"
                                        :disabled="!editMode" min="0" step="0.01"
                                        class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                        :class="{ 'bg-gray-50': !editMode }">
                                    @error('discount_amount')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Move Price Summary outside the grid -->
                        <div class="mt-8 bg-gray-50 p-6 rounded-lg shadow-sm">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Masraflar Detayı</h3>
                            <div class="space-y-3">
                                <!-- Base Costs -->
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Genel Masraflar:</span>
                                    <span
                                        class="font-medium">{{ number_format($discovery->service_cost + $discovery->transportation_cost + $discovery->labor_cost + $discovery->extra_fee, 2) }}</span>
                                </div>

                                <!-- Discount on Base Costs -->
                                @if ($discovery->discount_rate > 0)
                                    <div class="flex justify-between text-red-600">
                                        <span>İndirim ({{ number_format($discovery->discount_rate, 2) }}%):</span>
                                        <span>-{{ number_format($discovery->discount_rate_amount, 2) }}</span>
                                    </div>
                                @endif

                                <!-- Items Total -->
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Malzeme Masrafları:</span>
                                    <span
                                        class="font-medium">{{ number_format($discovery->items->sum(function ($item) {return ($item->pivot->custom_price ?? $item->price) * $item->pivot->quantity;}),2) }}</span>
                                </div>

                                <!-- Fixed Discount Amount -->
                                @if ($discovery->discount_amount > 0)
                                    <div class="flex justify-between text-red-600">
                                        <span>İndirim:</span>
                                        <span>-{{ number_format($discovery->discount_amount, 2) }}</span>
                                    </div>
                                @endif

                                <!-- Final Total -->
                                <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-3 mt-3">
                                    <span>Toplam:</span>
                                    <span>{{ number_format($discovery->total_cost, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method Section -->
                        <div>
                            <label for="payment_method_id" class="block text-sm font-medium text-gray-700 mb-2">Ödeme
                                Şekli</label>

                            <!-- View Mode Display -->
                            <div x-show="!editMode"
                                class="bg-gray-50 mt-1 block w-full rounded-md border-2 border-gray-300 px-4 py-2">
                                <span class="text-gray-900">
                                    @if ($discovery->paymentMethod)
                                        {{ $discovery->paymentMethod->name }}
                                    @else
                                        <span class="text-gray-500">Ödeme şekli seçilmemiş</span>
                                    @endif
                                </span>
                            </div>

                            <!-- Edit Mode Select -->
                            <select name="payment_method_id" id="payment_method_id" x-show="editMode"
                                x-data="paymentMethodSelector()" x-init="loadPaymentMethods();
                                selectedPaymentMethodId = '{{ old('payment_method_id', $discovery->payment_method_id) }}'" x-model="selectedPaymentMethodId"
                                class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                <option value="">Ödeme şekli seçin (opsiyonel)</option>
                                <template x-for="paymentMethod in paymentMethods" :key="paymentMethod.id">
                                    <option :value="paymentMethod.id" x-text="paymentMethod.name"></option>
                                </template>
                            </select>
                            @error('payment_method_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>



                        <!-- Add New Images -->
                        <div x-data="imageUploader()" x-init="init()"
                            data-existing-images='@json($discovery->images ?? [])'>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-medium text-gray-700">Resimler</label>
                                <button type="button" @click="clearAllImages()" x-show="previews.length > 0"
                                    :disabled="!editMode" class="text-sm text-red-600 hover:text-red-800">
                                    Hepsini Sil
                                </button>
                            </div>

                            <!-- Current Images -->
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
                                <template x-for="(preview, index) in previews" :key="index">
                                    <div class="relative">
                                        <img :src="preview" class="h-40 w-full object-cover rounded-lg">
                                        <button type="button"
                                            @click="removeImage(index, preview.includes('/storage/') ? preview.replace('/storage/', '') : null)"
                                            :disabled="!editMode"
                                            class="absolute top-2 right-2 bg-red-600 bg-opacity-50 text-white rounded-full p-2 hover:bg-opacity-75">
                                            <svg class="h-1 w-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <!-- New Image Upload -->
                            <input type="file" name="images[]" multiple accept="image/*"
                                @change="previewImages($event)" :disabled="!editMode"
                                class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                :class="{ 'bg-gray-50': !editMode }">
                            @error('images.*')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-3">
                            <button type="submit" x-show="editMode"
                                class="bg-blue-500 text-white px-6 py-3 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-lg font-medium">
                                Keşif Raporunu Güncelle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
