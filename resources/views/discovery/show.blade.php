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
                allItems: [], // Store all items loaded initially
                selectedItems: existingItems ? existingItems.map(item => ({
                    id: item.id,
                    item: item.item,
                    brand: item.brand,
                    price: item.price,
                    quantity: item.pivot?.quantity || 1,
                    custom_price: item.pivot?.custom_price || item.price,
                    is_existing: true
                })) : [],
                showModal: false,
                modalSelectedItems: [],
                isLoadingItems: false,

                // Pagination properties
                currentPage: 1,
                itemsPerPage: 25,

                // Computed properties
                get displayItems() {
                    // If there's a search query, return filtered results, otherwise return all items
                    return this.searchQuery.length >= 2 ? this.searchResults : this.allItems;
                },

                get totalPages() {
                    return Math.ceil(this.displayItems.length / this.itemsPerPage);
                },

                get paginatedSearchResults() {
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    const end = start + this.itemsPerPage;
                    return this.displayItems.slice(start, end);
                },

                get visiblePages() {
                    const pages = [];
                    const maxVisible = 5;
                    let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
                    let end = Math.min(this.totalPages, start + maxVisible - 1);

                    // Adjust start if we're near the end
                    if (end - start + 1 < maxVisible) {
                        start = Math.max(1, end - maxVisible + 1);
                    }

                    for (let i = start; i <= end; i++) {
                        pages.push(i);
                    }
                    return pages;
                },

                // Modal functions
                async openModal() {
                    this.showModal = true;
                    // Copy current selected items to modal
                    this.modalSelectedItems = [...this.selectedItems];
                    this.searchQuery = '';
                    this.searchResults = [];
                    this.currentPage = 1;

                    // Load all items if not already loaded
                    if (this.allItems.length === 0) {
                        await this.loadAllItems();
                    }
                },

                closeModal() {
                    this.showModal = false;
                    this.searchQuery = '';
                    this.searchResults = [];
                    this.currentPage = 1;
                },

                saveModalItems() {
                    this.selectedItems = [...this.modalSelectedItems];
                    this.closeModal();
                },

                // Load all items for initial display
                async loadAllItems() {
                    this.isLoadingItems = true;
                    try {
                        const response = await fetch('/items/search-for-discovery');
                        const data = await response.json();
                        this.allItems = data.items || [];
                    } catch (error) {
                        console.error('Error loading items:', error);
                        this.allItems = [];
                    } finally {
                        this.isLoadingItems = false;
                    }
                },

                // Pagination functions
                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                    }
                },

                previousPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                    }
                },

                goToPage(page) {
                    if (page >= 1 && page <= this.totalPages) {
                        this.currentPage = page;
                    }
                },

                // Search functionality
                async searchItems() {
                    if (this.searchQuery.length < 2) {
                        this.searchResults = [];
                        this.currentPage = 1;
                        return;
                    }

                    try {
                        const response = await fetch(
                            `/items/search-for-discovery?query=${encodeURIComponent(this.searchQuery)}`);
                        const data = await response.json();
                        this.searchResults = data.items;
                        this.currentPage = 1; // Reset to first page on new search
                    } catch (error) {
                        console.error('Error searching items:', error);
                        this.searchResults = [];
                        this.currentPage = 1;
                    }
                },

                // Modal item management
                addItemToModal(item) {
                    if (!this.modalSelectedItems.find(i => i.id === item.id)) {
                        this.modalSelectedItems.push({
                            ...item,
                            quantity: 1,
                            custom_price: null
                        });
                    }
                    // Don't clear search when adding items, just provide feedback
                },

                removeItemFromModal(index) {
                    this.modalSelectedItems.splice(index, 1);
                },

                // Main list item management
                removeItem(index) {
                    this.selectedItems.splice(index, 1);
                }
            }
        }

        function imageUploader() {
            return {
                previews: [],
                fileInput: null,
                showImageModal: false,
                selectedImage: null,
                existingImages: [], // Track original image paths from database

                init() {
                    // Initialize with existing images if any
                    if (this.$el.dataset.existingImages) {
                        this.existingImages = JSON.parse(this.$el.dataset.existingImages);
                        this.previews = this.existingImages.map(img => `/storage/${img}`);
                    }

                    // Add keyboard event listener for ESC key
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && this.showImageModal) {
                            this.closeImageModal();
                        }
                    });
                },

                viewImage(imageSrc) {
                    this.selectedImage = imageSrc;
                    this.showImageModal = true;
                },

                closeImageModal() {
                    this.showImageModal = false;
                    this.selectedImage = null;
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
                    // Check if this is an existing image (stored in database)
                    const isExistingImage = index < this.existingImages.length;
                    
                    if (isExistingImage) {
                        // Get the original database path for existing images
                        const originalPath = this.existingImages[index];
                        
                        // If it's an existing image, mark it for removal
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'remove_images[]';
                        input.value = originalPath;
                        // Add to form element, not to the imageUploader div
                        this.$el.closest('form').appendChild(input);
                        
                        // Remove from existingImages array too
                        this.existingImages.splice(index, 1);
                    } else {
                        // If it's a new image, remove from file input
                        const newImageIndex = index - this.existingImages.length;
                        
                        if (this.fileInput && this.fileInput.files.length > 0) {
                            const dt = new DataTransfer();
                            const files = Array.from(this.fileInput.files);
                            files.splice(newImageIndex, 1);
                            files.forEach(file => dt.items.add(file));
                            this.fileInput.files = dt.files;
                        }
                    }
                    
                    // Remove from previews array
                    this.previews.splice(index, 1);
                },

                clearAllImages() {
                    this.previews = [];
                    if (this.fileInput) {
                        this.fileInput.value = '';
                    }
                    // Add hidden inputs to remove all existing images
                    this.existingImages.forEach(img => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'remove_images[]';
                        input.value = img;
                        // Add to form element, not to the imageUploader div
                        this.$el.closest('form').appendChild(input);
                    });
                    // Clear the existing images array
                    this.existingImages = [];
                }
            }
        }

        function paymentMethodSelector() {
            return {
                paymentMethods: [],
                selectedPaymentMethodId: '',

                async loadPaymentMethods() {
                    try {
                        const response = await fetch('/api/payment-methods', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            credentials: 'same-origin'
                        });
                        
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        
                        const data = await response.json();
                        this.paymentMethods = data;
                        
                        // Set the selected payment method after loading the options
                        // This ensures the dropdown shows the correct selected value
                        this.$nextTick(() => {
                            const selectElement = this.$el.querySelector('select');
                            if (selectElement && this.selectedPaymentMethodId) {
                                selectElement.value = this.selectedPaymentMethodId;
                            }
                        });
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
                neighborhood: '{{ old('neighborhood', $discovery->neighborhood) }}',
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
                    const parts = [this.city, this.district, this.neighborhood, this.address].filter(part => part && part.trim());
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
                        this.neighborhood = '';
                        this.latitude = savedData.latitude || data.selectedProperty.latitude || '';
                        this.longitude = savedData.longitude || data.selectedProperty.longitude || '';
                    } else if (data.modalAddressType === 'manual') {
                        this.propertyId = '';
                        this.propertyName = '';
                        this.propertyFullAddress = '';
                        this.address = savedData.address || data.addressDetails || '';
                        this.city = savedData.city || data.selectedCity || '';
                        this.district = savedData.district || data.selectedDistrict || '';
                        this.neighborhood = savedData.neighborhood || data.selectedNeighborhood || '';
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
                selectedNeighborhood: @json($discovery->neighborhood ?? ''),
                addressDetails: @json($discovery->address ?? ''),
                districts: [],
                neighborhoods: [],
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
                            this.updateNeighborhoods();
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
                            this.selectedNeighborhood = '';
                            this.neighborhoods = [];
                        }
                    } else {
                        this.selectedNeighborhood = '';
                        this.neighborhoods = [];
                    }
                },

                updateNeighborhoods() {
                    if (this.selectedCity && this.selectedDistrict) {
                        // Fetch combined neighborhoods and sites from web API
                        fetch(`/api/combined-neighborhoods?city=${encodeURIComponent(this.selectedCity)}&district=${encodeURIComponent(this.selectedDistrict)}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        })
                        .then(response => response.json())
                        .then(data => {
                            this.neighborhoods = Array.isArray(data) ? data : [];
                            if (!this.neighborhoods.includes(this.selectedNeighborhood)) {
                                this.selectedNeighborhood = '';
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching neighborhoods:', error);
                            // Fallback to static data
                            const cityNeighborhoods = @json(\App\Data\AddressData::getAllNeighborhoods());
                            this.neighborhoods = cityNeighborhoods[this.selectedCity]?.[this.selectedDistrict] || [];
                            if (!this.neighborhoods.includes(this.selectedNeighborhood)) {
                                this.selectedNeighborhood = '';
                            }
                        });
                    } else {
                        this.neighborhoods = [];
                        this.selectedNeighborhood = '';
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
                            data.neighborhood = this.selectedNeighborhood || '';
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
                                selectedNeighborhood: this.selectedNeighborhood,
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
                                    <input type="hidden" name="neighborhood" x-model="neighborhood">
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
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Bölge</label>
                                                        <select x-model="selectedDistrict" @change="updateNeighborhoods()"
                                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                            <option value="">Bir ilçe seçin</option>
                                                            <template x-for="district in districts" :key="`${selectedCity}-${district}`">
                                                                <option :value="district" 
                                                                        x-text="district"
                                                                        :selected="district === selectedDistrict"></option>
                                                            </template>
                                                        </select>
                                                    </div>

                                                    <!-- Neighborhood Selection -->
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                                            @if (auth()->user()->isSoloHandyman())
                                                                Mahalle
                                                            @else
                                                                Mahalle/Site
                                                            @endif
                                                        </label>
                                                        <select x-model="selectedNeighborhood"
                                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                            <option value="">Önce şehir ve ilçe seçin</option>
                                                            <template x-for="neighborhood in neighborhoods" :key="`${selectedCity}-${selectedDistrict}-${neighborhood}`">
                                                                <option :value="neighborhood" 
                                                                        x-text="neighborhood"
                                                                        :selected="neighborhood === selectedNeighborhood"></option>
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Öncelik</label>
                            
                            <!-- View Mode -->
                            <div x-show="!editMode">
                                @if($discovery->priorityBadge)
                                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-white" 
                                         style="{{ $discovery->priorityBadge->style }}">
                                        {{ $discovery->priorityBadge->name }} 
                                        <span class="ml-1 text-xs opacity-75">(Seviye {{ $discovery->priorityBadge->level }})</span>
                                    </div>
                                    @if($discovery->priorityBadge->description)
                                        <p class="text-sm text-gray-500 mt-1">{{ $discovery->priorityBadge->description }}</p>
                                    @endif
                                @else
                                    <span class="text-gray-500 text-sm">Öncelik atanmamış</span>
                                @endif
                            </div>

                            <!-- Edit Mode -->
                            <select name="priority_id" x-show="editMode"
                                class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                <option value="">Öncelik seçin (isteğe bağlı)</option>
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority->id }}" 
                                        {{ old('priority_id', $discovery->priority_id) == $priority->id ? 'selected' : '' }}
                                        style="color: {{ $priority->color }};">
                                        {{ $priority->name }} (Seviye {{ $priority->level }})
                                    </option>
                                @endforeach
                            </select>
                            
                            @error('priority_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Work Group Display -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">İş Grubu</label>
                            
                            <!-- View Mode -->
                            <div x-show="!editMode" class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 px-4 py-2">
                                @if ($discovery->workGroup)
                                    <div class="flex items-center">
                                        <span class="font-medium text-gray-900">{{ $discovery->workGroup->name }}</span>
                                        @if ($discovery->workGroup->description)
                                            <span class="ml-2 text-sm text-gray-600">- {{ $discovery->workGroup->description }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-500 italic">Atanmış iş grubu yok</span>
                                @endif
                            </div>

                            <!-- Edit Mode -->
                            <select name="work_group_id" x-show="editMode"
                                class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                <option value="">İş grubu seçin (opsiyonel)</option>
                                @foreach($workGroups as $workGroup)
                                    <option value="{{ $workGroup->id }}" 
                                        {{ old('work_group_id', $discovery->work_group_id) == $workGroup->id ? 'selected' : '' }}>
                                        {{ $workGroup->name }}
                                        @if($workGroup->description) - {{ $workGroup->description }}@endif
                                    </option>
                                @endforeach
                            </select>
                            
                            @error('work_group_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Assignee Display (only for company users) -->
                        @if (auth()->user()->isCompanyAdmin() || auth()->user()->isCompanyEmployee())
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Atanan Personel</label>
                            
                            <!-- View Mode -->
                            <div x-show="!editMode" class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 px-4 py-2">
                                @if ($discovery->assignee)
                                    <div class="flex items-center">
                                        <span class="font-medium text-gray-900">{{ $discovery->assignee->name }}</span>
                                        <span class="ml-2 text-sm text-gray-600">({{ $discovery->assignee->email }})</span>
                                        @if($discovery->assignee->workGroups && $discovery->assignee->workGroups->count() > 0)
                                            <span class="ml-2 text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded">
                                                Gruplar: {{ $discovery->assignee->workGroups->pluck('name')->join(', ') }}
                                            </span>
                                        @else
                                            <span class="ml-2 text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                                Grup ataması yok
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-500 italic">Atanmış personel yok</span>
                                @endif
                            </div>

                            <!-- Edit Mode (only for company admins) -->
                            @if (auth()->user()->isCompanyAdmin() && $assignableEmployees && $assignableEmployees->count() > 0)
                            <select name="assignee_id" x-show="editMode"
                                class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                <option value="">Personel ataması yok</option>
                                @foreach($assignableEmployees as $employee)
                                    <option value="{{ $employee->id }}" 
                                        {{ old('assignee_id', $discovery->assignee_id) == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }} ({{ $employee->email }})
                                        @if($employee->workGroups && $employee->workGroups->count() > 0)
                                            - Gruplar: {{ $employee->workGroups->pluck('name')->join(', ') }}
                                        @else
                                            - Grup ataması yok
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @endif
                            
                            @error('assignee_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif

                        <!-- Item Selection -->
                        <div x-data="itemSelector({{ json_encode($discovery->items) }})" class="space-y-4 pt-3">
                            <div class="flex justify-between items-center">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Malzemeler</label>
                                <button type="button" @click="openModal()" x-show="editMode"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md font-medium transition duration-200 flex items-center space-x-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <span>Malzeme Ekle</span>
                                </button>
                            </div>

                            <!-- Selected Items Preview -->
                            <div x-show="selectedItems.length > 0" class="bg-gray-50 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-3">
                                    <h4 class="font-medium text-gray-700">Seçili Malzemeler</h4>
                                    <span class="text-sm text-gray-500"
                                        x-text="selectedItems.length + ' malzeme'"></span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <template x-for="(item, index) in selectedItems" :key="index">
                                        <div class="bg-white rounded-md p-3 border border-gray-200">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    <p class="font-medium text-sm" x-text="item.item"></p>
                                                    <p class="text-xs text-gray-500" x-text="item.brand"></p>
                                                    <p class="text-sm font-medium text-blue-600 mt-1">
                                                        <span x-text="item.quantity"></span> adet
                                                        <span x-text="' • ' + item.price + ' TL'"></span>
                                                        <span x-show="item.custom_price && item.custom_price !== item.price" class="text-green-600"
                                                            x-text="' → ' + item.custom_price + ' TL'"></span>
                                                    </p>
                                                </div>
                                                <button type="button" @click="removeItem(index)" x-show="editMode"
                                                    class="text-gray-600 hover:text-red-600 p-2 transition duration-200 hover:bg-red-50 rounded ml-2 flex-shrink-0">
                                                    <svg class="h-6 w-6" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <!-- Hidden inputs for form submission -->
                                            <input type="hidden" x-bind:name="'items[' + index + '][id]'"
                                                x-bind:value="item.id">
                                            <input type="hidden" x-bind:name="'items[' + index + '][quantity]'"
                                                x-bind:value="item.quantity">
                                            <input type="hidden"
                                                x-bind:name="'items[' + index + '][custom_price]'"
                                                x-bind:value="item.custom_price">
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Empty state -->
                            <div x-show="selectedItems.length === 0"
                                class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">Henüz malzeme seçilmemiş</p>
                                <p class="text-sm text-gray-400" x-show="editMode">Malzeme eklemek için yukarıdaki butonu kullanın</p>
                            </div>

                            <!-- Item Management Modal -->
                            <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
                                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                                <div
                                    class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <!-- Modal backdrop -->
                                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                                        @click="closeModal()"></div>

                                    <!-- Modal panel -->
                                    <div
                                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-4 sm:align-middle sm:max-w-5xl sm:w-full sm:max-h-[90vh]">
                                        <!-- Modal header -->
                                        <div class="bg-white px-6 py-4 border-b border-gray-200">
                                            <div class="flex justify-between items-center">
                                                <h3 class="text-lg font-medium text-gray-900">Malzeme Yönetimi</h3>
                                                <button type="button" @click="closeModal()"
                                                    class="text-gray-400 hover:text-gray-600">
                                                    <svg class="h-6 w-6" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Modal content -->
                                        <div class="bg-white px-6 py-4 max-h-[70vh] overflow-y-auto">
                                            <!-- Selected Items in Modal -->
                                            <div x-show="modalSelectedItems.length > 0" class="mb-6">
                                                <h4 class="text-sm font-medium text-gray-700 mb-3">
                                                    Seçili Malzemeler (<span
                                                        x-text="modalSelectedItems.length"></span>)
                                                </h4>
                                                <div class="space-y-3 max-h-80 overflow-y-auto">
                                                    <template x-for="(item, index) in modalSelectedItems"
                                                        :key="index">
                                                        <div
                                                            class="p-4 bg-white border border-gray-200 rounded-md">
                                                            <div class="grid grid-cols-12 gap-4 items-center">
                                                                <div class="col-span-5">
                                                                    <p class="font-medium" x-text="item.item"></p>
                                                                    <p class="text-sm text-gray-500"
                                                                        x-text="item.brand"></p>
                                                                    <p class="text-sm font-medium text-blue-600"
                                                                        x-text="item.price + ' TL'"></p>
                                                                </div>
                                                                <div class="col-span-2">
                                                                    <label
                                                                        class="block text-xs text-gray-500 mb-1">Miktar</label>
                                                                    <input type="number" x-model="item.quantity"
                                                                        min="1"
                                                                        class="w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-2 py-1">
                                                                </div>
                                                                <div class="col-span-4">
                                                                    <label
                                                                        class="block text-xs text-gray-500 mb-1">Özel
                                                                        Fiyat (opsiyonel)</label>
                                                                    <input type="number"
                                                                        x-model="item.custom_price" step="0.01"
                                                                        placeholder="Farklı fiyat girin"
                                                                        class="w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-2 py-1">
                                                                </div>
                                                                <div class="col-span-1 text-right">
                                                                    <button type="button"
                                                                        @click="removeItemFromModal(index)"
                                                                        class="text-gray-600 hover:text-red-600 p-2 transition duration-200 hover:bg-red-50 rounded">
                                                                        <svg class="h-6 w-6" fill="none"
                                                                            stroke="currentColor"
                                                                            viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                stroke-width="2"
                                                                                d="M6 18L18 6M6 6l12 12" />
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                            <!-- Search section -->
                                            <div class="mb-6">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Malzeme
                                                    Ara</label>
                                                <div class="relative">
                                                    <input type="text" x-model="searchQuery"
                                                        @input.debounce.300ms="searchItems()"
                                                        placeholder="Malzeme adı veya marka..."
                                                        class="w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-3">
                                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                                        <svg class="h-5 w-5 text-gray-400" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Items List (All Items or Search Results) -->
                                            <div x-show="!isLoadingItems && displayItems.length > 0"
                                                class="mb-6">
                                                <div class="flex justify-between items-center mb-3">
                                                    <h4 class="text-sm font-medium text-gray-700"
                                                        x-text="searchQuery.length >= 2 ? 'Arama Sonuçları' : 'Tüm Malzemeler'">
                                                    </h4>
                                                    <span class="text-xs text-gray-500"
                                                        x-text="displayItems.length + ' sonuç bulundu'"></span>
                                                </div>

                                                <!-- Paginated Results -->
                                                <div class="grid grid-cols-1 gap-3 max-h-72 overflow-y-auto">
                                                    <template x-for="item in paginatedSearchResults"
                                                        :key="item.id">
                                                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-md hover:bg-gray-100 cursor-pointer"
                                                            @click="addItemToModal(item)">
                                                            <div class="flex-1">
                                                                <p class="font-medium" x-text="item.item"></p>
                                                                <p class="text-sm text-gray-500"
                                                                    x-text="item.brand"></p>
                                                            </div>
                                                            <div class="text-right">
                                                                <p class="font-medium text-blue-600"
                                                                    x-text="item.price + ' TL'"></p>
                                                                <button type="button"
                                                                    class="mt-1 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                                                    Ekle
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>

                                                <!-- Pagination Controls -->
                                                <div x-show="totalPages > 1"
                                                    class="flex justify-between items-center mt-4 pt-3 border-t border-gray-200">
                                                    <div class="text-xs text-gray-500">
                                                        <span
                                                            x-text="(currentPage - 1) * itemsPerPage + 1"></span>-<span
                                                            x-text="Math.min(currentPage * itemsPerPage, displayItems.length)"></span>
                                                        / <span x-text="displayItems.length"></span>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <button type="button" @click="previousPage()"
                                                            :disabled="currentPage === 1"
                                                            class="px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 disabled:bg-gray-100 disabled:text-gray-400 rounded transition duration-200">
                                                            Önceki
                                                        </button>

                                                        <!-- Page Numbers -->
                                                        <template x-for="page in visiblePages"
                                                            :key="page">
                                                            <button type="button" @click="goToPage(page)"
                                                                :class="page === currentPage ? 'bg-blue-500 text-white' :
                                                                    'bg-gray-200 hover:bg-gray-300'"
                                                                class="px-3 py-1 text-xs rounded transition duration-200"
                                                                x-text="page">
                                                            </button>
                                                        </template>

                                                        <button type="button" @click="nextPage()"
                                                            :disabled="currentPage === totalPages"
                                                            class="px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 disabled:bg-gray-100 disabled:text-gray-400 rounded transition duration-200">
                                                            Sonraki
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Loading State -->
                                            <div x-show="isLoadingItems" class="mb-6">
                                                <div class="flex justify-center items-center py-8">
                                                    <div
                                                        class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500">
                                                    </div>
                                                    <span class="ml-3 text-gray-600">Malzemeler
                                                        yükleniyor...</span>
                                                </div>
                                            </div>

                                            <!-- Empty state in modal -->
                                            <div x-show="!isLoadingItems && modalSelectedItems.length === 0 && displayItems.length === 0"
                                                class="text-center py-12">
                                                <svg class="mx-auto h-16 w-16 text-gray-300" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8v6a2 2 0 01-2 2H9a2 2 0 01-2-2V5a2 2 0 012-2h8a2 2 0 012 2z" />
                                                </svg>
                                                <p class="mt-4 text-gray-500">Hiç malzeme bulunamadı</p>
                                                <p class="text-sm text-gray-400">Farklı bir arama terimi deneyin
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Modal footer -->
                                        <div class="bg-gray-50 px-6 py-4 flex justify-between items-center">
                                            <div class="text-sm text-gray-500">
                                                <span x-text="modalSelectedItems.length"></span> malzeme seçildi
                                            </div>
                                            <div class="flex space-x-3">
                                                <button type="button" @click="closeModal()"
                                                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md font-medium">
                                                    İptal
                                                </button>
                                                <button type="button" @click="saveModalItems()"
                                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md font-medium">
                                                    Kaydet
                                                </button>
                                            </div>
                                        </div>
                                    </div>
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
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hizmet</label>
                                <input type="number" name="service_cost"
                                    value="{{ old('service_cost', (string)$discovery->service_cost) }}"
                                    :disabled="!editMode"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                    :class="{ 'bg-gray-50': !editMode }" min="0" step="0.01">
                            </div>

                            <!-- Add these in the Cost Information section -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ulaşım</label>
                                <input type="number" name="transportation_cost"
                                    value="{{ old('transportation_cost', (string)$discovery->transportation_cost) }}"
                                    :disabled="!editMode"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                    :class="{ 'bg-gray-50': !editMode }" min="0" step="0.01">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">İşçilik</label>
                                <input type="number" name="labor_cost"
                                    value="{{ old('labor_cost', (string)$discovery->labor_cost) }}" :disabled="!editMode"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                    :class="{ 'bg-gray-50': !editMode }" min="0" step="0.01">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Görünmeyen Giderler</label>
                                <input type="number" name="extra_fee"
                                    value="{{ old('extra_fee', (string)$discovery->extra_fee) }}" :disabled="!editMode"
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
                                    %</label>
                                    <input type="number" name="discount_rate"
                                        value="{{ old('discount_rate', (string)$discovery->discount_rate) }}"
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
                                        value="{{ old('discount_amount', (string)$discovery->discount_amount) }}"
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
                                <!-- Non-Labor Costs (Genel Masraflar) -->
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Diğer Masraflar (Servis + Ulaşım + Ekstra):</span>
                                    <span class="font-medium">{{ number_format($discovery->non_labor_costs, 2) }} TL</span>
                                </div>

                                <!-- Items Total (Material Costs) - Now first -->
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Toplam Malzeme:</span>
                                    <span class="font-medium">
                                        @php
                                            $itemsTotal = 0;
                                            foreach ($discovery->items as $item) {
                                                $itemsTotal += ($item->pivot->custom_price ?? $item->price) * $item->pivot->quantity;
                                            }
                                        @endphp
                                        {{ number_format($itemsTotal, 2) }} TL
                                    </span>
                                </div>

                                <!-- Labor Cost (İşçilik) - Now after materials -->
                                <div class="flex justify-between">
                                    <span class="text-gray-600">İşçilik:</span>
                                    <span class="font-medium">{{ number_format((float)$discovery->labor_cost, 2) }} TL</span>
                                </div>

                                <!-- Discount on Labor Cost Only -->
                                @if ($discovery->discount_rate > 0)
                                    <div class="flex justify-between text-red-600">
                                        <span>İşçilik İndirimi ({{ number_format((float)$discovery->discount_rate, 2) }}%):</span>
                                        <span>-{{ number_format($discovery->discount_rate_amount, 2) }} TL</span>
                                    </div>
                                    <div class="flex justify-between text-green-600">
                                        <span>İndirimli İşçilik ({{ number_format((float)$discovery->labor_cost, 2) }} - {{ number_format($discovery->discount_rate_amount, 2) }}):</span>
                                        <span>{{ number_format($discovery->discounted_labor_cost, 2) }} TL</span>
                                    </div>
                                @endif

                                <!-- Fixed Discount Amount -->
                                @if ($discovery->discount_amount > 0)
                                    <div class="flex justify-between text-red-600">
                                        <span>Sabit İndirim:</span>
                                        <span>-{{ number_format((float)$discovery->discount_amount, 2) }} TL</span>
                                    </div>
                                @endif

                                <!-- Final Total -->
                                <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-3 mt-3">
                                    <span>Toplam:</span>
                                    <span>{{ number_format($discovery->total_cost, 2) }} TL</span>
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
                                x-data="paymentMethodSelector()" 
                                x-init="selectedPaymentMethodId = '{{ old('payment_method_id', $discovery->payment_method_id) }}'; loadPaymentMethods();" 
                                x-model="selectedPaymentMethodId"
                                class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                <option value="">Ödeme şekli seçin (opsiyonel)</option>
                                <template x-for="paymentMethod in paymentMethods" :key="paymentMethod.id">
                                    <option :value="paymentMethod.id" x-text="paymentMethod.name" :selected="paymentMethod.id == selectedPaymentMethodId"></option>
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
                                    <div class="relative group">
                                        <!-- Image with click to enlarge -->
                                        <img :src="preview" 
                                             class="h-40 w-full object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                             @click="viewImage(preview)">
                                        
                                        <!-- Enhanced remove button overlay -->
                                        <button type="button"
                                            @click="removeImage(index)"
                                            :disabled="!editMode"
                                            x-show="editMode"
                                            class="absolute -top-2 -right-2 w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center shadow-lg opacity-80 hover:opacity-100 transition-all duration-200 hover:scale-110 disabled:opacity-50 disabled:cursor-not-allowed">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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

                            <!-- Image Preview Modal -->
                            <div x-show="showImageModal" 
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="fixed inset-0 z-50 overflow-y-auto"
                                 style="display: none;"
                                 @click.self="closeImageModal()">
                                
                                <!-- Background overlay -->
                                <div class="fixed inset-0 bg-black bg-opacity-75 transition-opacity"
                                     @click.stop="closeImageModal()"></div>
                                
                                <!-- Modal content -->
                                <div class="flex min-h-full items-center justify-center p-4"
                                     @click.self="closeImageModal()">
                                    <div class="relative max-w-4xl max-h-full">
                                        <!-- Close button -->
                                        <button type="button" 
                                                @click.stop="closeImageModal()"
                                                class="absolute -top-4 -right-4 z-10 w-10 h-10 bg-white hover:bg-gray-100 text-gray-800 rounded-full flex items-center justify-center shadow-lg transition-all duration-200 hover:scale-110">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                        
                                        <!-- Image -->
                                        <img :src="selectedImage" 
                                             alt="Preview" 
                                             class="max-w-full max-h-screen object-contain rounded-lg shadow-2xl"
                                             @click.stop>
                                    </div>
                                </div>
                            </div>
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
