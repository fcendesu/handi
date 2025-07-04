<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Keşif Oluşturma - {{ config('app.name', 'Handi') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
</head>

<body class="bg-gray-100">
    <x-navigation />

    <!-- Success Message -->
    @if (session('success'))
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
            <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm"
                x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 00-1.414 1.414l2 2a1 1 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        {{ session('success') }}
                    </div>
                    <button @click="show = false" class="text-green-700 hover:text-green-500">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 011.414 0L10 8.586l4.293-4.293a1 1 111.414 1.414L11.414 10l4.293 4.293a1 1 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
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
                        <h2 class="text-2xl font-semibold text-gray-800">Yeni Keşif Oluştur

                        </h2>

                    </div>

                    <form action="{{ route('discovery.store') }}" method="POST" enctype="multipart/form-data"
                        class="space-y-8">
                        @csrf

                        <div class="space-y-8">
                            <!-- Customer Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="customer_name"
                                        class="block text-sm font-medium text-gray-700 mb-2">Müşteri Adı</label>
                                    <input type="text" name="customer_name" id="customer_name"
                                        class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                        value="{{ old('customer_name') }}" required>
                                    @error('customer_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="customer_phone"
                                        class="block text-sm font-medium text-gray-700 mb-2">Telefon Numarası</label>
                                    <input type="text" name="customer_phone" id="customer_phone"
                                        class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                        value="{{ old('customer_phone') }}" required>
                                    @error('customer_phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="customer_email"
                                        class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" name="customer_email" id="customer_email"
                                        class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                        value="{{ old('customer_email') }}" required>
                                    @error('customer_email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="col-span-full">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Adres</label>

                                    <!-- Property Selection -->
                                    <div x-data="propertySelector()" class="space-y-4">
                                        <!-- Option selector -->
                                        <div class="flex space-x-4">
                                            <label class="flex items-center">
                                                <input type="radio" name="address_type" value="property"
                                                    x-model="addressType" class="mr-2">
                                                <span class="text-sm text-gray-700">Kayıtlı Mülk Seç</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" name="address_type" value="manual"
                                                    x-model="addressType" class="mr-2">
                                                <span class="text-sm text-gray-700">Manuel Adres Gir</span>
                                            </label>
                                        </div>

                                        <!-- Property Selection -->
                                        <div x-show="addressType === 'property'" class="space-y-3">
                                            <select name="property_id" x-model="selectedPropertyId"
                                                @change="onPropertyChange()"
                                                class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                                <option value="">Bir mülk seçin</option>
                                                <template x-for="property in properties" :key="property.id">
                                                    <option :value="property.id"
                                                        x-text="property.name + ' - ' + property.full_address"></option>
                                                </template>
                                            </select>

                                            <!-- Selected property preview -->
                                            <div x-show="selectedProperty"
                                                class="p-3 bg-blue-50 rounded-md border border-blue-200">
                                                <div class="text-sm font-medium text-blue-900"
                                                    x-text="selectedProperty ? selectedProperty.name : ''"></div>
                                                <div class="text-sm text-blue-700"
                                                    x-text="selectedProperty ? selectedProperty.full_address : ''">
                                                </div>
                                                <div x-show="selectedProperty && selectedProperty.has_map_location"
                                                    class="mt-2">
                                                    <a :href="'https://www.google.com/maps?q=' + (selectedProperty ?
                                                        selectedProperty.latitude : '') + ',' + (selectedProperty ?
                                                        selectedProperty.longitude : '')"
                                                        target="_blank"
                                                        class="text-blue-600 hover:text-blue-800 text-sm">
                                                        Haritada Görüntüle →
                                                    </a>
                                                </div>
                                            </div>

                                            <!-- Link to add new property -->
                                            <div class="text-center py-2">
                                                <a href="{{ route('properties.create') }}" target="_blank"
                                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                                    + Yeni Mülk Ekle
                                                </a>
                                            </div>
                                        </div> <!-- Manual Address Input -->
                                        <div x-show="addressType === 'manual'" x-data="manualAddressSelector()"
                                            class="space-y-4">
                                            <!-- City Selection -->
                                            <div>
                                                <label for="city"
                                                    class="block text-sm font-medium text-gray-700 mb-2">Şehir</label>
                                                <select name="city" id="city" x-model="selectedCity"
                                                    @change="updateDistricts()"
                                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                                    <option value="">Bir şehir seçin</option>
                                                    @foreach ($cities as $city)
                                                        <option value="{{ $city }}">{{ $city }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('city')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <!-- District Selection -->
                                            <div>
                                                <label for="district"
                                                    class="block text-sm font-medium text-gray-700 mb-2">Bölge</label>
                                                <select name="district" id="district"
                                                    x-model="selectedDistrict"
                                                    @change="updateNeighborhoods()"
                                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                                    <option value="">Önce şehir seçin</option>
                                                    <template x-for="district in districts" :key="district">
                                                        <option :value="district" x-text="district"></option>
                                                    </template>
                                                </select>
                                                @error('district')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <!-- Neighborhood Selection -->
                                            <div>
                                                <label for="neighborhood"
                                                    class="block text-sm font-medium text-gray-700 mb-2">
                                                    @if (auth()->user()->isSoloHandyman())
                                                        Mahalle
                                                    @else
                                                        Mahalle/Site
                                                    @endif
                                                </label>
                                                <select name="neighborhood" id="neighborhood"
                                                    x-model="selectedNeighborhood"
                                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                                    <option value="">Önce şehir ve ilçe seçin</option>
                                                    <template x-for="neighborhood in neighborhoods" :key="neighborhood">
                                                        <option :value="neighborhood" x-text="neighborhood"></option>
                                                    </template>
                                                </select>
                                                @error('neighborhood')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <!-- Remaining Address Details -->
                                            <div>
                                                <label for="address"
                                                    class="block text-sm font-medium text-gray-700 mb-2">Adres
                                                    Detayları</label>
                                                <textarea name="address" id="address" rows="3"
                                                    placeholder="Site adı, sokak, kapı numarası vb. detayları girin..."
                                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">{{ old('address') }}</textarea>
                                                @error('address')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <!-- Map Location Picker -->
                                            <div class="space-y-3">
                                                <div class="flex items-center justify-between">
                                                    <h4 class="text-sm font-medium text-gray-700">Konum Seçici (İsteğe
                                                        Bağlı)</h4>
                                                    <button type="button" @click="getCurrentLocation()"
                                                        :disabled="loadingLocation"
                                                        class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-3 py-1 rounded text-sm transition duration-200">
                                                        <span x-show="!loadingLocation">Mevcut Konumu Al</span>
                                                        <span x-show="loadingLocation">Konum Alınıyor...</span>
                                                    </button>
                                                </div>

                                                <!-- Coordinate Display -->
                                                <div class="grid grid-cols-2 gap-3" x-show="latitude && longitude">
                                                    <div class="bg-blue-50 border border-blue-200 rounded p-2">
                                                        <div class="text-xs font-medium text-blue-700">Enlem</div>
                                                        <div class="text-sm font-mono text-blue-900"
                                                            x-text="latitude"></div>
                                                    </div>
                                                    <div class="bg-blue-50 border border-blue-200 rounded p-2">
                                                        <div class="text-xs font-medium text-blue-700">Boylam</div>
                                                        <div class="text-sm font-mono text-blue-900"
                                                            x-text="longitude"></div>
                                                    </div>
                                                </div>

                                                <!-- Error Display -->
                                                <div x-show="locationError" class="text-sm text-red-600"
                                                    x-text="locationError"></div>

                                                <!-- Interactive Map -->
                                                <div class="border border-gray-300 rounded-lg overflow-hidden">
                                                    <div id="manualAddressMap" style="height: 300px; width: 100%;">
                                                    </div>
                                                </div>

                                                <p class="text-xs text-gray-500 text-center">
                                                    Harita üzerine tıklayarak konum seçebilirsiniz
                                                </p>

                                                <!-- Hidden coordinate inputs for form submission -->
                                                <input type="hidden" name="latitude" x-model="latitude">
                                                <input type="hidden" name="longitude" x-model="longitude">
                                            </div>
                                        </div>
                                    </div>

                                    @error('address')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    @error('property_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Discovery Details -->
                            <div>
                                <label for="discovery" class="block text-sm font-medium text-gray-700 mb-2">Keşif
                                    Detayı</label>
                                <textarea name="discovery" id="discovery" rows="4"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                    required>{{ old('discovery') }}</textarea>
                                @error('discovery')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="todo_list"
                                    class="block text-sm font-medium text-gray-700 mb-2">Yapılacaklar Listesi</label>
                                <textarea name="todo_list" id="todo_list" rows="4"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">{{ old('todo_list') }}</textarea>
                                @error('todo_list')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Priority Selection -->
                            <div>
                                <label for="priority_id" class="block text-sm font-medium text-gray-700 mb-2">Öncelik
                                    Seviyesi</label>
                                <select name="priority_id" id="priority_id"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="">Öncelik seçin (isteğe bağlı)</option>
                                    @foreach ($priorities as $priority)
                                        <option value="{{ $priority->id }}"
                                            {{ old('priority_id') == $priority->id ? 'selected' : '' }}
                                            style="color: {{ $priority->color }};">
                                            {{ $priority->name }} (Seviye {{ $priority->level }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('priority_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Work Group Selection -->
                            @if ($workGroups && $workGroups->count() > 0)
                                <div>
                                    <label for="work_group_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        @if (auth()->user()->isSoloHandyman())
                                            Çalışma Kategorisi
                                        @else
                                            Çalışma Grubu
                                        @endif
                                        <span class="text-gray-500">(İsteğe Bağlı)</span>
                                    </label>
                                    <select name="work_group_id" id="work_group_id"
                                        class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                        <option value="">
                                            @if (auth()->user()->isSoloHandyman())
                                                Kategori seçin (isteğe bağlı)
                                            @else
                                                Çalışma grubu seçin (isteğe bağlı)
                                            @endif
                                        </option>
                                        @foreach ($workGroups as $workGroup)
                                            <option value="{{ $workGroup->id }}"
                                                {{ old('work_group_id') == $workGroup->id ? 'selected' : '' }}>
                                                {{ $workGroup->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('work_group_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            <!-- Assignee Selection (only for company admins) -->
                            @if (auth()->user()->isCompanyAdmin() && $assignableEmployees && $assignableEmployees->count() > 0)
                                <div>
                                    <label for="assignee_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        Atanan Personel
                                        <span class="text-gray-500">(İsteğe Bağlı)</span>
                                    </label>
                                    <select name="assignee_id" id="assignee_id"
                                        class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                        <option value="">Personel ataması yok</option>
                                        @foreach ($assignableEmployees as $employee)
                                            <option value="{{ $employee->id }}"
                                                {{ old('assignee_id') == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }} ({{ $employee->email }})
                                                @if($employee->workGroups && $employee->workGroups->count() > 0)
                                                    - Gruplar: {{ $employee->workGroups->pluck('name')->join(', ') }}
                                                @else
                                                    - Grup ataması yok
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('assignee_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            <!-- Item Selection -->
                            <div x-data="itemSelector()" class="space-y-4 pt-3">
                                <div class="flex justify-between items-center">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Malzemeler</label>
                                    <button type="button" @click="openModal()"
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
                                                    <button type="button" @click="removeItem(index)"
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
                                    <p class="text-sm text-gray-400">Malzeme eklemek için yukarıdaki butonu kullanın
                                    </p>
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
                                    <label for="completion_time"
                                        class="block text-sm font-medium text-gray-700 mb-2">Tamamlanma Süresi
                                        (gün)</label>
                                    <input type="number" name="completion_time" id="completion_time"
                                        class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                        min="1" value="{{ old('completion_time') }}">
                                    @error('completion_time')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="offer_valid_until"
                                        class="block text-sm font-medium text-gray-700 mb-2">Teklif Geçerlilik
                                        Tarihi *</label>
                                    <input type="date" name="offer_valid_until" id="offer_valid_until" required
                                        class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                        value="{{ old('offer_valid_until') }}">
                                    @error('offer_valid_until')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="service_cost"
                                        class="block text-sm font-medium text-gray-700 mb-2">Servis Masrafı</label>
                                    <input type="number" name="service_cost" id="service_cost"
                                        class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                        min="0" step="0.01" value="{{ old('service_cost', 0) }}">
                                    @error('service_cost')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="transportation_cost"
                                        class="block text-sm font-medium text-gray-700 mb-2">Ulaşım Masrafı</label>
                                    <input type="number" name="transportation_cost" id="transportation_cost"
                                        class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                        min="0" step="0.01" value="{{ old('transportation_cost', 0) }}">
                                    @error('transportation_cost')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="labor_cost"
                                        class="block text-sm font-medium text-gray-700 mb-2">İşçilik Masrafı</label>
                                    <input type="number" name="labor_cost" id="labor_cost"
                                        class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                        min="0" step="0.01" value="{{ old('labor_cost', 0) }}">
                                    @error('labor_cost')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="extra_fee" class="block text-sm font-medium text-gray-700 mb-2">Ekstra
                                        Masraflar</label>
                                    <input type="number" name="extra_fee" id="extra_fee"
                                        class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                        min="0" step="0.01" value="{{ old('extra_fee', 0) }}">
                                    @error('extra_fee')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="discount_rate"
                                        class="block text-sm font-medium text-gray-700 mb-2">İndirim Oranı (%)</label>
                                    <input type="number" name="discount_rate" id="discount_rate"
                                        class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                        min="0" max="100" step="0.01"
                                        value="{{ old('discount_rate', 0) }}">
                                    @error('discount_rate')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="discount_amount"
                                        class="block text-sm font-medium text-gray-700 mb-2">İndirim Miktarı</label>
                                    <input type="number" name="discount_amount" id="discount_amount"
                                        class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
                                        min="0" step="0.01" value="{{ old('discount_amount', 0) }}">
                                    @error('discount_amount')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label for="note_to_customer"
                                    class="block text-sm font-medium text-gray-700 mb-2">Müşteriye Not</label>
                                <textarea name="note_to_customer" id="note_to_customer" rows="3"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">{{ old('note_to_customer') }}</textarea>
                            </div>

                            <div>
                                <label for="note_to_handi" class="block text-sm font-medium text-gray-700 mb-2">Özel
                                    Not</label>
                                <textarea name="note_to_handi" id="note_to_handi" rows="3"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">{{ old('note_to_handi') }}</textarea>
                            </div>

                            <!-- Payment Method -->
                            <div x-data="paymentMethodSelector()" x-init="loadPaymentMethods()">
                                <label for="payment_method_id"
                                    class="block text-sm font-medium text-gray-700 mb-2">Ödeme Şekli</label>
                                <select name="payment_method_id" id="payment_method_id"
                                    x-model="selectedPaymentMethodId"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                    <option value="">Ödeme şekli seçin (opsiyonel)</option>
                                    <template x-for="paymentMethod in paymentMethods" :key="paymentMethod.id">
                                        <option :value="paymentMethod.id" x-text="paymentMethod.name"
                                            :selected="paymentMethod.id == '{{ old('payment_method_id') }}'"></option>
                                    </template>
                                </select>
                                @error('payment_method_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Image Upload with Preview -->
                            <div x-data="imageUploader()">
                                <div class="flex justify-between items-center mb-2">
                                    <label for="images"
                                        class="block text-sm font-medium text-gray-700">Resimler</label>
                                    <button type="button" @click="clearAllImages()" x-show="previews.length > 0"
                                        class="text-sm text-red-600 hover:text-red-800">
                                        Clear All
                                    </button>
                                </div>
                                <input type="file" name="images[]" id="images" multiple accept="image/*"
                                    @change="previewImages($event)"
                                    class="bg-gray-100 mt-1 block w-full rounded-md border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                                @error('images.*')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                <!-- Image Preview Grid -->
                                <div x-show="previews.length > 0"
                                    class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    <template x-for="(preview, index) in previews" :key="index">
                                        <div class="relative group">
                                            <!-- Image with click to enlarge -->
                                            <img :src="preview" 
                                                 class="h-40 w-full object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                                 @click="viewImage(preview)">
                                            
                                            <!-- Enhanced remove button overlay -->
                                            <button type="button" 
                                                    @click="removeImage(index)"
                                                    class="absolute -top-2 -right-2 w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center shadow-lg opacity-80 hover:opacity-100 transition-all duration-200 hover:scale-110">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>

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
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
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
                                <button type="submit"
                                    class="bg-blue-500 text-white px-6 py-3 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-lg font-medium">
                                    Keşif Oluştur
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function itemSelector() {
            return {
                searchQuery: '',
                searchResults: [],
                allItems: [], // Store all items loaded initially
                selectedItems: [],
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

        function paymentMethodSelector() {
            return {
                paymentMethods: [],
                selectedPaymentMethodId: '{{ old('payment_method_id') }}',
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
                    } catch (error) {
                        console.error('Error loading payment methods:', error);
                        this.paymentMethods = [];
                    }
                }
            }
        }

        function propertySelector() {
            return {
                addressType: '{{ old('property_id') ? 'property' : (old('address') || old('city') || old('district') ? 'manual' : 'property') }}',
                properties: [],
                selectedPropertyId: '{{ old('property_id') }}',
                selectedProperty: null,

                async init() {
                    await this.loadProperties();
                    if (this.selectedPropertyId) {
                        this.onPropertyChange();
                    }
                },

                async loadProperties() {
                    try {
                        const response = await fetch('/api/company-properties');
                        const data = await response.json();
                        this.properties = data;
                    } catch (error) {
                        console.error('Error loading properties:', error);
                    }
                },

                onPropertyChange() {
                    this.selectedProperty = this.properties.find(p => p.id == this.selectedPropertyId) || null;
                }
            }
        }

        function imageUploader() {
            return {
                previews: [],
                fileInput: null,
                showImageModal: false,
                selectedImage: null,

                init() {
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
                    this.previews = [];

                    Array.from(event.target.files).forEach(file => {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.previews.push(e.target.result);
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                },

                removeImage(index) {
                    // Remove from previews array
                    this.previews.splice(index, 1);

                    // Remove from file input
                    const dt = new DataTransfer();
                    const files = Array.from(this.fileInput.files);
                    files.splice(index, 1);
                    files.forEach(file => dt.items.add(file));
                    this.fileInput.files = dt.files;

                    // Clear input if no files left
                    if (files.length === 0) {
                        this.fileInput.value = '';
                    }
                },

                clearAllImages() {
                    this.previews = [];
                    this.fileInput.value = '';
                }
            }
        }

        function manualAddressSelector() {
            return {
                selectedCity: '{{ old('city') ?: old('manual_city') }}',
                selectedDistrict: '{{ old('district') ?: old('manual_district') }}',
                selectedNeighborhood: '{{ old('neighborhood') ?: old('manual_neighborhood') }}',
                districts: [],
                neighborhoods: [],
                cityDistricts: @json($districts),
                cityNeighborhoods: @json($neighborhoods),
                latitude: {{ old('latitude', old('manual_latitude', 'null')) }},
                longitude: {{ old('longitude', old('manual_longitude', 'null')) }},
                loadingLocation: false,
                locationError: '',
                map: null,
                marker: null,

                init() {
                    this.updateDistricts();
                    this.updateNeighborhoods();
                    this.initMap();

                    // Watch for coordinate changes
                    this.$watch('latitude', () => this.updateMapLocation());
                    this.$watch('longitude', () => this.updateMapLocation());
                },

                updateDistricts() {
                    this.districts = this.cityDistricts[this.selectedCity] || [];
                    if (!this.districts.includes(this.selectedDistrict)) {
                        this.selectedDistrict = '';
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
                            this.neighborhoods = this.cityNeighborhoods[this.selectedCity]?.[this.selectedDistrict] || [];
                            if (!this.neighborhoods.includes(this.selectedNeighborhood)) {
                                this.selectedNeighborhood = '';
                            }
                        });
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
                            switch (error.code) {
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
                    this.map = L.map('manualAddressMap').setView([defaultLat, defaultLng], 10);

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
