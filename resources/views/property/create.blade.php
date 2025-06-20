<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Yeni Mülk Ekle - İşler</title>
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
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center mb-6">
                        <a href="{{ route('properties.index') }}" 
                           class="text-blue-600 hover:text-blue-800 mr-4">
                            ← Mülklere Geri Dön
                        </a>
                        <h1 class="text-3xl font-bold text-gray-900">Yeni Mülk Ekle</h1>
                    </div>

                    <form action="{{ route('properties.store') }}" method="POST" 
                          x-data="propertyForm()" 
                          class="space-y-6">
                        @csrf

                        <!-- Property Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Mülk Adı *
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
                        </div>

                        <!-- Owner Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">Mülk Sahibi Bilgileri</h3>
                            
                            <div>
                                <label for="owner_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Sahip Adı
                                </label>
                                <input type="text" 
                                       name="owner_name" 
                                       id="owner_name" 
                                       value="{{ old('owner_name') }}"
                                       placeholder="örn., Ahmet Yılmaz"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('owner_name') border-red-500 @enderror">
                                @error('owner_name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="owner_email" class="block text-sm font-medium text-gray-700 mb-2">
                                        E-posta
                                    </label>
                                    <input type="email" 
                                           name="owner_email" 
                                           id="owner_email" 
                                           value="{{ old('owner_email') }}"
                                           placeholder="örn., ahmet@example.com"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('owner_email') border-red-500 @enderror">
                                    @error('owner_email')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="owner_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                        Telefon
                                    </label>
                                    <input type="text" 
                                           name="owner_phone" 
                                           id="owner_phone" 
                                           value="{{ old('owner_phone') }}"
                                           placeholder="örn., +90 533 123 45 67"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('owner_phone') border-red-500 @enderror">
                                    @error('owner_phone')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>                        <!-- Address Components -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">Adres</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="site_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Site Adı
                                    </label>
                                    <input type="text" 
                                           name="site_name" 
                                           id="site_name" 
                                           value="{{ old('site_name') }}"
                                           placeholder="örn., Marina Kompleksi"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('site_name') border-red-500 @enderror">
                                    @error('site_name')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="building_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Bina Adı
                                    </label>
                                    <input type="text" 
                                           name="building_name" 
                                           id="building_name" 
                                           value="{{ old('building_name') }}"
                                           placeholder="örn., A Blok"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('building_name') border-red-500 @enderror">
                                    @error('building_name')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>                            <div>
                                <label for="street" class="block text-sm font-medium text-gray-700 mb-2">
                                    Sokak
                                </label>
                                <input type="text" 
                                       name="street" 
                                       id="street" 
                                       value="{{ old('street') }}"
                                       placeholder="örn., Şehit Salahi Şevket Sokağı"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('street') border-red-500 @enderror">
                                @error('street')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>                            <div>
                                <label for="door_apartment_no" class="block text-sm font-medium text-gray-700 mb-2">
                                    Kapı/Daire Numarası
                                </label>
                                <input type="text" 
                                       name="door_apartment_no" 
                                       id="door_apartment_no" 
                                       value="{{ old('door_apartment_no') }}"
                                       placeholder="örn., 15A veya Daire 3"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('door_apartment_no') border-red-500 @enderror">
                                @error('door_apartment_no')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>                        <!-- City, District, and Neighborhood -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                                    Şehir *
                                </label>                                <select name="city" 
                                        id="city" 
                                        x-model="selectedCity"
                                        @change="updateDistricts()"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('city') border-red-500 @enderror">
                                    <option value="">Bir şehir seçin</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city }}" {{ old('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                                    @endforeach
                                </select>
                                @error('city')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>                            <div>
                                <label for="district" class="block text-sm font-medium text-gray-700 mb-2">
                                    İlçe *
                                </label>
                                <select name="district" 
                                        id="district" 
                                        x-model="selectedDistrict"
                                        @change="updateNeighborhoods()"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('district') border-red-500 @enderror">
                                    <option value="">Bir ilçe seçin</option>
                                    <template x-for="district in districts" :key="district">
                                        <option :value="district" x-text="district"></option>
                                    </template>
                                </select>
                                @error('district')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="neighborhood" class="block text-sm font-medium text-gray-700 mb-2">
                                    @if(auth()->user() && auth()->user()->company_id)
                                        Mahalle/Site
                                    @else
                                        Mahalle
                                    @endif
                                </label>
                                <select name="neighborhood" 
                                        id="neighborhood" 
                                        x-model="selectedNeighborhood"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('neighborhood') border-red-500 @enderror">
                                    <option value="">
                                        @if(auth()->user() && auth()->user()->company_id)
                                            Bir mahalle/site seçin
                                        @else
                                            Bir mahalle seçin
                                        @endif
                                    </option>
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
                                <h3 class="text-lg font-medium text-gray-900">Konum Koordinatları</h3>
                                <button type="button" 
                                        @click="getCurrentLocation()"
                                        :disabled="loadingLocation"
                                        class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-md text-sm transition duration-200">
                                    <span x-show="!loadingLocation">Mevcut Konumu Al</span>
                                    <span x-show="loadingLocation">Konum Alınıyor...</span>
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
                                <p class="text-sm text-gray-600 mb-2">Bir konum seçmek için harita üzerine tıklayın:</p>
                                <div id="map" style="height: 400px; width: 100%;" class="rounded-lg border border-gray-300"></div>
                            </div>

                            <div x-show="locationError" class="text-red-600 text-sm" x-text="locationError"></div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notlar
                            </label>
                            <textarea name="notes" 
                                      id="notes" 
                                      rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror"
                                      placeholder="Bu mülk hakkında ek notlar...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4 pt-6">
                            <a href="{{ route('properties.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-200">
                                İptal
                            </a>
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                Mülk Oluştur
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
                selectedDistrict: '{{ old('district') }}',
                selectedNeighborhood: '{{ old('neighborhood') }}',
                districts: [],
                neighborhoods: [],
                latitude: {{ old('latitude') ?? 'null' }},
                longitude: {{ old('longitude') ?? 'null' }},
                loadingLocation: false,
                locationError: '',
                map: null,
                marker: null,
                cityDistricts: @json($districts),
                cityNeighborhoods: @json($neighborhoods),

                init() {
                    this.updateDistricts();
                    this.updateNeighborhoods();
                    this.initMap();
                    
                    // Watch for manual changes to coordinates
                    this.$watch('latitude', () => this.updateMapLocation());
                    this.$watch('longitude', () => this.updateMapLocation());
                },

                updateDistricts() {
                    this.districts = this.cityDistricts[this.selectedCity] || [];
                    if (!this.districts.includes(this.selectedDistrict)) {
                        this.selectedDistrict = '';
                        this.selectedNeighborhood = '';
                        this.neighborhoods = [];
                    } else {
                        this.updateNeighborhoods();
                    }
                },

                updateNeighborhoods() {
                    if (this.selectedCity && this.selectedDistrict) {
                        @if(auth()->user() && auth()->user()->company_id)
                            // For company users, fetch combined neighborhoods and company sites
                            fetch(`/api/combined-neighborhoods?city=${encodeURIComponent(this.selectedCity)}&district=${encodeURIComponent(this.selectedDistrict)}`)
                                .then(response => response.json())
                                .then(data => {
                                    this.neighborhoods = data;
                                    if (!this.neighborhoods.includes(this.selectedNeighborhood)) {
                                        this.selectedNeighborhood = '';
                                    }
                                })
                                .catch(error => {
                                    console.error('Error fetching neighborhoods:', error);
                                    this.neighborhoods = [];
                                    this.selectedNeighborhood = '';
                                });
                        @else
                            // For solo handymen, use static data
                            this.neighborhoods = (this.cityNeighborhoods[this.selectedCity] && 
                                                this.cityNeighborhoods[this.selectedCity][this.selectedDistrict]) || [];
                            if (!this.neighborhoods.includes(this.selectedNeighborhood)) {
                                this.selectedNeighborhood = '';
                            }
                        @endif
                    } else {
                        this.neighborhoods = [];
                        this.selectedNeighborhood = '';
                    }
                },

                getCurrentLocation() {
                    if (!navigator.geolocation) {
                        this.locationError = 'Tarayıcınız coğrafi konum belirlemeyi desteklemiyor.';
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
                                    this.locationError = 'Konum erişimi kullanıcı tarafından reddedildi.';
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    this.locationError = 'Konum bilgisi mevcut değil.';
                                    break;
                                case error.TIMEOUT:
                                    this.locationError = 'Konum isteği zaman aşımına uğradı.';
                                    break;
                                default:
                                    this.locationError = 'Bilinmeyen bir hata oluştu.';
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