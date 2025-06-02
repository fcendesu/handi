<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Şirket Yönetimi</title>
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
                        <h2 class="text-2xl font-semibold text-gray-800">Şirket Yönetimi</h2>
                        <a href="{{ route('company.show', $company) }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Şirket Detayları
                        </a>
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

                    <!-- Company Overview -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ $company->name }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $company->employees->count() }}</div>
                                <div class="text-sm text-gray-600">Toplam Çalışan</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ $company->workGroups->count() }}</div>
                                <div class="text-sm text-gray-600">Çalışma Grubu</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">{{ $company->discoveries->count() }}</div>
                                <div class="text-sm text-gray-600">Toplam Keşif</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-orange-600">{{ $company->discoveries->where('status', 'pending')->count() }}</div>
                                <div class="text-sm text-gray-600">Bekleyen Keşif</div>
                            </div>
                        </div>
                    </div>

                    <!-- Company Admins Management -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Şirket Yöneticileri</h3>
                            @if($company->admin_id === auth()->user()->id)
                                <button onclick="showCreateAdminModal()" 
                                    class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                                    Yeni Yönetici Ekle
                                </button>
                            @endif
                        </div>

                        @if($company->allAdmins->count() > 0)
                            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                                <ul class="divide-y divide-gray-200">
                                    @foreach($company->allAdmins as $admin)
                                        <li class="px-6 py-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <div class="h-10 w-10 rounded-full bg-purple-300 flex items-center justify-center">
                                                            <span class="text-sm font-medium text-purple-700">
                                                                {{ strtoupper(substr($admin->name, 0, 2)) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="flex items-center">
                                                            <div class="text-sm font-medium text-gray-900">{{ $admin->name }}</div>
                                                            @if($admin->id === $company->admin_id)
                                                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                                    Ana Yönetici
                                                                </span>
                                                            @else
                                                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                                    Yönetici
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="text-sm text-gray-500">{{ $admin->email }}</div>
                                                        <div class="text-xs text-gray-400 mt-1">
                                                            Katılım: {{ $admin->created_at->format('d.m.Y') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                @if($company->admin_id === auth()->user()->id && $admin->id !== auth()->user()->id)
                                                    <div class="flex items-center space-x-2">
                                                        <button onclick="showTransferPrimaryModal('{{ $admin->name }}', {{ $admin->id }})" 
                                                                class="text-yellow-600 hover:text-yellow-800 text-sm">
                                                            Ana Yönetici Yap
                                                        </button>
                                                        <button onclick="showDemoteAdminModal('{{ $admin->name }}', {{ $admin->id }})" 
                                                                class="text-red-600 hover:text-red-800">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="bg-white shadow rounded-lg p-6 text-center">
                                <p class="text-gray-500">Henüz ek yönetici yok</p>
                            </div>
                        @endif
                    </div>

                    <!-- Employee Management -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Çalışan Yönetimi</h3>
                            <button onclick="showCreateEmployeeModal()" 
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Yeni Çalışan Ekle
                            </button>
                        </div>

                        @if($company->employees->count() > 0)
                            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                                <ul class="divide-y divide-gray-200">
                                    @foreach($company->employees as $employee)
                                        <li class="px-6 py-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                            <span class="text-sm font-medium text-gray-700">
                                                                {{ strtoupper(substr($employee->name, 0, 2)) }}
                                                            </span>
                                                        </div>
                                                    </div>                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $employee->email }}</div>
                                        <div class="mt-2">
                                            @if($employee->workGroups->count() > 0)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($employee->workGroups as $workGroup)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ $workGroup->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                    Atanmamış çalışma alanı
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1">
                                            Keşifler: {{ $employee->assignedDiscoveries->count() }} • 
                                            Katılım: {{ $employee->created_at->format('d.m.Y') }}
                                        </div>
                                    </div>
                                                </div>                                                <div class="flex items-center space-x-2">
                                                    @if($company->admin_id === auth()->user()->id)
                                                        <button onclick="showPromoteEmployeeModal('{{ $employee->name }}', {{ $employee->id }})" 
                                                                class="text-purple-600 hover:text-purple-800" title="Yöneticiye Terfi Et">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                                            </svg>
                                                        </button>
                                                    @endif
                                                    <button onclick="showEditEmployeeModal({{ $employee->id }}, '{{ $employee->name }}', '{{ $employee->email }}', {{ $employee->workGroups->pluck('id') }})" 
                                                            class="text-blue-600 hover:text-blue-800">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </button>
                                                    <button onclick="showDeleteEmployeeModal('{{ $employee->name }}', {{ $employee->id }})" 
                                                            class="text-red-600 hover:text-red-800">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="text-center py-12 bg-gray-50 rounded-lg">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2m5-8a3 3 0 110-6 3 3 0 010 6zm5 3a4 4 0 00-8 0v3h8v-3z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Henüz çalışan yok</h3>
                                <p class="mt-1 text-sm text-gray-500">Şirketinize ilk çalışanınızı ekleyin.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Work Groups Summary -->
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Çalışma Grupları</h3>
                            <a href="{{ route('work-groups.index') }}" 
                               class="text-blue-600 hover:text-blue-800 font-medium">
                                Tümünü Gör →
                            </a>
                        </div>

                        @if($company->workGroups->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($company->workGroups->take(6) as $workGroup)
                                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                                        <h4 class="font-medium text-gray-900 mb-2">{{ $workGroup->name }}</h4>
                                        <div class="text-sm text-gray-600 space-y-1">
                                            <p>Üye Sayısı: {{ $workGroup->users->count() }}</p>
                                            <p>Keşif Sayısı: {{ $workGroup->discoveries->count() }}</p>
                                        </div>
                                        <div class="mt-2">
                                            <a href="{{ route('work-groups.show', $workGroup) }}" 
                                               class="text-blue-600 hover:text-blue-800 text-sm">
                                                Detayları Gör →
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 bg-gray-50 rounded-lg">
                                <p class="text-gray-500">Henüz çalışma grubu oluşturulmamış.</p>
                                <a href="{{ route('work-groups.index') }}" 
                                   class="text-blue-600 hover:text-blue-800 font-medium">
                                    İlk grubunuzu oluşturun →
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Employee Modal -->
    <div id="createEmployeeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Yeni Çalışan Ekle</h3>
                <form method="POST" action="{{ route('company.create-employee') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Ad Soyad</label>
                        <input type="text" id="name" name="name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-posta</label>
                        <input type="email" id="email" name="email" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Şifre</label>
                        <input type="password" id="password" name="password" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Şifre Tekrar</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="work_group_ids" class="block text-sm font-medium text-gray-700 mb-2">Çalışma Grupları (İsteğe Bağlı)</label>
                        <select id="work_group_ids" name="work_group_ids[]" multiple 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($company->workGroups as $workGroup)
                                <option value="{{ $workGroup->id }}">{{ $workGroup->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Ctrl tuşu ile birden fazla seçim yapabilirsiniz</p>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideCreateEmployeeModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            İptal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                            Ekle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div id="editEmployeeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Çalışan Düzenle</h3>
                <form id="editEmployeeForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-4">
                        <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-2">Ad Soyad</label>
                        <input type="text" id="edit_name" name="name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-2">E-posta</label>
                        <input type="email" id="edit_email" name="email" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="edit_password" class="block text-sm font-medium text-gray-700 mb-2">Yeni Şifre (İsteğe Bağlı)</label>
                        <input type="password" id="edit_password" name="password" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="edit_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Şifre Tekrar</label>
                        <input type="password" id="edit_password_confirmation" name="password_confirmation" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="edit_work_group_ids" class="block text-sm font-medium text-gray-700 mb-2">Çalışma Grupları</label>
                        <select id="edit_work_group_ids" name="work_group_ids[]" multiple 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($company->workGroups as $workGroup)
                                <option value="{{ $workGroup->id }}">{{ $workGroup->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Ctrl tuşu ile birden fazla seçim yapabilirsiniz</p>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideEditEmployeeModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            İptal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                            Güncelle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Employee Modal -->
    <div id="deleteEmployeeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Çalışanı Sil</h3>
                <p class="text-sm text-gray-500 mb-4">
                    <span id="employeeName"></span> çalışanını silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
                </p>
                <form id="deleteEmployeeForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-center space-x-3">
                        <button type="button" onclick="hideDeleteEmployeeModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            İptal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            Sil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Admin Modal -->
    <div id="createAdminModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Yeni Yönetici Ekle</h3>
                <form method="POST" action="{{ route('company.create-admin') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="admin_name" class="block text-sm font-medium text-gray-700 mb-2">Ad Soyad</label>
                        <input type="text" id="admin_name" name="name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-2">E-posta</label>
                        <input type="email" id="admin_email" name="email" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-2">Şifre</label>
                        <input type="password" id="admin_password" name="password" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="admin_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Şifre Onayı</label>
                        <input type="password" id="admin_password_confirmation" name="password_confirmation" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideCreateAdminModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            İptal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-purple-500 text-white rounded-md hover:bg-purple-600">
                            Ekle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Promote Employee Modal -->
    <div id="promoteEmployeeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Çalışanı Yöneticiye Terfi Et</h3>
                <p class="text-sm text-gray-500 mb-4">
                    <span id="promoteEmployeeName"></span> çalışanını şirket yöneticisi yapmak istediğinizden emin misiniz?
                </p>
                <form id="promoteEmployeeForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="flex justify-center space-x-3">
                        <button type="button" onclick="hidePromoteEmployeeModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            İptal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-purple-500 text-white rounded-md hover:bg-purple-600">
                            Terfi Et
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Demote Admin Modal -->
    <div id="demoteAdminModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Yöneticiyi Çalışan Yap</h3>
                <p class="text-sm text-gray-500 mb-4">
                    <span id="demoteAdminName"></span> yöneticisini çalışan yapmak istediğinizden emin misiniz?
                </p>
                <form id="demoteAdminForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="flex justify-center space-x-3">
                        <button type="button" onclick="hideDemoteAdminModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            İptal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            Çalışan Yap
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Transfer Primary Admin Modal -->
    <div id="transferPrimaryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Ana Yöneticilik Devret</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Ana yöneticilik yetkisini <span id="transferAdminName"></span> kişisine devretmek istediğinizden emin misiniz? Bu işlemden sonra o kişi şirketin ana yöneticisi olacak.
                </p>
                <form id="transferPrimaryForm" method="POST" action="{{ route('company.transfer-primary-admin') }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" id="new_admin_id" name="new_admin_id">
                    <div class="flex justify-center space-x-3">
                        <button type="button" onclick="hideTransferPrimaryModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            İptal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                            Devret
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showCreateEmployeeModal() {
            document.getElementById('createEmployeeModal').classList.remove('hidden');
        }

        function hideCreateEmployeeModal() {
            document.getElementById('createEmployeeModal').classList.add('hidden');
        }

        function showEditEmployeeModal(employeeId, name, email, workGroupIds) {
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('editEmployeeForm').action = `/company/employees/${employeeId}`;
            
            // Set work group selections
            const select = document.getElementById('edit_work_group_ids');
            for (let option of select.options) {
                option.selected = workGroupIds.includes(parseInt(option.value));
            }
            
            document.getElementById('editEmployeeModal').classList.remove('hidden');
        }

        function hideEditEmployeeModal() {
            document.getElementById('editEmployeeModal').classList.add('hidden');
        }

        function showDeleteEmployeeModal(employeeName, employeeId) {
            document.getElementById('employeeName').textContent = employeeName;
            document.getElementById('deleteEmployeeForm').action = `/company/employees/${employeeId}`;
            document.getElementById('deleteEmployeeModal').classList.remove('hidden');
        }

        function hideDeleteEmployeeModal() {
            document.getElementById('deleteEmployeeModal').classList.add('hidden');
        }

        // Admin Management Functions
        function showCreateAdminModal() {
            document.getElementById('createAdminModal').classList.remove('hidden');
        }

        function hideCreateAdminModal() {
            document.getElementById('createAdminModal').classList.add('hidden');
        }

        function showPromoteEmployeeModal(employeeName, employeeId) {
            document.getElementById('promoteEmployeeName').textContent = employeeName;
            document.getElementById('promoteEmployeeForm').action = `/company/employees/${employeeId}/promote`;
            document.getElementById('promoteEmployeeModal').classList.remove('hidden');
        }

        function hidePromoteEmployeeModal() {
            document.getElementById('promoteEmployeeModal').classList.add('hidden');
        }

        function showDemoteAdminModal(adminName, adminId) {
            document.getElementById('demoteAdminName').textContent = adminName;
            document.getElementById('demoteAdminForm').action = `/company/admins/${adminId}/demote`;
            document.getElementById('demoteAdminModal').classList.remove('hidden');
        }

        function hideDemoteAdminModal() {
            document.getElementById('demoteAdminModal').classList.add('hidden');
        }

        function showTransferPrimaryModal(adminName, adminId) {
            document.getElementById('transferAdminName').textContent = adminName;
            document.getElementById('new_admin_id').value = adminId;
            document.getElementById('transferPrimaryModal').classList.remove('hidden');
        }

        function hideTransferPrimaryModal() {
            document.getElementById('transferPrimaryModal').classList.add('hidden');
        }
    </script>
</body>
</html>
