<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Add New Property - {{ config('app.name', 'Handi') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <x-navigation />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center mb-6">
                        <a href="{{ route('properties.index') }}" 
                           class="text-blue-600 hover:text-blue-800 mr-4">
                            ← Back to Properties
                        </a>
                        <h1 class="text-3xl font-bold text-gray-900">Add New Property</h1>
                    </div>

                    <form action="{{ route('properties.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Property Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Property Name *
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   placeholder="e.g., Main Office, Customer Site A"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- City Selection -->
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                                City *
                            </label>
                            <select id="city" 
                                    name="city" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('city') border-red-500 @enderror">
                                <option value="">Select a city</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city }}" {{ old('city') === $city ? 'selected' : '' }}>
                                        {{ $city }}
                                    </option>
                                @endforeach
                            </select>
                            @error('city')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Neighborhood Selection -->
                        <div>
                            <label for="neighborhood" class="block text-sm font-medium text-gray-700 mb-2">
                                Neighborhood *
                            </label>
                            <select id="neighborhood" 
                                    name="neighborhood"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('neighborhood') border-red-500 @enderror">
                                <option value="">Select a neighborhood</option>
                            </select>
                            @error('neighborhood')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Site Name (Optional) -->
                        <div>
                            <label for="site_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Site Name (Optional)
                            </label>
                            <input type="text" 
                                   id="site_name"
                                   name="site_name"
                                   value="{{ old('site_name') }}"
                                   placeholder="e.g., Business Park, Shopping Center"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('site_name') border-red-500 @enderror">
                            @error('site_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Building Name (Optional) -->
                        <div>
                            <label for="building_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Building Name (Optional)
                            </label>
                            <input type="text" 
                                   id="building_name"
                                   name="building_name"
                                   value="{{ old('building_name') }}"
                                   placeholder="e.g., Block A, Tower 1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('building_name') border-red-500 @enderror">
                            @error('building_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Street -->
                        <div>
                            <label for="street" class="block text-sm font-medium text-gray-700 mb-2">
                                Street Address *
                            </label>
                            <input type="text" 
                                   id="street"
                                   name="street"
                                   value="{{ old('street') }}"
                                   placeholder="e.g., Şehit Ecvet Yusuf Caddesi 15"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('street') border-red-500 @enderror">
                            @error('street')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Door/Apartment Number -->
                        <div>
                            <label for="door_apartment_no" class="block text-sm font-medium text-gray-700 mb-2">
                                Door/Apartment Number *
                            </label>
                            <input type="text" 
                                   id="door_apartment_no"
                                   name="door_apartment_no"
                                   value="{{ old('door_apartment_no') }}"
                                   placeholder="e.g., A-5, 12, Office 301"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('door_apartment_no') border-red-500 @enderror">
                            @error('door_apartment_no')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Map Location -->
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Map Location (Optional)</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="latitude" class="block text-sm font-medium text-gray-700 mb-2">
                                        Latitude
                                    </label>
                                    <input type="number" 
                                           id="latitude"
                                           name="latitude"
                                           value="{{ old('latitude') }}"
                                           step="any"
                                           placeholder="35.1856"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('latitude') border-red-500 @enderror">
                                    @error('latitude')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="longitude" class="block text-sm font-medium text-gray-700 mb-2">
                                        Longitude
                                    </label>
                                    <input type="number" 
                                           id="longitude"
                                           name="longitude"
                                           value="{{ old('longitude') }}"
                                           step="any"
                                           placeholder="33.3823"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('longitude') border-red-500 @enderror">
                                    @error('longitude')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <button type="button" 
                                    id="get-current-location"
                                    class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                Get Current Location
                            </button>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notes (Optional)
                            </label>
                            <textarea id="notes" 
                                      name="notes"
                                      rows="3"
                                      placeholder="Additional information about this property..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('properties.index') }}" 
                               class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Save Property
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const neighborhoods = @json($neighborhoods);
        const citySelect = document.getElementById('city');
        const neighborhoodSelect = document.getElementById('neighborhood');
        const oldNeighborhood = '{{ old("neighborhood") }}';

        // Function to update neighborhoods based on selected city
        function updateNeighborhoods() {
            const selectedCity = citySelect.value;
            neighborhoodSelect.innerHTML = '<option value="">Select a neighborhood</option>';
            
            if (selectedCity && neighborhoods[selectedCity]) {
                neighborhoods[selectedCity].forEach(function(neighborhood) {
                    const option = document.createElement('option');
                    option.value = neighborhood;
                    option.textContent = neighborhood;
                    if (neighborhood === oldNeighborhood) {
                        option.selected = true;
                    }
                    neighborhoodSelect.appendChild(option);
                });
            }
        }

        // Update neighborhoods when city changes
        citySelect.addEventListener('change', updateNeighborhoods);

        // Load neighborhoods for pre-selected city (if any)
        if (citySelect.value) {
            updateNeighborhoods();
        }

        // Geolocation functionality
        document.getElementById('get-current-location').addEventListener('click', function() {
            if (navigator.geolocation) {
                this.textContent = 'Getting location...';
                this.disabled = true;
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        document.getElementById('latitude').value = position.coords.latitude.toFixed(8);
                        document.getElementById('longitude').value = position.coords.longitude.toFixed(8);
                        
                        document.getElementById('get-current-location').textContent = 'Location Obtained!';
                        document.getElementById('get-current-location').disabled = false;
                        
                        setTimeout(function() {
                            document.getElementById('get-current-location').textContent = 'Get Current Location';
                        }, 2000);
                    },
                    function(error) {
                        alert('Error getting location: ' + error.message);
                        document.getElementById('get-current-location').textContent = 'Get Current Location';
                        document.getElementById('get-current-location').disabled = false;
                    }
                );
            } else {
                alert('Geolocation is not supported by this browser.');
            }
        });
    });
    </script>
</body>
</html>
