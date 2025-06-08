<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $company->name }} - Şirket Detayları</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <x-navigation />

    <!-- Main Content -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-800">{{ $company->name }}</h2>
                            <p class="text-gray-600">Şirket Detayları</p>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('company.index') }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Geri Dön
                            </a>
                            <button onclick="openEditModal()" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Düzenle
                            </button>
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Company Information -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                        <!-- Company Details Card -->
                        <div class="lg:col-span-2">
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Şirket Bilgileri</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Şirket Adı</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $company->name }}</p>
                                    </div>
                                    @if($company->address)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Adres</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $company->address }}</p>
                                    </div>
                                    @endif
                                    @if($company->phone)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Telefon</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $company->phone }}</p>
                                    </div>
                                    @endif
                                    @if($company->email)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">E-posta</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $company->email }}</p>
                                    </div>
                                    @endif
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Oluşturulma Tarihi</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $company->created_at->format('d.m.Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Card -->
                        <div>
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">İstatistikler</h3>
                                <div class="space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Toplam Çalışan</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $company->employees->count() }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">İş Grupları</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $company->workGroups->count() }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Toplam Keşif</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $company->discoveries->count() }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Aktif Keşif</span>
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ $company->discoveries->where('status', 'active')->count() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Information -->
                    <div class="mb-8">
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Şirket Yöneticisi</h3>
                            @if($company->admin)
                            <div class="flex items-center space-x-4">
                                <div class="bg-blue-500 text-white rounded-full h-10 w-10 flex items-center justify-center">
                                    {{ strtoupper(substr($company->admin->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $company->admin->name }}</p>
                                    <p class="text-sm text-gray-600">{{ $company->admin->email }}</p>
                                </div>
                            </div>
                            @else
                            <p class="text-gray-600">Şirket yöneticisi atanmamış</p>
                            @endif
                        </div>                    </div>

                    <!-- Company Employees -->
                    <div class="mb-8">
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-800">Çalışanlar ve Çalışma Alanları</h3>
                                <a href="{{ route('company.index') }}" 
                                   class="bg-green-500 hover:bg-green-700 text-white text-sm py-1 px-3 rounded">
                                    Çalışan Yönet
                                </a>
                            </div>
                            @if($company->employees->count() > 0)
                                <div class="space-y-4">
                                    @foreach($company->employees as $employee)
                                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                            <div class="flex items-start justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <div class="bg-green-500 text-white rounded-full h-10 w-10 flex items-center justify-center">
                                                        {{ strtoupper(substr($employee->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <h4 class="font-medium text-gray-900">{{ $employee->name }}</h4>
                                                        <p class="text-sm text-gray-600">{{ $employee->email }}</p>
                                                        <p class="text-xs text-gray-500 mt-1">
                                                            Katılım: {{ $employee->created_at->format('d.m.Y') }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    @if($employee->workGroups->count() > 0)
                                                        <div class="mb-2">
                                                            <span class="text-xs font-medium text-gray-700">Çalışma Alanları:</span>
                                                        </div>
                                                        <div class="flex flex-wrap gap-1 justify-end max-w-xs">
                                                            @foreach($employee->workGroups as $workGroup)
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                    {{ $workGroup->name }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                            Atanmamış Çalışma Alanı
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($employee->workGroups->count() > 0)
                                                <div class="mt-3 pt-3 border-t border-gray-100">
                                                    <div class="text-xs text-gray-500">
                                                        <strong>Aktif Projeler:</strong> 
                                                        {{ $employee->workGroups->sum(function($group) { return $group->discoveries->where('status', 'active')->count(); }) }}
                                                        |
                                                        <strong>Tamamlanan:</strong> 
                                                        {{ $employee->workGroups->sum(function($group) { return $group->discoveries->where('status', 'completed')->count(); }) }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-2.25" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Henüz çalışan yok</h3>
                                    <p class="mt-1 text-sm text-gray-500">Şirketinize çalışan ekleyerek başlayın.</p>
                                    <div class="mt-6">
                                        <a href="{{ route('company.index') }}" 
                                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                            Çalışan Ekle
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Work Groups -->
                    <div class="mb-8">
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-800">İş Grupları</h3>
                                <a href="{{ route('work-groups.index') }}" 
                                   class="bg-blue-500 hover:bg-blue-700 text-white text-sm py-1 px-3 rounded">
                                    Yönet
                                </a>
                            </div>
                            @if($company->workGroups->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($company->workGroups as $workGroup)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-2">{{ $workGroup->name }}</h4>
                                    <p class="text-sm text-gray-600 mb-2">{{ $workGroup->description }}</p>
                                    <div class="flex justify-between items-center text-xs text-gray-500">
                                        <span>{{ $workGroup->users->count() }} çalışan</span>
                                        <a href="{{ route('work-groups.show', $workGroup) }}" 
                                           class="text-blue-600 hover:text-blue-800">Detay</a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-gray-600">Henüz iş grubu oluşturulmamış</p>
                            @endif
                        </div>
                    </div>

                    <!-- Recent Discoveries -->
                    <div class="mb-8">
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-800">Son Keşifler</h3>
                                <a href="{{ route('discovery') }}" 
                                   class="bg-blue-500 hover:bg-blue-700 text-white text-sm py-1 px-3 rounded">
                                    Tümünü Gör
                                </a>
                            </div>
                            @if($company->discoveries->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Başlık</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oluşturan</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($company->discoveries->take(5) as $discovery)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $discovery->title }}</div>
                                                <div class="text-sm text-gray-500">{{ Str::limit($discovery->description, 50) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $discovery->status === 'active' ? 'bg-green-100 text-green-800' : 
                                                       ($discovery->status === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                                    {{ ucfirst($discovery->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $discovery->creator->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $discovery->created_at->format('d.m.Y') }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="text-gray-600">Henüz keşif oluşturulmamış</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Company Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Şirket Bilgilerini Düzenle</h3>
                <form action="{{ route('company.update', $company) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label for="edit_name" class="block text-sm font-medium text-gray-700">Şirket Adı</label>
                        <input type="text" name="name" id="edit_name" value="{{ $company->name }}" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    </div>

                    <div class="mb-4">
                        <label for="edit_address" class="block text-sm font-medium text-gray-700">Adres</label>
                        <textarea name="address" id="edit_address" rows="3" 
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ $company->address }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label for="edit_phone" class="block text-sm font-medium text-gray-700">Telefon</label>
                        <input type="text" name="phone" id="edit_phone" value="{{ $company->phone }}" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="mb-6">
                        <label for="edit_email" class="block text-sm font-medium text-gray-700">E-posta</label>
                        <input type="email" name="email" id="edit_email" value="{{ $company->email }}" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeEditModal()" 
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            İptal
                        </button>
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Güncelle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openEditModal() {
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            let modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
