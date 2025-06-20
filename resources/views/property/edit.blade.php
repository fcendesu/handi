<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mülk Düzenle - İşler</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .map-controls {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .coordinate-display {
            font-family: 'Courier New', monospace;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 4px;
            padding: 4px 8px;
        }
        
        #location-map {
            transition: opacity 0.3s ease;
        }
        
        .map-help-text {
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            color: white;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 12px;
            text-align: center;
        }
    </style>
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
                            ← Mülklere Dön
                        </a>
                        <h1 class="text-3xl font-bold text-gray-900">Mülk Düzenle: {{ $property->name }}</h1>
                    </div>

                    <form action="{{ route('properties.update', $property) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Property Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Mülk Adı *
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name', $property->name) }}"
                                   required
                                   placeholder="ör. Ana Ofis, Müşteri Sahası A"
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
                                       value="{{ old('owner_name', $property->owner_name) }}"
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
                                           value="{{ old('owner_email', $property->owner_email) }}"
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
                                           value="{{ old('owner_phone', $property->owner_phone) }}"
                                           placeholder="örn., +90 533 123 45 67"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('owner_phone') border-red-500 @enderror">
                                    @error('owner_phone')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- City, District, and Neighborhood -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                                    Şehir *
                                </label>
                                <select name="city" 
                                        id="city" 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('city') border-red-500 @enderror">
                                    <option value="">Bir şehir seçin</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city }}" {{ old('city', $property->city) == $city ? 'selected' : '' }}>{{ $city }}</option>
                                    @endforeach
                                </select>
                                @error('city')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="district" class="block text-sm font-medium text-gray-700 mb-2">
                                    İlçe *
                                </label>
                                <select name="district" 
                                        id="district" 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('district') border-red-500 @enderror">
                                    <option value="">Bir ilçe seçin</option>
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
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('neighborhood') border-red-500 @enderror">
                                    <option value="">
                                        @if(auth()->user() && auth()->user()->company_id)
                                            Bir mahalle/site seçin
                                        @else
                                            Bir mahalle seçin
                                        @endif
                                    </option>
                                </select>
                                @error('neighborhood')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Address Components -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">Adres Detayları (İsteğe Bağlı)</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="site_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Site Adı
                                    </label>
                                    <input type="text" 
                                           name="site_name" 
                                           id="site_name" 
                                           value="{{ old('site_name', $property->site_name) }}"
                                           placeholder="ör. Marina Kompleksi"
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
                                           value="{{ old('building_name', $property->building_name) }}"
                                           placeholder="ör. A Binası"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('building_name') border-red-500 @enderror">
                                    @error('building_name')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="street" class="block text-sm font-medium text-gray-700 mb-2">
                                    Sokak
                                </label>
                                <input type="text" 
                                       name="street" 
                                       id="street" 
                                       value="{{ old('street', $property->street) }}"
                                       placeholder="ör. Şehit Salahi Şevket Sokağı"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('street') border-red-500 @enderror">
                                @error('street')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="door_apartment_no" class="block text-sm font-medium text-gray-700 mb-2">
                                    Kapı/Daire Numarası
                                </label>
                                <input type="text" 
                                       name="door_apartment_no" 
                                       id="door_apartment_no" 
                                       value="{{ old('door_apartment_no', $property->door_apartment_no) }}"
                                       placeholder="ör. 15A veya Daire 3"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('door_apartment_no') border-red-500 @enderror">
                                @error('door_apartment_no')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Geolocation -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">Konum Koordinatları (İsteğe Bağlı)</h3>
                                <button type="button" 
                                        id="get-current-location"
                                        class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-md text-sm transition duration-200">
                                    Mevcut Konumu Al
                                </button>
                            </div>

                            <!-- Hidden coordinate inputs for form submission -->
                            <div class="hidden">
                                <input type="number" 
                                       name="latitude" 
                                       id="latitude" 
                                       step="0.00000001"
                                       value="{{ old('latitude', $property->latitude) }}">
                                <input type="number" 
                                       name="longitude" 
                                       id="longitude" 
                                       step="0.00000001"
                                       value="{{ old('longitude', $property->longitude) }}">
                            </div>

                            <!-- Interactive Map for Location Selection -->
                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">İnteraktif Harita Konum Seçici:</h4>

                                
                                <!-- Leaflet Map Container -->
                                <div class="border border-gray-300 rounded-lg overflow-hidden bg-gray-100">
                                    <div id="map" style="height: 400px; width: 100%;" class="rounded-lg"></div>
                                </div>
                                
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <button type="button" 
                                            id="center-map-on-coordinates"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition duration-200 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Haritayı Koordinatlara Ortala
                                    </button>
                                    <a href="https://www.google.com/maps?q={{ old('latitude', $property->latitude) ?: '35.1856' }},{{ old('longitude', $property->longitude) ?: '33.3823' }}" 
                                       target="_blank"
                                       class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition duration-200 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                        Google Haritalar'da Görüntüle
                                    </a>
                                </div>
                            </div>

                            <div id="location-status" class="text-sm hidden"></div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Ek Notlar (İsteğe Bağlı)
                            </label>
                            <textarea name="notes" 
                                      id="notes" 
                                      rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror"
                                      placeholder="Bu mülk hakkında ek notlar...">{{ old('notes', $property->notes) }}</textarea>
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
                                Mülkü Güncelle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const districts = @json($districts);
            const neighborhoods = @json($neighborhoods);
            const citySelect = document.getElementById('city');
            const districtSelect = document.getElementById('district');
            const neighborhoodSelect = document.getElementById('neighborhood');
            const oldDistrict = '{{ old("district", $property->district) }}';
            const oldNeighborhood = '{{ old("neighborhood", $property->neighborhood) }}';
            const locationButton = document.getElementById('get-current-location');
            const locationStatus = document.getElementById('location-status');
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');
            const centerMapButton = document.getElementById('center-map-on-coordinates');

            // Leaflet map variables
            let map = null;
            let marker = null;

            // Default coordinates (Cyprus/Turkey region)
            const defaultLat = {{ old('latitude', $property->latitude) ?: '35.1856' }};
            const defaultLng = {{ old('longitude', $property->longitude) ?: '33.3823' }};

            // Initialize Leaflet map
            function initMap() {
                // Initialize map with default or existing coordinates
                const lat = parseFloat(latitudeInput.value) || defaultLat;
                const lng = parseFloat(longitudeInput.value) || defaultLng;
                
                map = L.map('map').setView([lat, lng], 12);
                
                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
                
                // Add marker if coordinates exist
                if (latitudeInput.value && longitudeInput.value) {
                    marker = L.marker([lat, lng]).addTo(map);
                }
                
                // Handle map clicks
                map.on('click', function(e) {
                    const clickedLat = e.latlng.lat;
                    const clickedLng = e.latlng.lng;
                    
                    // Update input fields
                    latitudeInput.value = clickedLat.toFixed(8);
                    longitudeInput.value = clickedLng.toFixed(8);
                    
                    // Update marker
                    updateMapLocation();
                    
                    showLocationStatus('Harita tıklamasından konum güncellendi!', 'success');
                });
            }

            // Update map location with current coordinates
            function updateMapLocation() {
                if (!map) return;
                
                const lat = parseFloat(latitudeInput.value);
                const lng = parseFloat(longitudeInput.value);
                
                if (isNaN(lat) || isNaN(lng)) return;
                
                // Remove existing marker
                if (marker) {
                    map.removeLayer(marker);
                }
                
                // Add new marker
                marker = L.marker([lat, lng]).addTo(map);
                
                // Center map on new location
                map.setView([lat, lng], map.getZoom());
            }

            // Function to update districts based on selected city
            function updateDistricts() {
                const selectedCity = citySelect.value;
                districtSelect.innerHTML = '<option value="">Bir ilçe seçin</option>';
                neighborhoodSelect.innerHTML = '<option value="">Bir mahalle seçin</option>';
                
                if (selectedCity && districts[selectedCity]) {
                    districts[selectedCity].forEach(function(district) {
                        const option = document.createElement('option');
                        option.value = district;
                        option.textContent = district;
                        if (district === oldDistrict) {
                            option.selected = true;
                        }
                        districtSelect.appendChild(option);
                    });
                }
                
                // Update neighborhoods if district is already selected
                if (districtSelect.value) {
                    updateNeighborhoods();
                }
            }

            // Function to update neighborhoods based on selected city and district
            function updateNeighborhoods() {
                const selectedCity = citySelect.value;
                const selectedDistrict = districtSelect.value;
                neighborhoodSelect.innerHTML = '<option value="">@if(auth()->user() && auth()->user()->company_id)Bir mahalle/site seçin@elseBir mahalle seçin@endif</option>';
                
                if (selectedCity && selectedDistrict) {
                    @if(auth()->user() && auth()->user()->company_id)
                        // For company users, fetch combined neighborhoods and company sites
                        fetch(`/api/combined-neighborhoods?city=${encodeURIComponent(selectedCity)}&district=${encodeURIComponent(selectedDistrict)}`)
                            .then(response => response.json())
                            .then(data => {
                                data.forEach(function(neighborhood) {
                                    const option = document.createElement('option');
                                    option.value = neighborhood;
                                    option.textContent = neighborhood;
                                    if (neighborhood === oldNeighborhood) {
                                        option.selected = true;
                                    }
                                    neighborhoodSelect.appendChild(option);
                                });
                            })
                            .catch(error => {
                                console.error('Error fetching neighborhoods:', error);
                            });
                    @else
                        // For solo handymen, use static data
                        if (neighborhoods[selectedCity] && neighborhoods[selectedCity][selectedDistrict]) {
                            neighborhoods[selectedCity][selectedDistrict].forEach(function(neighborhood) {
                                const option = document.createElement('option');
                                option.value = neighborhood;
                                option.textContent = neighborhood;
                                if (neighborhood === oldNeighborhood) {
                                    option.selected = true;
                                }
                                neighborhoodSelect.appendChild(option);
                            });
                        }
                    @endif
                }
            }

            // Update districts when city changes
            citySelect.addEventListener('change', function() {
                updateDistricts();
            });

            // Update neighborhoods when district changes
            districtSelect.addEventListener('change', function() {
                updateNeighborhoods();
            });

            // Center map on coordinates button
            if (centerMapButton) {
                centerMapButton.addEventListener('click', function() {
                    const lat = parseFloat(latitudeInput.value);
                    const lng = parseFloat(longitudeInput.value);
                    
                    if (lat && lng && map) {
                        map.setView([lat, lng], 15);
                        showLocationStatus('Harita mevcut koordinatlara ortalandı', 'info');
                    } else {
                        showLocationStatus('Lütfen önce geçerli koordinatları girin', 'error');
                    }
                });
            }

            // Update map when coordinates change manually
            latitudeInput.addEventListener('change', function() {
                if (this.value) {
                    updateMapLocation();
                }
            });

            longitudeInput.addEventListener('change', function() {
                if (this.value) {
                    updateMapLocation();
                }
            });

            // Geolocation functionality
            if (locationButton) {
                locationButton.addEventListener('click', function() {
                    if (!navigator.geolocation) {
                        showLocationStatus('Tarayıcınız coğrafi konum belirlemeyi desteklemiyor.', 'error');
                        return;
                    }

                    locationButton.textContent = 'Konum alınıyor...';
                    locationButton.disabled = true;
                    showLocationStatus('Mevcut konumunuz alınıyor...', 'info');

                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            latitudeInput.value = position.coords.latitude.toFixed(8);
                            longitudeInput.value = position.coords.longitude.toFixed(8);
                            
                            // Update map with new coordinates
                            updateMapLocation();
                            
                            locationButton.textContent = 'Konum Alındı!';
                            showLocationStatus('Konum başarıyla alındı ve harita güncellendi!', 'success');
                            
                            setTimeout(function() {
                                locationButton.textContent = 'Mevcut Konumu Al';
                                locationButton.disabled = false;
                                hideLocationStatus();
                            }, 3000);
                        },
                        function(error) {
                            let errorMessage;
                            switch(error.code) {
                                case error.PERMISSION_DENIED:
                                    errorMessage = 'Konum erişimi kullanıcı tarafından reddedildi.';
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    errorMessage = 'Konum bilgisi mevcut değil.';
                                    break;
                                case error.TIMEOUT:
                                    errorMessage = 'Konum isteği zaman aşımına uğradı.';
                                    break;
                                default:
                                    errorMessage = 'Konum alınırken bilinmeyen bir hata oluştu.';
                                    break;
                            }
                            
                            showLocationStatus(errorMessage, 'error');
                            locationButton.textContent = 'Mevcut Konumu Al';
                            locationButton.disabled = false;
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 60000
                        }
                    );
                });
            }

            function showLocationStatus(message, type) {
                if (!locationStatus) return;
                
                locationStatus.textContent = message;
                locationStatus.className = 'text-sm block mt-2';
                
                if (type === 'error') {
                    locationStatus.classList.add('text-red-600');
                } else if (type === 'success') {
                    locationStatus.classList.add('text-green-600');
                } else {
                    locationStatus.classList.add('text-blue-600');
                }

                // Auto-hide info and success messages after 3 seconds
                if (type !== 'error') {
                    setTimeout(hideLocationStatus, 3000);
                }
            }

            function hideLocationStatus() {
                if (!locationStatus) return;
                locationStatus.className = 'text-sm hidden';
                locationStatus.textContent = '';
            }

            // Load districts for current city on page load
            updateDistricts();

            // Initialize map
            initMap();
        });
    </script>
</body>
</html>

