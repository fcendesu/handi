<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>İşlem Geçmişi</title>
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
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">İşlem Geçmişi</h2>
                        <div class="flex items-center space-x-4">
                            <p class="text-gray-600">{{ $logs->total() }} toplam aktivite kaydı</p>
                            <!-- Cleanup Button -->
                            <button onclick="toggleCleanupModal()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm">
                                Temizle
                            </button>
                        </div>
                    </div>

                    <!-- Advanced Filters -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <form method="GET" action="{{ route('transaction-logs') }}" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <!-- Entity Type Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Varlık Türü</label>
                                    <select name="entity_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Tümü</option>
                                        @foreach($entityTypes as $value => $label)
                                            <option value="{{ $value }}" {{ request('entity_type') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Action Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">İşlem</label>
                                    <select name="action" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Tümü</option>
                                        @foreach($actions as $value => $label)
                                            <option value="{{ $value }}" {{ request('action') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Performer Type Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Yapan Kişi Türü</label>
                                    <select name="performer_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Tümü</option>
                                        @foreach($performerTypes as $value => $label)
                                            <option value="{{ $value }}" {{ request('performer_type') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- User Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kullanıcı</label>
                                    <select name="user_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Tümü</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Date From -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Başlangıç Tarihi</label>
                                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <!-- Date To -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Bitiş Tarihi</label>
                                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <!-- Search -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Arama</label>
                                    <input type="text" name="search" value="{{ request('search') }}" 
                                           placeholder="Müşteri, kullanıcı, ürün adı..."
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <!-- Filter Actions -->
                                <div class="flex items-end space-x-2">
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                                        Filtrele
                                    </button>
                                    <a href="{{ route('transaction-logs') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                                        Temizle
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Transaction Logs Table -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full">                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih/Saat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Varlık/İşlem</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Yapan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hedef</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detaylar</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($logs as $log)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $log->created_at->format('j M Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-col">
                                                    <span class="text-xs font-medium text-gray-500 uppercase">{{ $log->entity_type_text }}</span>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @switch($log->action)
                                                            @case('created') bg-green-100 text-green-800 @break
                                                            @case('updated') bg-blue-100 text-blue-800 @break
                                                            @case('status_changed') bg-yellow-100 text-yellow-800 @break
                                                            @case('approved') bg-green-100 text-green-800 @break
                                                            @case('rejected') bg-red-100 text-red-800 @break
                                                            @case('assigned') bg-purple-100 text-purple-800 @break
                                                            @case('unassigned') bg-gray-100 text-gray-800 @break
                                                            @case('deleted') bg-red-100 text-red-800 @break
                                                            @case('viewed') bg-blue-100 text-blue-800 @break
                                                            @case('shared') bg-indigo-100 text-indigo-800 @break
                                                            @case('activated') bg-green-100 text-green-800 @break
                                                            @case('deactivated') bg-gray-100 text-gray-800 @break
                                                            @case('price_changed') bg-orange-100 text-orange-800 @break
                                                            @case('attached') bg-blue-100 text-blue-800 @break
                                                            @case('detached') bg-gray-100 text-gray-800 @break
                                                            @default bg-gray-100 text-gray-800
                                                        @endswitch
                                                    ">
                                                        {{ $log->action_text }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if($log->performed_by_type === 'customer')
                                                    <span class="text-blue-600 font-medium">Müşteri</span>
                                                    @if($log->performed_by_identifier)
                                                        <br><span class="text-xs text-gray-500">{{ $log->performed_by_identifier }}</span>
                                                    @endif
                                                @elseif($log->user)
                                                    <span class="font-medium">{{ $log->user->name }}</span>
                                                    <br><span class="text-xs text-gray-500">{{ $log->user->email }}</span>
                                                @else
                                                    <span class="text-gray-500 font-medium">Sistem</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if($log->entity_type === 'discovery' && $log->discovery)
                                                    <a href="{{ route('discovery.show', $log->discovery) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                                        Keşif #{{ $log->discovery->id }}
                                                    </a>
                                                    <br><span class="text-xs text-gray-500">{{ $log->discovery->customer_name }}</span>
                                                @elseif($log->entity_type === 'item')
                                                    @php
                                                        $metadata = is_array($log->metadata) ? $log->metadata : json_decode($log->metadata, true);
                                                    @endphp
                                                    <span class="font-medium">Malzeme #{{ $log->entity_id }}</span>
                                                    @if(isset($metadata['item_name']))
                                                        <br><span class="text-xs text-gray-500">{{ $metadata['item_name'] }}</span>
                                                    @endif
                                                @elseif($log->entity_type === 'property')
                                                    @php
                                                        $metadata = is_array($log->metadata) ? $log->metadata : json_decode($log->metadata, true);
                                                    @endphp
                                                    <span class="font-medium">Mülk #{{ $log->entity_id }}</span>
                                                    @if(isset($metadata['property_name']))
                                                        <br><span class="text-xs text-gray-500">{{ $metadata['property_name'] }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-gray-500">{{ ucfirst($log->entity_type) }} #{{ $log->entity_id ?? $log->discovery_id }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                @if($log->old_values && $log->new_values)
                                                    @php
                                                        $oldValues = is_array($log->old_values) ? $log->old_values : json_decode($log->old_values, true);
                                                        $newValues = is_array($log->new_values) ? $log->new_values : json_decode($log->new_values, true);
                                                    @endphp
                                                    @if(isset($oldValues['status']) && isset($newValues['status']))
                                                        <span class="text-gray-500">{{ ucfirst($oldValues['status']) }}</span>
                                                        <span class="mx-1">→</span>
                                                        <span class="font-medium">{{ ucfirst($newValues['status']) }}</span>
                                                    @elseif(isset($oldValues['price']) && isset($newValues['price']))
                                                        <span class="text-gray-500">₺{{ number_format($oldValues['price'], 2) }}</span>
                                                        <span class="mx-1">→</span>
                                                        <span class="font-medium">₺{{ number_format($newValues['price'], 2) }}</span>
                                                    @elseif(isset($newValues['assignee_name']))
                                                        Atanan: <span class="font-medium">{{ $newValues['assignee_name'] }}</span>
                                                    @else
                                                        <div class="max-w-xs">
                                                            @foreach($newValues as $key => $value)
                                                                <div class="text-xs">
                                                                    <span class="font-medium">{{ ucfirst($key) }}:</span> {{ is_array($value) ? json_encode($value) : $value }}
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                @elseif($log->new_values)
                                                    @php $newValues = is_array($log->new_values) ? $log->new_values : json_decode($log->new_values, true); @endphp
                                                    @if(isset($newValues['customer_name']))
                                                        <span class="font-medium">{{ $newValues['customer_name'] }}</span>
                                                    @elseif(isset($newValues['item']))
                                                        <span class="font-medium">{{ $newValues['item'] }}</span>
                                                    @elseif(isset($newValues['name']))
                                                        <span class="font-medium">{{ $newValues['name'] }}</span>
                                                    @else
                                                        <div class="max-w-xs text-xs">
                                                            @foreach($newValues as $key => $value)
                                                                <div>
                                                                    <span class="font-medium">{{ ucfirst($key) }}:</span> {{ is_array($value) ? json_encode($value) : $value }}
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-gray-500">-</span>
                                                @endif
                                                
                                                @if($log->metadata)
                                                    @php $metadata = is_array($log->metadata) ? $log->metadata : json_decode($log->metadata, true); @endphp
                                                    @if(isset($metadata['view_type']) && $metadata['view_type'] === 'shared_link')
                                                        <br><span class="text-xs text-blue-500">paylaşım bağlantısı ile</span>
                                                    @elseif(isset($metadata['approval_method']))
                                                        <br><span class="text-xs text-green-500">{{ $metadata['approval_method'] }} ile</span>
                                                    @elseif(isset($metadata['rejection_method']))
                                                        <br><span class="text-xs text-red-500">{{ $metadata['rejection_method'] }} ile</span>
                                                    @elseif(isset($metadata['discovery_id']))
                                                        <br><span class="text-xs text-gray-500">Keşif #{{ $metadata['discovery_id'] }} ile ilişkili</span>                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                                İşlem geçmişi bulunamadı.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if($logs->hasPages())
                        <div class="mt-6">
                            {{ $logs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Cleanup Modal -->
    <div id="cleanupModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">İşlem Geçmişi Temizleme</h3>
                
                <form method="POST" action="{{ route('transaction-logs.cleanup') }}">
                    @csrf
                    <div class="space-y-4">
                        <!-- Cleanup Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Temizleme Türü</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="cleanup_type" value="days" class="mr-2" checked>
                                    <span>Belirli günden eski kayıtları sil</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="cleanup_type" value="action" class="mr-2">
                                    <span>Belirli işlem türündeki eski kayıtları sil</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="cleanup_type" value="all_old" class="mr-2">
                                    <span>15 günden eski tüm kayıtları sil</span>
                                </label>
                            </div>
                        </div>

                        <!-- Days to Keep -->
                        <div id="daysSection">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kaç günlük kayıt tutulsun?</label>
                            <input type="number" name="days_to_keep" value="30" min="1" max="365" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Bu sayıdan eski kayıtlar silinecek</p>
                        </div>

                        <!-- Action Type Section -->
                        <div id="actionSection" class="hidden">
                            <div class="space-y-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">İşlem Türü</label>
                                    <select name="action_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="viewed">Görüntüleme kayıtları</option>
                                        <option value="shared">Paylaşım kayıtları</option>
                                        <option value="updated">Güncelleme kayıtları</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kaç günlük tutulsun?</label>
                                    <input type="number" name="action_days" value="7" min="1" max="365" 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Warning -->
                        <div class="bg-red-50 border border-red-200 rounded-md p-3">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Dikkat!</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <p>Bu işlem geri alınamaz. Silinen kayıtlar kalıcı olarak kaldırılacaktır.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="toggleCleanupModal()" 
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md">
                            İptal
                        </button>
                        <button type="submit" 
                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">
                            Temizle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleCleanupModal() {
            const modal = document.getElementById('cleanupModal');
            modal.classList.toggle('hidden');
        }

        // Handle cleanup type changes
        const cleanupTypeRadios = document.querySelectorAll('input[name="cleanup_type"]');
        const daysSection = document.getElementById('daysSection');
        const actionSection = document.getElementById('actionSection');

        cleanupTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'days') {
                    daysSection.classList.remove('hidden');
                    actionSection.classList.add('hidden');
                } else if (this.value === 'action') {
                    daysSection.classList.add('hidden');
                    actionSection.classList.remove('hidden');
                } else {
                    daysSection.classList.add('hidden');
                    actionSection.classList.add('hidden');
                }
            });
        });

        // Close modal when clicking outside
        document.getElementById('cleanupModal').addEventListener('click', function(e) {
            if (e.target === this) {
                toggleCleanupModal();
            }
        });
    </script>
</body>
</html>
