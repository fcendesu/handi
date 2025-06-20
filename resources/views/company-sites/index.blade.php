<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mahalle/Site Yönetimi - İşler</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <x-navigation />

    <!-- Main Content -->
    <div class="py-12" x-data="companySiteManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-3xl font-bold text-gray-900">Mahalle/Site Ekle</h1>
                        <a href="{{ route('properties.index') }}" 
                           class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                            ← Mülklere Geri Dön
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Filter Section -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Filtrele ve Yeni Site Ekle</h3>
                        
                        <!-- City and District Selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Şehir</label>
                                <select x-model="selectedCity" 
                                        @change="updateDistricts(); fetchCompanySites()"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Tüm şehirler</option>
                                    <template x-for="city in cities" :key="city">
                                        <option :value="city" x-text="city"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bölge</label>
                                <select x-model="selectedDistrict" 
                                        @change="fetchCompanySites()"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Tüm ilçeler</option>
                                    <template x-for="district in districts" :key="district">
                                        <option :value="district" x-text="district"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- Add New Site Section -->
                        <div x-show="selectedCity && selectedDistrict" class="p-4 bg-blue-50 rounded-lg">
                            <h4 class="text-md font-medium text-gray-900 mb-3">Yeni Mahalle/Site Ekle</h4>
                            <form @submit.prevent="addNewSite()" class="flex gap-3">
                                <input type="text" 
                                       x-model="newSiteName"
                                       placeholder="ör. Dolphin Site, Alanya Mahallesi"
                                       required
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <button type="submit"
                                        :disabled="!newSiteName.trim() || loading"
                                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-md">
                                    <span x-show="!loading">Ekle</span>
                                    <span x-show="loading">Ekleniyor...</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Sites List -->
                    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                Mevcut Mahalle/Siteler
                                <span x-show="loading" class="text-sm text-gray-500">(Yükleniyor...)</span>
                                <span x-show="!loading && companySites.length > 0" class="text-sm text-gray-500">
                                    (<span x-text="companySites.length"></span> adet)
                                </span>
                            </h3>
                        </div>

                        <div x-show="companySites.length === 0 && !loading" class="text-center py-12">
                            <div class="text-gray-500 text-lg mb-4">Henüz mahalle/site eklenmemiş</div>
                            <p class="text-gray-400">Yukarıdaki formu kullanarak ilk mahalle/site'nizi ekleyebilirsiniz</p>
                        </div>

                        <div x-show="companySites.length > 0" class="divide-y divide-gray-200">
                            <template x-for="site in companySites" :key="site.id">
                                <div class="px-6 py-4 flex justify-between items-center hover:bg-gray-50">
                                    <div>
                                        <div class="text-lg font-medium text-gray-900" x-text="site.name"></div>
                                        <div class="text-sm text-gray-500">
                                            <span x-text="site.city"></span> → <span x-text="site.district"></span>
                                        </div>
                                        <div class="text-xs text-gray-400" x-text="'Eklenme: ' + new Date(site.created_at).toLocaleDateString('tr-TR')"></div>
                                    </div>
                                    <button @click="deleteSite(site.id, site.name)"
                                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm transition duration-200">
                                        Sil
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function companySiteManager() {
            return {
                selectedCity: '',
                selectedDistrict: '',
                districts: [],
                companySites: [],
                newSiteName: '',
                loading: false,
                cities: @json(\App\Data\AddressData::getCities()),
                cityDistricts: @json(\App\Data\AddressData::getAllDistricts()),

                init() {
                    this.fetchCompanySites();
                },

                updateDistricts() {
                    this.districts = this.cityDistricts[this.selectedCity] || [];
                    this.selectedDistrict = '';
                },

                async fetchCompanySites() {
                    this.loading = true;
                    try {
                        let url = '/api/company-sites';
                        const params = new URLSearchParams();
                        
                        if (this.selectedCity) {
                            params.append('city', this.selectedCity);
                        }
                        if (this.selectedDistrict) {
                            params.append('district', this.selectedDistrict);
                        }
                        
                        if (params.toString()) {
                            url += '?' + params.toString();
                        }

                        const response = await fetch(url);
                        const data = await response.json();
                        this.companySites = data;
                    } catch (error) {
                        console.error('Error fetching company sites:', error);
                        this.companySites = [];
                        this.showError('Siteler yüklenirken bir hata oluştu');
                    } finally {
                        this.loading = false;
                    }
                },

                async addNewSite() {
                    if (!this.newSiteName.trim() || !this.selectedCity || !this.selectedDistrict) return;

                    this.loading = true;
                    try {
                        const response = await fetch('/api/company-sites', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                name: this.newSiteName.trim(),
                                city: this.selectedCity,
                                district: this.selectedDistrict
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.newSiteName = '';
                            await this.fetchCompanySites();
                            this.showSuccess('Mahalle/Site başarıyla eklendi!');
                        } else {
                            this.showError(data.error || 'Site eklenirken bir hata oluştu');
                        }
                    } catch (error) {
                        console.error('Error adding site:', error);
                        this.showError('Site eklenirken bir hata oluştu');
                    } finally {
                        this.loading = false;
                    }
                },

                async deleteSite(siteId, siteName) {
                    if (!confirm(`"${siteName}" mahalle/site'sini silmek istediğinizden emin misiniz?`)) return;

                    this.loading = true;
                    try {
                        const response = await fetch(`/api/company-sites/${siteId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            await this.fetchCompanySites();
                            this.showSuccess('Mahalle/Site başarıyla silindi!');
                        } else {
                            this.showError(data.error || 'Site silinirken bir hata oluştu');
                        }
                    } catch (error) {
                        console.error('Error deleting site:', error);
                        this.showError('Site silinirken bir hata oluştu');
                    } finally {
                        this.loading = false;
                    }
                },

                showSuccess(message) {
                    // Simple success notification
                    const notification = document.createElement('div');
                    notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                    notification.textContent = message;
                    document.body.appendChild(notification);
                    setTimeout(() => notification.remove(), 3000);
                },

                showError(message) {
                    // Simple error notification
                    const notification = document.createElement('div');
                    notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                    notification.textContent = message;
                    document.body.appendChild(notification);
                    setTimeout(() => notification.remove(), 5000);
                }
            }
        }
    </script>
</body>
</html>
