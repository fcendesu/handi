<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@if(auth()->user()->isSoloHandyman()) Çalışma Kategorileri @else Çalışma Grupları @endif</title>
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
                        <h2 class="text-2xl font-semibold text-gray-800">
                            @if(auth()->user()->isSoloHandyman()) Çalışma Kategorileri @else Çalışma Grupları @endif
                        </h2>
                        @if(auth()->user()->isSoloHandyman() || auth()->user()->isCompanyAdmin())
                            <button onclick="showCreateModal()" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                @if(auth()->user()->isSoloHandyman()) Yeni Kategori Oluştur @else Yeni Grup Oluştur @endif
                            </button>
                        @endif
                    </div>

                    <!-- Success/Error Messages -->
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

                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Work Groups List -->
                    @if($workGroups->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($workGroups as $workGroup)
                                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-4">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $workGroup->name }}</h3>
                                        @if(auth()->user()->isSoloHandyman() && $workGroup->creator_id === auth()->id() || auth()->user()->isCompanyAdmin())
                                            <div class="flex space-x-2">
                                                <a href="{{ route('work-groups.show', $workGroup) }}" 
                                                   class="text-blue-600 hover:text-blue-800">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </a>
                                                <button onclick="showDeleteModal('{{ $workGroup->name }}', '{{ route('work-groups.destroy', $workGroup) }}')" 
                                                        class="text-red-600 hover:text-red-800">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                      <div class="space-y-2 text-sm text-gray-600">
                                        <p><strong>Oluşturan:</strong> {{ $workGroup->creator->name }}</p>
                                        @if($workGroup->company)
                                            <p><strong>Şirket:</strong> {{ $workGroup->company->name }}</p>
                                        @else
                                            <p><strong>Tip:</strong> Bireysel Usta</p>
                                        @endif
                                        @if(auth()->user()->isSoloHandyman())
                                            <p><strong>Keşif Sayısı:</strong> {{ $workGroup->discoveries->count() }}</p>
                                        @else
                                            <p><strong>Üye Sayısı:</strong> {{ $workGroup->users->count() }}</p>
                                            <p><strong>Keşif Sayısı:</strong> {{ $workGroup->discoveries->count() }}</p>
                                        @endif
                                        <p><strong>Oluşturulma:</strong> {{ $workGroup->created_at->format('d.m.Y') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $workGroups->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2m5-8a3 3 0 110-6 3 3 0 010 6zm5 3a4 4 0 00-8 0v3h8v-3z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">
                                @if(auth()->user()->isSoloHandyman()) Henüz çalışma kategorisi yok @else Henüz çalışma grubu yok @endif
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if(auth()->user()->isSoloHandyman())
                                    Başlamak için yeni bir çalışma kategorisi oluşturun.
                                @elseif(auth()->user()->isCompanyAdmin())
                                    Başlamak için yeni bir çalışma grubu oluşturun.
                                @else
                                    Yöneticiniz tarafından bir çalışma grubuna atanmanızı bekleyin.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create Work Group Modal -->
    <div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    @if(auth()->user()->isSoloHandyman()) Yeni Çalışma Kategorisi @else Yeni Çalışma Grubu @endif
                </h3>
                <form method="POST" action="{{ route('work-groups.store') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            @if(auth()->user()->isSoloHandyman()) Kategori Adı @else Grup Adı @endif
                        </label>
                        <input type="text" id="name" name="name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideCreateModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            İptal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                            Oluştur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    @if(auth()->user()->isSoloHandyman()) Çalışma Kategorisini Sil @else Çalışma Grubunu Sil @endif
                </h3>
                <p class="text-sm text-gray-500 mb-4">
                    <span id="groupName"></span> 
                    @if(auth()->user()->isSoloHandyman()) 
                        çalışma kategorisini silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
                    @else 
                        çalışma grubunu silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
                    @endif
                </p>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-center space-x-3">
                        <button type="button" onclick="hideDeleteModal()" 
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

    <script>
        function showCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }

        function hideCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        function showDeleteModal(groupName, deleteUrl) {
            document.getElementById('groupName').textContent = groupName;
            document.getElementById('deleteForm').action = deleteUrl;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function hideDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>
</body>
</html>
