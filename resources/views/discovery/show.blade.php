<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Discovery Details - {{ config('app.name', 'Handi') }}</title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
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

        function addressDisplay() {
            return {
                showAddressModal: false,
                address: '{{ old('address', $discovery->address) }}',
                city: '{{ old('city', $discovery->city) }}',
                district: '{{ old('district', $discovery->district) }}',
                propertyId: '{{ old('property_id', $discovery->property_id) }}',
                latitude: '{{ old('latitude', $discovery->latitude) }}',
                longitude: '{{ old('longitude', $discovery->longitude) }}',
                
                // Property data for display
                propertyName: @json($discovery->property->name ?? ''),
                propertyFullAddress: @json($discovery->property->full_address ?? ''),
                
                init() {
                    // Component initialization if needed
                },
                
                getPropertyDisplayName() {
                    return this.propertyName || 'Kayıtlı Mülk';
                },
                
                getPropertyDisplayAddress() {
                    return this.propertyFullAddress || 'Adres bilgisi yok';
                },
                
                getManualAddressDisplay() {
                    const parts = [this.city, this.district, this.address].filter(part => part && part.trim());
                    return parts.length > 0 ? parts.join(', ') : 'Adres belirtilmemiş';
                },
                
                handleAddressSaved(data) {
                    // Use saved data from server if available
                    const savedData = data.savedData || {};
                    
                    if (data.modalAddressType === 'property' && data.selectedProperty) {
                        this.propertyId = savedData.property_id || data.selectedProperty.id;
                        this.propertyName = savedData.property?.name || data.selectedProperty.name;
                        this.propertyFullAddress = savedData.property?.full_address || data.selectedProperty.full_address;
                        this.address = '';
                        this.city = '';
                        this.district = '';
                        this.latitude = savedData.latitude || data.selectedProperty.latitude || '';
                        this.longitude = savedData.longitude || data.selectedProperty.longitude || '';
                    } else if (data.modalAddressType === 'manual') {
                        this.propertyId = '';
                        this.propertyName = '';
                        this.propertyFullAddress = '';
                        this.address = savedData.address || data.addressDetails || '';
                        this.city = savedData.city || data.selectedCity || '';
                        this.district = savedData.district || data.selectedDistrict || '';
                        this.latitude = savedData.latitude || data.latitude || '';
                        this.longitude = savedData.longitude || data.longitude || '';
                    }
                    
                    // Close the modal
                    this.showAddressModal = false;
                }
            }
        }

        function addressModalData() {
            return {
                modalAddressType: '{{ $discovery->property_id ? 'property' : 'manual' }}',
                selectedPropertyId: '{{ $discovery->property_id }}',
                selectedProperty: null,
                properties: [],
                
                // Manual address fields - use separate city/district fields
                selectedCity: @json($discovery->city ?? ''),
                selectedDistrict: @json($discovery->district ?? ''),
                addressDetails: @json($discovery->address ?? ''),
                districts: [],
                latitude: @json($discovery->latitude ?? ''),
                longitude: @json($discovery->longitude ?? ''),
                loadingLocation: false,
                locationError: '',
                map: null,
                marker: null,

                async init() {
                    await this.loadProperties();
                    if (this.selectedPropertyId) {
                        this.selectedProperty = this.properties.find(p => p.id == this.selectedPropertyId);
                    }
                    
                    // Initialize districts for the selected city if available
                    if (this.selectedCity) {
                        setTimeout(() => {
                            this.updateDistricts();
                        }, 100);
                    }
                    
                    // Check if Leaflet is available
                    if (typeof L === 'undefined') {
                        this.locationError = 'Harita kütüphanesi yüklenemedi';
                        return;
                    }
                    
                    // Initialize map after a delay to ensure DOM is ready
                    setTimeout(() => {
                        if (document.getElementById('addressModalMap')) {
                            this.initMap();
                        }
                    }, 200);
                },

                updateDistricts() {
                    const cityDistrictMap = @json(\App\Data\AddressData::getAllDistricts());
                    
                    // Store current district before update
                    const currentDistrict = this.selectedDistrict;
                    
                    this.districts = cityDistrictMap[this.selectedCity] || [];
                    
                    // Check if current district is valid for this city
                    if (currentDistrict && this.districts.length > 0) {
                        const isDistrictValid = this.districts.includes(currentDistrict);
                        
                        if (isDistrictValid) {
                            // Force re-assignment to trigger reactivity
                            this.selectedDistrict = '';
                            this.$nextTick(() => {
                                this.selectedDistrict = currentDistrict;
                            });
                        } else {
                            this.selectedDistrict = '';
                        }
                    }
                },

                async getCurrentLocation() {
                    this.loadingLocation = true;
                    this.locationError = '';

                    if (!navigator.geolocation) {
                        this.locationError = 'Konum servisi bu tarayıcıda desteklenmemektedir.';
                        this.loadingLocation = false;
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            this.latitude = position.coords.latitude.toFixed(6);
                            this.longitude = position.coords.longitude.toFixed(6);
                            this.updateMapLocation();
                            this.loadingLocation = false;
                        },
                        (error) => {
                            this.locationError = 'Konum alınamadı: ' + error.message;
                            this.loadingLocation = false;
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 300000
                        }
                    );
                },

                initMap() {
                    try {
                        // Check if map container exists
                        const mapContainer = document.getElementById('addressModalMap');
                        if (!mapContainer) {
                            setTimeout(() => this.initMap(), 500);
                            return;
                        }

                        // Check if container is visible (important for timing)
                        const containerRect = mapContainer.getBoundingClientRect();
                        if (containerRect.width === 0 || containerRect.height === 0) {
                            setTimeout(() => this.initMap(), 300);
                            return;
                        }

                        if (this.map) {
                            this.map.remove(); // Clean up existing map
                        }

                        // Default location (Northern Cyprus - Lefkoşa)
                        let lat = 35.1856;
                        let lng = 33.3823;

                        // Use existing coordinates if available
                        if (this.latitude && this.longitude) {
                            lat = parseFloat(this.latitude);
                            lng = parseFloat(this.longitude);
                        }

                        this.map = L.map('addressModalMap').setView([lat, lng], 13);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(this.map);

                        // Add marker if coordinates exist
                        if (this.latitude && this.longitude) {
                            this.marker = L.marker([lat, lng]).addTo(this.map);
                        }

                        // Click handler for map
                        this.map.on('click', (e) => {
                            this.latitude = e.latlng.lat.toFixed(6);
                            this.longitude = e.latlng.lng.toFixed(6);
                            this.updateMapLocation();
                        });

                        // Force map to resize after initialization
                        setTimeout(() => {
                            if (this.map) {
                                this.map.invalidateSize();
                            }
                        }, 100);

                    } catch (error) {
                        this.locationError = 'Harita yüklenemedi.';
                    }
                },

                updateMapLocation() {
                    if (!this.map || !this.latitude || !this.longitude) {
                        return;
                    }

                    const lat = parseFloat(this.latitude);
                    const lng = parseFloat(this.longitude);

                    // Remove existing marker
                    if (this.marker) {
                        this.map.removeLayer(this.marker);
                    }

                    // Add new marker
                    this.marker = L.marker([lat, lng]).addTo(this.map);
                    
                    // Center map on new location
                    this.map.setView([lat, lng], 15);
                },

                async loadProperties() {
                    try {
                        const response = await fetch('/api/company-properties');
                        const data = await response.json();
                        this.properties = data;
                    } catch (error) {
                        console.error('Error loading properties:', error);
                        this.properties = [];
                    }
                },

                onPropertyChange() {
                    this.selectedProperty = this.properties.find(p => p.id == this.selectedPropertyId) || null;
                },

                async saveAddress() {
                    try {
                        // Prepare the data to send
                        const data = {
                            address_type: this.modalAddressType,
                            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        };

                        if (this.modalAddressType === 'property' && this.selectedProperty) {
                            data.property_id = this.selectedProperty.id;
                        } else if (this.modalAddressType === 'manual') {
                            data.address = this.addressDetails || '';
                            data.city = this.selectedCity || '';
                            data.district = this.selectedDistrict || '';
                            data.latitude = this.latitude || '';
                            data.longitude = this.longitude || '';
                        }

                        // Show loading state
                        const saveButton = document.querySelector('[x-data*="addressModalData"] button[type="button"]:last-child');
                        const originalText = saveButton.textContent;
                        saveButton.textContent = 'Kaydediliyor...';
                        saveButton.disabled = true;

                        // Make AJAX request
                        const response = await fetch(`/discovery/{{ $discovery->id }}/address`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': data._token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(data)
                        });

                        const result = await response.json();

                        if (result.success) {
                            // Dispatch event to update the display
                            this.$dispatch('address-saved', {
                                modalAddressType: this.modalAddressType,
                                selectedProperty: this.selectedProperty,
                                selectedCity: this.selectedCity,
                                selectedDistrict: this.selectedDistrict,
                                addressDetails: this.addressDetails,
                                latitude: this.latitude,
                                longitude: this.longitude,
                                savedData: result.data
                            });

                            // Show success message
                            this.showNotification('success', result.message || 'Adres başarıyla güncellendi.');
                        } else {
                            // Show error message
                            this.showNotification('error', result.message || 'Adres güncellenirken bir hata oluştu.');
                        }

                        // Restore button state
                        saveButton.textContent = originalText;
                        saveButton.disabled = false;

                    } catch (error) {
                        console.error('Error saving address:', error);
                        this.showNotification('error', 'Adres güncellenirken bir hata oluştu.');
                        
                        // Restore button state
                        const saveButton = document.querySelector('[x-data*="addressModalData"] button[type="button"]:last-child');
                        saveButton.textContent = 'Kaydet';
                        saveButton.disabled = false;
                    }
                },

                showNotification(type, message) {
                    // Create and show a notification
                    const notification = document.createElement('div');
                    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-md ${type === 'success' ? 'bg-green-100 border-l-4 border-green-500 text-green-700' : 'bg-red-100 border-l-4 border-red-500 text-red-700'}`;
                    notification.textContent = message;
                    
                    document.body.appendChild(notification);
                    
                    // Remove after 5 seconds
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 5000);
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

                            <!-- Address Display/Edit Section -->
                            <div class="col-span-full" x-data="addressDisplay()" @address-saved="handleAddressSaved($event.detail)">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Adres</label>
                                
                                <!-- View Mode -->
                                <div x-show="!editMode" class="space-y-3">
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                        <!-- Property Address Display -->
                                        <div x-show="propertyId" class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="font-medium text-gray-900" x-text="getPropertyDisplayName()"></div>
                                                    <div class="text-gray-700 mt-1" x-text="getPropertyDisplayAddress()"></div>
                                                    <div class="text-sm text-gray-500 mt-1">Kayıtlı Mülk</div>
                                                </div>
                                                @if($discovery->property && $discovery->property->latitude && $discovery->property->longitude)
                                                    <a href="https://www.google.com/maps?q={{ $discovery->property->latitude }},{{ $discovery->property->longitude }}" 
                                                       target="_blank"
                                                       class="ml-4 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        </svg>
                                                        Google Maps'te Görüntüle
                                                    </a>
                                                @endif
                                            </div>
                                        <!-- Manual Address Display -->
                                        <div x-show="!propertyId" class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="text-gray-900" x-text="getManualAddressDisplay()"></div>
                                                    <template x-if="city || district">
                                                        <div class="text-sm text-gray-600 mt-1">
                                                            <template x-if="city">
                                                                <span class="font-medium" x-text="city"></span>
                                                            </template>
                                                            <template x-if="district">
                                                                <span>
                                                                    <template x-if="city">, </template>
                                                                    <span x-text="district"></span>
                                                                </span>
                                                            </template>
                                                        </div>
                                                    </template>
                                                    <div class="text-sm text-gray-500 mt-1">Manuel Adres</div>
                                                </div>
                                                @if($discovery->latitude && $discovery->longitude)
                                                    <a href="https://www.google.com/maps?q={{ $discovery->latitude }},{{ $discovery->longitude }}" 
                                                       target="_blank"
                                                       class="ml-4 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        </svg>
                                                        Google Maps'te Görüntüle
                                                    </a>
                                                @elseif($discovery->address)
                                                    <a href="https://www.google.com/maps/search/{{ urlencode($discovery->address) }}" 
                                                       target="_blank"
                                                       class="ml-4 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        </svg>
                                                        Google Maps'te Ara
                                                    </a>
                                                @endif
                                            </div>
                                    </div>
                                </div>

                                <!-- Edit Mode -->
                                <div x-show="editMode" class="space-y-3">
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="font-medium text-yellow-800">Mevcut Adres</div>
                                                <div class="text-yellow-700 text-sm mt-1">
                                                    <!-- Property Address Display (Reactive) -->
                                                    <div x-show="propertyId">
                                                        <span x-text="getPropertyDisplayName()"></span> - <span x-text="getPropertyDisplayAddress()"></span>
                                                    </div>
                                                    <!-- Manual Address Display (Reactive) -->
                                                    <div x-show="!propertyId">
                                                        <span x-text="getManualAddressDisplay()"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" @click="showAddressModal = true"
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200">
                                                Adresi Değiştir
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Hidden inputs for form submission -->
                                    <input type="hidden" name="address" x-model="address">
                                    <input type="hidden" name="city" x-model="city">
                                    <input type="hidden" name="district" x-model="district">
                                    <input type="hidden" name="property_id" x-model="propertyId">
                                    <input type="hidden" name="latitude" x-model="latitude">
                                    <input type="hidden" name="longitude" x-model="longitude">
                                </div>

                                <!-- Address Change Modal -->
                                <div x-show="showAddressModal" x-cloak
                                     class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
                                     @click.self="showAddressModal = false">
                                    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                                        <div class="mt-3">
                                            <h3 class="text-lg font-medium text-gray-900 mb-4">Adres Değiştir</h3>
                                            
                                            <div x-data="addressModalData()" 
                                                 x-init="$watch('$parent.showAddressModal', value => { 
                                                     if (value) { 
                                                         updateDistricts();
                                                         setTimeout(() => updateDistricts(), 50); 
                                                         // Initialize map when modal opens and we're in manual mode
                                                         setTimeout(() => {
                                                             if (modalAddressType === 'manual') {
                                                                 initMap();
                                                             }
                                                         }, 500);
                                                     } 
                                                 });
                                                 $watch('modalAddressType', value => {
                                                     if (value === 'manual') {
                                                         // Wait for the DOM to update and become visible
                                                         setTimeout(() => {
                                                             initMap();
                                                         }, 200);
                                                     }
                                                 })" 
                                                 class="space-y-4">
                                                <!-- Address Type Selection -->
                                                <div class="flex space-x-4">
                                                    <label class="flex items-center">
                                                        <input type="radio" value="property" x-model="modalAddressType" class="mr-2">
                                                        <span class="text-sm text-gray-700">Kayıtlı Mülk Seç</span>
                                                    </label>
                                                    <label class="flex items-center">
                                                        <input type="radio" value="manual" x-model="modalAddressType" class="mr-2">
                                                        <span class="text-sm text-gray-700">Manuel Adres Gir</span>
                                                    </label>
                                                </div>

                                                <!-- Property Selection -->
                                                <div x-show="modalAddressType === 'property'" class="space-y-3">
                                                    <select x-model="selectedPropertyId" @change="onPropertyChange()"
                                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                        <option value="">Bir mülk seçin</option>
                                                        <template x-for="property in properties" :key="property.id">
                                                            <option :value="property.id" 
                                                                x-text="property.name + ' - ' + property.full_address"></option>
                                                        </template>
                                                    </select>

                                                    <div x-show="selectedProperty" class="p-3 bg-blue-50 rounded-md border border-blue-200">
                                                        <div class="text-sm font-medium text-blue-900" x-text="selectedProperty ? selectedProperty.name : ''"></div>
                                                        <div class="text-sm text-blue-700" x-text="selectedProperty ? selectedProperty.full_address : ''"></div>
                                                    </div>
                                                </div>

                                                <!-- Manual Address Input -->
                                                <div x-show="modalAddressType === 'manual'" class="space-y-4">
                                                    <!-- City Selection -->
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Şehir</label>
                                                        <select x-model="selectedCity" @change="updateDistricts()"
                                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                            <option value="">Bir şehir seçin</option>
                                                            @foreach (\App\Data\AddressData::getCities() as $city)
                                                                <option value="{{ $city }}">{{ $city }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <!-- District Selection -->
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">İlçe</label>
                                                        <select x-model="selectedDistrict"
                                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                            <option value="">Bir ilçe seçin</option>
                                                            <template x-for="district in districts" :key="`${selectedCity}-${district}`">
                                                                <option :value="district" 
                                                                        x-text="district"
                                                                        :selected="district === selectedDistrict"></option>
                                                            </template>
                                                        </select>
                                                    </div>

                                                    <!-- Address Details -->
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Adres Detayları</label>
                                                        <textarea x-model="addressDetails" rows="3" 
                                                            placeholder="Site adı, sokak, kapı numarası vb. detayları girin..."
                                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                                    </div>

                                                    <!-- Map Location Picker -->
                                                    <div class="space-y-3">
                                                        <div class="flex items-center justify-between">
                                                            <h4 class="text-sm font-medium text-gray-700">Konum Seçici (İsteğe Bağlı)</h4>
                                                            <button type="button" @click="getCurrentLocation()" :disabled="loadingLocation"
                                                                class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-3 py-1 rounded text-sm transition duration-200">
                                                                <span x-show="!loadingLocation">Mevcut Konumu Al</span>
                                                                <span x-show="loadingLocation">Konum Alınıyor...</span>
                                                            </button>
                                                        </div>

                                                        <!-- Error Display -->
                                                        <div x-show="locationError" class="text-sm text-red-600" x-text="locationError"></div>

                                                        <!-- Interactive Map -->
                                                        <div class="border border-gray-300 rounded-lg overflow-hidden">
                                                            <div id="addressModalMap" style="height: 300px; width: 100%;"></div>
                                                        </div>

                                                        <p class="text-xs text-gray-500 text-center">
                                                            Harita üzerine tıklayarak konum seçebilirsiniz
                                                        </p>
                                                    </div>
                                                </div>

                                                <!-- Modal Actions -->
                                                <div class="flex justify-end space-x-3 pt-4">
                                                    <button type="button" @click="showAddressModal = false"
                                                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md">
                                                        İptal
                                                    </button>
                                                    <button type="button" @click="saveAddress()"
                                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                                                        Kaydet
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                    Tarihi *</label>
                                <input type="date" name="offer_valid_until" required
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
