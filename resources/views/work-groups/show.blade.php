<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $workGroup->name }} - @if(auth()->user()->isSoloHandyman()) Çalışma Kategorisi @else Çalışma Grubu @endif</title>
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
                <div class="p-6">                    <!-- Header -->
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-800">{{ $workGroup->name }}</h2>
                            <p class="text-gray-600">
                                @if(auth()->user()->isSoloHandyman())
                                    Çalışma Kategorisi • Oluşturan: {{ $workGroup->creator->name }}
                                @else
                                    Oluşturan: {{ $workGroup->creator->name }} • 
                                    @if($workGroup->company)
                                        Şirket: {{ $workGroup->company->name }}
                                    @else
                                        Bireysel Usta
                                    @endif
                                @endif
                            </p>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('work-groups.index') }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Geri Dön
                            </a>
                            @can('update', $workGroup)
                                <button onclick="showEditModal()" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    @if(auth()->user()->isSoloHandyman()) Kategoriyi Düzenle @else Grubu Düzenle @endif
                                </button>
                            @endcan
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
                    @endif                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        @if(auth()->user()->isSoloHandyman())
                            <!-- Solo Handyman Statistics -->
                            <div class="bg-green-50 p-6 rounded-lg">
                                <div class="flex items-center">
                                    <div class="p-3 bg-green-500 rounded-full">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-green-600">Toplam Keşif</p>
                                        <p class="text-2xl font-semibold text-green-900">{{ $workGroup->discoveries->count() }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-blue-50 p-6 rounded-lg">
                                <div class="flex items-center">
                                    <div class="p-3 bg-blue-500 rounded-full">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-blue-600">Tamamlanan</p>
                                        <p class="text-2xl font-semibold text-blue-900">{{ $workGroup->discoveries->where('status', 'completed')->count() }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-purple-50 p-6 rounded-lg">
                                <div class="flex items-center">
                                    <div class="p-3 bg-purple-500 rounded-full">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-purple-600">Oluşturulma</p>
                                        <p class="text-2xl font-semibold text-purple-900">{{ $workGroup->created_at->format('M Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Company Statistics -->
                            <div class="bg-blue-50 p-6 rounded-lg">
                                <div class="flex items-center">
                                    <div class="p-3 bg-blue-500 rounded-full">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2m5-8a3 3 0 110-6 3 3 0 010 6zm5 3a4 4 0 00-8 0v3h8v-3z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-blue-600">Toplam Üye</p>
                                        <p class="text-2xl font-semibold text-blue-900">{{ $workGroup->users->count() }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-green-50 p-6 rounded-lg">
                                <div class="flex items-center">
                                    <div class="p-3 bg-green-500 rounded-full">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-green-600">Toplam Keşif</p>
                                        <p class="text-2xl font-semibold text-green-900">{{ $workGroup->discoveries->count() }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-purple-50 p-6 rounded-lg">
                                <div class="flex items-center">
                                    <div class="p-3 bg-purple-500 rounded-full">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-purple-600">Oluşturulma</p>
                                        <p class="text-2xl font-semibold text-purple-900">{{ $workGroup->created_at->format('M Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif</div>

                    <!-- Work Category Info (Solo Handyman) or Group Members (Company Users) -->
                    @if(auth()->user()->isSoloHandyman())
                        <!-- Solo Handyman - Show category description and discoveries -->
                        <div class="mb-8">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-800">Çalışma Kategorisi Detayları</h3>
                            </div>
                            
                            <div class="bg-blue-50 p-6 rounded-lg mb-6">
                                <div class="flex items-start">
                                    <div class="p-3 bg-blue-500 rounded-full">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-lg font-medium text-blue-900">{{ $workGroup->name }}</h4>
                                        <p class="text-blue-700 mt-2">
                                            Bu çalışma kategorisi keşiflerinizi düzenli bir şekilde gruplandırmanıza ve organize etmenize yardımcı olur. 
                                            Benzer türdeki işleri bu kategori altında toplayarak daha etkili çalışabilirsiniz.
                                        </p>
                                        <div class="mt-4 flex flex-wrap gap-4 text-sm text-blue-600">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                </svg>
                                                {{ $workGroup->discoveries->count() }} keşif
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                {{ $workGroup->created_at->format('d.m.Y') }} tarihinde oluşturuldu
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Discoveries in this Category -->
                            @if($workGroup->discoveries->count() > 0)
                                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                                    <div class="px-4 py-3 bg-gray-50 border-b">
                                        <h4 class="text-sm font-medium text-gray-900">Bu Kategorideki Son Keşifler</h4>
                                    </div>
                                    <ul class="divide-y divide-gray-200">
                                        @foreach($workGroup->discoveries->take(5) as $discovery)
                                            <li class="px-6 py-4">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex-1">
                                                        <div class="text-sm font-medium text-gray-900">{{ $discovery->title }}</div>
                                                        <div class="text-sm text-gray-500">{{ $discovery->created_at->format('d.m.Y H:i') }}</div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                            @if($discovery->status === 'completed') bg-green-100 text-green-800
                                                            @elseif($discovery->status === 'in_progress') bg-blue-100 text-blue-800
                                                            @elseif($discovery->status === 'pending') bg-yellow-100 text-yellow-800
                                                            @else bg-gray-100 text-gray-800 @endif">
                                                            @if($discovery->status === 'completed') Tamamlandı
                                                            @elseif($discovery->status === 'in_progress') Devam Ediyor
                                                            @elseif($discovery->status === 'pending') Beklemede
                                                            @else İptal Edildi @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                    @if($workGroup->discoveries->count() > 5)
                                        <div class="bg-gray-50 px-6 py-3">
                                            <div class="text-sm text-gray-500">
                                                Ve {{ $workGroup->discoveries->count() - 5 }} keşif daha...
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-center py-12 bg-gray-50 rounded-lg">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Henüz keşif yok</h3>
                                    <p class="mt-1 text-sm text-gray-500">Bu kategoride henüz keşif bulunmuyor.</p>
                                    <div class="mt-6">
                                        <a href="{{ route('discovery') }}" 
                                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Yeni Keşif Oluştur
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Company Users - Show actual group members -->
                        <div class="mb-8">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-800">Grup Üyeleri</h3>
                                @can('update', $workGroup)
                                    <button onclick="showAssignModal()" 
                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        Üye Ekle
                                    </button>
                                @endcan
                            </div>

                            @if($workGroup->users->count() > 0)
                                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                                    <ul class="divide-y divide-gray-200">
                                        @foreach($workGroup->users as $user)
                                            <li class="px-6 py-4">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                                <span class="text-sm font-medium text-gray-700">
                                                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                            @if($user->user_type === 'company_admin') bg-blue-100 text-blue-800
                                                            @elseif($user->user_type === 'company_employee') bg-green-100 text-green-800
                                                            @else bg-gray-100 text-gray-800 @endif">
                                                            @if($user->user_type === 'company_admin') Yönetici
                                                            @elseif($user->user_type === 'company_employee') Çalışan
                                                            @else Bireysel Usta @endif
                                                        </span>
                                                        @can('update', $workGroup)
                                                            <button onclick="showRemoveModal('{{ $user->name }}', {{ $user->id }})" 
                                                                    class="text-red-600 hover:text-red-800">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                </svg>
                                                            </button>
                                                        @endcan
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
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Henüz üye yok</h3>
                                    <p class="mt-1 text-sm text-gray-500">Bu çalışma grubuna henüz üye atanmamış.</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Work Group Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    @if(auth()->user()->isSoloHandyman()) Çalışma Kategorisini Düzenle @else Çalışma Grubunu Düzenle @endif
                </h3>
                <form method="POST" action="{{ route('work-groups.update', $workGroup) }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-4">
                        <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-2">
                            @if(auth()->user()->isSoloHandyman()) Kategori Adı @else Grup Adı @endif
                        </label>
                        <input type="text" id="edit_name" name="name" value="{{ $workGroup->name }}" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideEditModal()" 
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
    </div>    @if(!auth()->user()->isSoloHandyman())
        <!-- Assign User Modal -->
        <div id="assignModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Üye Ekle</h3>
                    <form method="POST" action="{{ route('work-groups.assign-user', $workGroup) }}">
                        @csrf
                        <div class="mb-4">
                            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">Kullanıcı Seç</label>
                            <select id="user_id" name="user_id" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Kullanıcı seçin...</option>
                                @if(auth()->user()->isCompanyAdmin())
                                    @foreach(auth()->user()->company->employees as $employee)
                                        @if(!$workGroup->users->contains($employee))
                                            <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->email }})</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="hideAssignModal()" 
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

        <!-- Remove User Modal -->
        <div id="removeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Üyeyi Kaldır</h3>
                    <p class="text-sm text-gray-500 mb-4">
                        <span id="userName"></span> kullanıcısını bu çalışma grubundan kaldırmak istediğinizden emin misiniz?
                    </p>
                    <form id="removeForm" method="POST" action="{{ route('work-groups.remove-user', $workGroup) }}">
                        @csrf
                        <input type="hidden" id="removeUserId" name="user_id">
                        <div class="flex justify-center space-x-3">
                            <button type="button" onclick="hideRemoveModal()" 
                                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                İptal
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                                Kaldır
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script>
        function showEditModal() {
            document.getElementById('editModal').classList.remove('hidden');
        }

        function hideEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function showAssignModal() {
            document.getElementById('assignModal').classList.remove('hidden');
        }

        function hideAssignModal() {
            document.getElementById('assignModal').classList.add('hidden');
        }

        function showRemoveModal(userName, userId) {
            document.getElementById('userName').textContent = userName;
            document.getElementById('removeUserId').value = userId;
            document.getElementById('removeModal').classList.remove('hidden');
        }

        function hideRemoveModal() {
            document.getElementById('removeModal').classList.add('hidden');
        }
    </script>
</body>
</html>
