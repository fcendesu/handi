<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Property - İşler</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                        <h1 class="text-3xl font-bold text-gray-900">Edit Property: {{ $property->name }}</h1>
                    </div>

                    <form action="{{ route('properties.update', $property) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Property Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Property Name *
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name', $property->name) }}"
                                   required
                                   placeholder="e.g., Main Office, Customer Site A"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- City and District -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                                    City *
                                </label>
                                <select name="city" 
                                        id="city" 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('city') border-red-500 @enderror">
                                    <option value="">Select a city</option>
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
                                    District *
                                </label>
                                <select name="district" 
                                        id="district" 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('district') border-red-500 @enderror">
                                    <option value="">Select a district</option>
                                </select>
                                @error('district')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Address Components -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">Address Details (Optional)</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="site_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Site Name
                                    </label>
                                    <input type="text" 
                                           name="site_name" 
                                           id="site_name" 
                                           value="{{ old('site_name', $property->site_name) }}"
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
                                           value="{{ old('building_name', $property->building_name) }}"
                                           placeholder="e.g., Building A"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('building_name') border-red-500 @enderror">
                                    @error('building_name')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="street" class="block text-sm font-medium text-gray-700 mb-2">
                                    Street
                                </label>
                                <input type="text" 
                                       name="street" 
                                       id="street" 
                                       value="{{ old('street', $property->street) }}"
                                       placeholder="e.g., Şehit Salahi Şevket Sokağı"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('street') border-red-500 @enderror">
                                @error('street')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="door_apartment_no" class="block text-sm font-medium text-gray-700 mb-2">
                                    Door/Apartment Number
                                </label>
                                <input type="text" 
                                       name="door_apartment_no" 
                                       id="door_apartment_no" 
                                       value="{{ old('door_apartment_no', $property->door_apartment_no) }}"
                                       placeholder="e.g., 15A or Apartment 3"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('door_apartment_no') border-red-500 @enderror">
                                @error('door_apartment_no')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Geolocation -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">Location Coordinates (Optional)</h3>
                                <button type="button" 
                                        id="get-current-location"
                                        class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-md text-sm transition duration-200">
                                    Get Current Location
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="latitude" class="block text-sm font-medium text-gray-700 mb-2">
                                        Latitude
                                    </label>
                                    <input type="number" 
                                           name="latitude" 
                                           id="latitude" 
                                           step="0.00000001"
                                           value="{{ old('latitude', $property->latitude) }}"
                                           placeholder="35.1856"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('latitude') border-red-500 @enderror">
                                    @error('latitude')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="longitude" class="block text-sm font-medium text-gray-700 mb-2">
                                        Longitude
                                    </label>
                                    <input type="number" 
                                           name="longitude" 
                                           id="longitude" 
                                           step="0.00000001"
                                           value="{{ old('longitude', $property->longitude) }}"
                                           placeholder="33.3823"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('longitude') border-red-500 @enderror">
                                    @error('longitude')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            @if($property->hasMapLocation())
                                <div class="mt-3">
                                    <a href="https://www.google.com/maps?q={{ $property->latitude }},{{ $property->longitude }}" 
                                       target="_blank" 
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        View current location on Google Maps →
                                    </a>
                                </div>
                            @endif

                            <div id="location-status" class="text-sm hidden"></div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Additional Notes (Optional)
                            </label>
                            <textarea name="notes" 
                                      id="notes" 
                                      rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror"
                                      placeholder="Any additional notes about this property...">{{ old('notes', $property->notes) }}</textarea>
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
                                Update Property
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
            const citySelect = document.getElementById('city');
            const districtSelect = document.getElementById('district');
            const oldDistrict = '{{ old("district", $property->district) }}';
            const locationButton = document.getElementById('get-current-location');
            const locationStatus = document.getElementById('location-status');
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');

            // Function to update districts based on selected city
            function updateDistricts() {
                const selectedCity = citySelect.value;
                districtSelect.innerHTML = '<option value="">Select a district</option>';
                
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
            }

            // Update districts when city changes
            citySelect.addEventListener('change', function() {
                updateDistricts();
                console.log('City changed, districts updated');
            });

            // Load districts for current city on page load
            updateDistricts();

            // Geolocation functionality
            locationButton.addEventListener('click', function() {
                if (!navigator.geolocation) {
                    showLocationStatus('Geolocation is not supported by this browser.', 'error');
                    return;
                }

                locationButton.textContent = 'Getting location...';
                locationButton.disabled = true;
                showLocationStatus('Getting your current location...', 'info');

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        latitudeInput.value = position.coords.latitude.toFixed(8);
                        longitudeInput.value = position.coords.longitude.toFixed(8);
                        
                        locationButton.textContent = 'Location Obtained!';
                        showLocationStatus('Location successfully obtained!', 'success');
                        
                        setTimeout(function() {
                            locationButton.textContent = 'Get Current Location';
                            locationButton.disabled = false;
                            hideLocationStatus();
                        }, 3000);
                    },
                    function(error) {
                        let errorMessage;
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage = 'Location access denied by user.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage = 'Location information is unavailable.';
                                break;
                            case error.TIMEOUT:
                                errorMessage = 'Location request timed out.';
                                break;
                            default:
                                errorMessage = 'An unknown error occurred while getting location.';
                                break;
                        }
                        
                        showLocationStatus(errorMessage, 'error');
                        locationButton.textContent = 'Get Current Location';
                        locationButton.disabled = false;
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 60000
                    }
                );
            });

            function showLocationStatus(message, type) {
                locationStatus.textContent = message;
                locationStatus.className = 'text-sm block mt-2';
                
                if (type === 'error') {
                    locationStatus.classList.add('text-red-600');
                } else if (type === 'success') {
                    locationStatus.classList.add('text-green-600');
                } else {
                    locationStatus.classList.add('text-blue-600');
                }
            }

            function hideLocationStatus() {
                locationStatus.className = 'text-sm hidden';
                locationStatus.textContent = '';
            }
        });
    </script>
</body>
</html>

