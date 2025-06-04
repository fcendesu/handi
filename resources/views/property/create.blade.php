<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Add New Property - İşler</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <x-navigation />

    <!-- Main Content -->
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center mb-6">
                        <a href="{{ route('properties.index') }}" 
                           class="text-blue-600 hover:text-blue-800 mr-4">
                            ← Back to Properties
                        </a>
                        <h1 class="text-3xl font-bold text-gray-900">Add New Property</h1>
                    </div>

                    <form action="{{ route('properties.store') }}" method="POST" 
                          x-data="propertyForm()" 
                          class="space-y-6">
                        @csrf

                        <!-- Property Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Property Name *
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name') }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>                        <!-- Address Components -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">Address</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="site_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Site Name
                                    </label>
                                    <input type="text" 
                                           name="site_name" 
                                           id="site_name" 
                                           value="{{ old('site_name') }}"
                                           placeholder="e.g., Marina Complex"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('site_name') border-red-500 @enderror">
                                    @error('site_name')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="building_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Building Name
                                    </label>
                                    <input type="text" 
                                           name="building_name" 
                                           id="building_name" 
                                           value="{{ old('building_name') }}"
                                           placeholder="e.g., Building A"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('building_name') border-red-500 @enderror">
                                    @error('building_name')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>                            <div>
                                <label for="street" class="block text-sm font-medium text-gray-700 mb-2">
                                    Street
                                </label>
                                <input type="text" 
                                       name="street" 
                                       id="street" 
                                       value="{{ old('street') }}"
                                       placeholder="e.g., Şehit Salahi Şevket Sokağı"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('street') border-red-500 @enderror">
                                @error('street')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>                            <div>
                                <label for="door_apartment_no" class="block text-sm font-medium text-gray-700 mb-2">
                                    Door/Apartment Number
                                </label>
                                <input type="text" 
                                       name="door_apartment_no" 
                                       id="door_apartment_no" 
                                       value="{{ old('door_apartment_no') }}"
                                       placeholder="e.g., 15A or Apartment 3"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('door_apartment_no') border-red-500 @enderror">
                                @error('door_apartment_no')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>                        <!-- City and District -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                                    City *
                                </label>                                <select name="city" 
                                        id="city" 
                                        x-model="selectedCity"
                                        @change="updateNeighborhoods()"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('city') border-red-500 @enderror">
                                    <option value="">Select a city</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city }}" {{ old('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                                    @endforeach
                                </select>
                                @error('city')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>                            <div>
                                <label for="neighborhood" class="block text-sm font-medium text-gray-700 mb-2">
                                    District *
                                </label>
                                <select name="neighborhood" 
                                        id="neighborhood" 
                                        x-model="selectedNeighborhood"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('neighborhood') border-red-500 @enderror">
                                    <option value="">Select a district</option>
                                    <template x-for="neighborhood in neighborhoods" :key="neighborhood">
                                        <option :value="neighborhood" x-text="neighborhood"></option>
                                    </template>
                                </select>
                                @error('neighborhood')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Geolocation -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">Location Coordinates</h3>
                                <button type="button" 
                                        @click="getCurrentLocation()"
                                        :disabled="loadingLocation"
                                        class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-md text-sm transition duration-200">
                                    <span x-show="!loadingLocation">Get Current Location</span>
                                    <span x-show="loadingLocation">Getting Location...</span>
                                </button>
                            </div>

                            <!-- Hidden coordinate inputs for form submission -->
                            <div class="hidden">
                                <input type="number" 
                                       name="latitude" 
                                       id="latitude" 
                                       step="any"
                                       x-model="latitude"
                                       value="{{ old('latitude') }}">
                                <input type="number" 
                                       name="longitude" 
                                       id="longitude" 
                                       step="any"
                                       x-model="longitude"
                                       value="{{ old('longitude') }}">
                            </div>

                            <!-- Interactive Map -->
                            <div class="mt-4">
                                <p class="text-sm text-gray-600 mb-2">Click on the map to select a location:</p>
                                <div id="map" style="height: 400px; width: 100%;" class="rounded-lg border border-gray-300"></div>
                            </div>

                            <div x-show="locationError" class="text-red-600 text-sm" x-text="locationError"></div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notes
                            </label>
                            <textarea name="notes" 
                                      id="notes" 
                                      rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror"
                                      placeholder="Any additional notes about this property...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4 pt-6">
                            <a href="{{ route('properties.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-200">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                Create Property
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function propertyForm() {
            return {
                selectedCity: '{{ old('city') }}',
                selectedNeighborhood: '{{ old('neighborhood') }}',
                neighborhoods: [],
                latitude: {{ old('latitude') ?? 'null' }},
                longitude: {{ old('longitude') ?? 'null' }},
                loadingLocation: false,
                locationError: '',
                map: null,
                marker: null,
                cityNeighborhoods: @json($districts),

                init() {
                    this.updateNeighborhoods();
                    this.initMap();
                    
                    // Watch for manual changes to coordinates
                    this.$watch('latitude', () => this.updateMapLocation());
                    this.$watch('longitude', () => this.updateMapLocation());
                },

                updateNeighborhoods() {
                    this.neighborhoods = this.cityNeighborhoods[this.selectedCity] || [];
                    if (!this.neighborhoods.includes(this.selectedNeighborhood)) {
                        this.selectedNeighborhood = '';
                    }
                },

                getCurrentLocation() {
                    if (!navigator.geolocation) {
                        this.locationError = 'Geolocation is not supported by this browser.';
                        return;
                    }

                    this.loadingLocation = true;
                    this.locationError = '';

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            this.latitude = position.coords.latitude;
                            this.longitude = position.coords.longitude;
                            this.updateMapLocation();
                            this.loadingLocation = false;
                        },
                        (error) => {
                            this.loadingLocation = false;
                            switch(error.code) {
                                case error.PERMISSION_DENIED:
                                    this.locationError = 'Location access denied by user.';
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    this.locationError = 'Location information is unavailable.';
                                    break;
                                case error.TIMEOUT:
                                    this.locationError = 'Location request timed out.';
                                    break;
                                default:
                                    this.locationError = 'An unknown error occurred.';
                                    break;
                            }
                        }
                    );
                },

                initMap() {
                    // Default center: Northern Cyprus (approximately Lefkoşa)
                    const defaultLat = this.latitude || 35.1856;
                    const defaultLng = this.longitude || 33.3823;
                    
                    // Initialize map
                    this.map = L.map('map').setView([defaultLat, defaultLng], 10);
                    
                    // Add OpenStreetMap tiles
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(this.map);
                    
                    // Add marker if coordinates exist
                    if (this.latitude && this.longitude) {
                        this.marker = L.marker([this.latitude, this.longitude]).addTo(this.map);
                    }
                    
                    // Handle map clicks
                    this.map.on('click', (e) => {
                        this.latitude = e.latlng.lat;
                        this.longitude = e.latlng.lng;
                        this.updateMapLocation();
                    });
                },

                updateMapLocation() {
                    if (!this.map || !this.latitude || !this.longitude) return;
                    
                    // Remove existing marker
                    if (this.marker) {
                        this.map.removeLayer(this.marker);
                    }
                    
                    // Add new marker
                    this.marker = L.marker([this.latitude, this.longitude]).addTo(this.map);
                    
                    // Center map on new location
                    this.map.setView([this.latitude, this.longitude], this.map.getZoom());
                }
            }
        }
    </script>
</body>
</html>