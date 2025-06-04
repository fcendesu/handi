<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $property->name }} - İşler</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <x-navigation />

    <!-- Main Content -->
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <a href="{{ route('properties.index') }}" 
                               class="text-blue-600 hover:text-blue-800 mr-4">
                                ← Back to Properties
                            </a>
                            <h1 class="text-3xl font-bold text-gray-900">{{ $property->name }}</h1>
                        </div>
                        <div class="flex space-x-3">
                            @can('update', $property)
                                <a href="{{ route('properties.edit', $property) }}" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                    Edit Property
                                </a>
                            @endcan
                            @can('delete', $property)
                                <form action="{{ route('properties.destroy', $property) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this property?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                        Delete Property
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Property Details -->
                        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                <h2 class="text-xl font-semibold text-gray-900">Property Details</h2>
                            </div>
                            <div class="p-6">
                                <dl class="space-y-4">                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Property Name</dt>
                                        <dd class="mt-1 text-lg text-gray-900">{{ $property->name }}</dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Owner</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $property->isSoloHandymanProperty() ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $property->owner_name }}
                                            </span>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $property->address }}</dd>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">City</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    {{ $property->city }}
                                                </span>
                                            </dd>
                                        </div>

                                        @if($property->neighborhood)
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">Neighborhood</dt>
                                                <dd class="mt-1 text-sm text-gray-900">{{ $property->neighborhood }}</dd>
                                            </div>
                                        @endif
                                    </div>

                                    @if($property->notes)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $property->notes }}</dd>
                                        </div>
                                    @endif

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $property->created_at->format('M d, Y') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $property->updated_at->format('M d, Y') }}</dd>
                                        </div>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Map and Location -->
                        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                <h2 class="text-xl font-semibold text-gray-900">Location</h2>
                            </div>
                            <div class="p-6">
                                @if($property->latitude && $property->longitude)
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">Latitude</dt>
                                                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $property->latitude }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">Longitude</dt>
                                                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $property->longitude }}</dd>
                                            </div>
                                        </div>

                                        <div class="space-y-2">
                                            <a href="https://www.google.com/maps?q={{ $property->latitude }},{{ $property->longitude }}" 
                                               target="_blank"
                                               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition duration-200">
                                                View on Google Maps
                                                <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                            </a>
                                        </div>

                                        <!-- Simple Map Display -->
                                        <div class="mt-4 bg-gray-100 rounded-lg h-64 flex items-center justify-center">
                                            <div class="text-center">
                                                <div class="text-gray-600 mb-2">📍</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $property->latitude }}, {{ $property->longitude }}
                                                </div>
                                                <div class="text-xs text-gray-400 mt-1">
                                                    Click "View on Google Maps" for full map
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <div class="text-gray-400 text-lg mb-2">📍</div>
                                        <div class="text-gray-500">No location coordinates set</div>
                                        <p class="text-sm text-gray-400 mt-2">Edit the property to add location coordinates</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Associated Discoveries -->
                    @if($property->discoveries && $property->discoveries->count() > 0)
                        <div class="mt-8 bg-white shadow-lg rounded-lg overflow-hidden">
                            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                <h2 class="text-xl font-semibold text-gray-900">
                                    Associated Discoveries ({{ $property->discoveries->count() }})
                                </h2>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Customer
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Discovery
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($property->discoveries as $discovery)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $discovery->customer_name }}</div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-900">{{ Str::limit($discovery->discovery, 60) }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                        @if($discovery->status === 'pending') bg-yellow-100 text-yellow-800
                                                        @elseif($discovery->status === 'in_progress') bg-blue-100 text-blue-800
                                                        @elseif($discovery->status === 'completed') bg-green-100 text-green-800
                                                        @else bg-gray-100 text-gray-800 @endif">
                                                        {{ ucfirst(str_replace('_', ' ', $discovery->status)) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $discovery->created_at->format('M d, Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('discovery.show', $discovery) }}" 
                                                       class="text-blue-600 hover:text-blue-900">
                                                        View Discovery
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <a href="{{ route('properties.index') }}" 
                   class="text-blue-600 hover:text-blue-800 mr-4">
                    ← Back to Properties
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ $property->name }}</h1>
            </div>
            <div class="flex space-x-3">
                @can('update', $property)
                    <a href="{{ route('properties.edit', $property) }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                        Edit Property
                    </a>
                @endcan
                @can('delete', $property)
                    <form action="{{ route('properties.destroy', $property) }}" 
                          method="POST" 
                          class="inline"
                          onsubmit="return confirm('Are you sure you want to delete this property?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                            Delete Property
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Property Details -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Property Details</h2>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Property Name</dt>
                            <dd class="mt-1 text-lg text-gray-900">{{ $property->name }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Full Address</dt>
                            <dd class="mt-1 text-gray-900">{{ $property->full_address }}</dd>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">City</dt>
                                <dd class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $property->city }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Neighborhood</dt>
                                <dd class="mt-1 text-gray-900">{{ $property->neighborhood }}</dd>
                            </div>
                        </div>

                        @if($property->site_name)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Site Name</dt>
                                <dd class="mt-1 text-gray-900">{{ $property->site_name }}</dd>
                            </div>
                        @endif

                        @if($property->building_name)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Building Name</dt>
                                <dd class="mt-1 text-gray-900">{{ $property->building_name }}</dd>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Street</dt>
                                <dd class="mt-1 text-gray-900">{{ $property->street }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Door/Apartment No</dt>
                                <dd class="mt-1 text-gray-900">{{ $property->door_apartment_no }}</dd>
                            </div>
                        </div>

                        @if($property->notes)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                <dd class="mt-1 text-gray-900">{{ $property->notes }}</dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-gray-900">{{ $property->created_at->format('M j, Y \a\t g:i A') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Map Location -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Map Location</h2>
                </div>
                <div class="p-6">
                    @if($property->hasMapLocation())
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Latitude</dt>
                                    <dd class="mt-1 text-gray-900 font-mono">{{ $property->latitude }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Longitude</dt>
                                    <dd class="mt-1 text-gray-900 font-mono">{{ $property->longitude }}</dd>
                                </div>
                            </div>

                            <div class="flex space-x-3">
                                <a href="https://www.google.com/maps?q={{ $property->latitude }},{{ $property->longitude }}" 
                                   target="_blank" 
                                   class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                    View on Google Maps
                                </a>
                                <a href="https://maps.apple.com/?q={{ $property->latitude }},{{ $property->longitude }}" 
                                   target="_blank" 
                                   class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                    View on Apple Maps
                                </a>
                            </div>

                            <!-- Simple embedded map -->
                            <div class="mt-4">
                                <iframe 
                                    width="100%" 
                                    height="250" 
                                    frameborder="0" 
                                    style="border:0" 
                                    src="https://www.google.com/maps/embed/v1/place?key=YOUR_GOOGLE_MAPS_API_KEY&q={{ $property->latitude }},{{ $property->longitude }}&zoom=16"
                                    allowfullscreen>
                                </iframe>
                                <p class="text-xs text-gray-500 mt-2">
                                    Note: Google Maps API key required for embedded map display
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-400 text-lg mb-2">No map location set</div>
                            <p class="text-gray-500 mb-4">Add coordinates to show this property on a map</p>
                            @can('update', $property)
                                <a href="{{ route('properties.edit', $property) }}" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                    Add Location
                                </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Related Discoveries -->
        @if($property->discoveries->count() > 0)
            <div class="mt-8 bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Related Discoveries ({{ $property->discoveries->count() }})
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Customer
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Created
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($property->discoveries as $discovery)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $discovery->customer_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $discovery->customer_phone }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($discovery->status === 'completed') bg-green-100 text-green-800
                                            @elseif($discovery->status === 'in_progress') bg-blue-100 text-blue-800
                                            @elseif($discovery->status === 'pending') bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $discovery->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $discovery->created_at->format('M j, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('discovery.show', $discovery) }}" 
                                           class="text-blue-600 hover:text-blue-900">
                                            View Discovery
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
