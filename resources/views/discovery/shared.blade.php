<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keşif Detayları - {{ $discovery->customer_name }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <!-- Success/Error Messages -->
                @if(session('success'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 00-1.414 1.414l2 2a1 1 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('error') }}
                    </div>
                </div>
                @endif

                <!-- Status Display and Action Buttons -->
                <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="mb-4 md:mb-0">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Keşif Durumu</h3>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{
                                $discovery->status === 'pending' ? 'bg-blue-100 text-blue-800' : (
                                    $discovery->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : (
                                        $discovery->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                        'bg-red-100 text-red-800'
                                    )
                                )
                            }}">
                                {{ $discovery->status === 'pending' ? 'Beklemede' : (
                                    $discovery->status === 'in_progress' ? 'Sürmekte' : (
                                        $discovery->status === 'completed' ? 'Tamamlandı' : 'İptal Edildi'
                                    )
                                ) }}
                            </span>
                        </div>

                        @if($discovery->status === 'pending')
                        <div class="flex flex-col sm:flex-row gap-3">
                            <form action="{{ route('discovery.customer-approve', $discovery->share_token) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg transition duration-150 ease-in-out"
                                        onclick="return confirm('Bu keşifi onaylamak istediğinizden emin misiniz? Onayladıktan sonra çalışmalar başlayacaktır.')">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Keşifi Onayla
                                </button>
                            </form>
                            
                            <form action="{{ route('discovery.customer-reject', $discovery->share_token) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-lg transition duration-150 ease-in-out"
                                        onclick="return confirm('Bu keşifi reddetmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Keşifi Reddet
                                </button>
                            </form>
                        </div>
                        @elseif($discovery->status === 'in_progress')
                        <div class="text-sm text-gray-600">
                            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Çalışmalar devam ediyor...
                        </div>
                        @elseif($discovery->status === 'completed')
                        <div class="text-sm text-green-600">
                            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Çalışmalar tamamlandı
                        </div>
                        @else
                        <div class="text-sm text-red-600">
                            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Bu keşif iptal edildi
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <h3 class="text-sm font-medium text-gray-700">Müşteri Adı</h3>
                        <p class="mt-2">{{ $discovery->customer_name }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-700">Telefon Numarası</h3>
                        <p class="mt-2">{{ $discovery->customer_phone }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-700">Email</h3>
                        <p class="mt-2">{{ $discovery->customer_email }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-700">Adres</h3>
                        <p class="mt-2">{{ $discovery->address }}</p>
                    </div>
                </div>

                <!-- Discovery Details -->
                <div class="mb-8">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Keşif Detayı</h3>
                    <div class="bg-gray-50 p-6 rounded-lg min-h-[200px]">
                        {!! nl2br(e($discovery->discovery)) !!}
                    </div>
                </div>

                <!-- Priority Display -->
                <div class="mb-8">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Öncelik Seviyesi</h3>
                    @php
                        $turkishPriorityLabels = [
                            \App\Models\Discovery::PRIORITY_LOW => 'Yok',
                            \App\Models\Discovery::PRIORITY_MEDIUM => 'Var', 
                            \App\Models\Discovery::PRIORITY_HIGH => 'Acil',
                        ];
                        $priorityLabel = $turkishPriorityLabels[$discovery->priority] ?? 'Yok';
                        $priorityBadgeClass = $discovery->priority == \App\Models\Discovery::PRIORITY_HIGH ? 'bg-red-100 text-red-800' : 
                                            ($discovery->priority == \App\Models\Discovery::PRIORITY_MEDIUM ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800');
                    @endphp
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $priorityBadgeClass }}">
                        {{ $priorityLabel }}
                    </div>
                </div>

                <!-- Add Todo List section after Discovery Details -->
                @if($discovery->todo_list)
                <div class="mb-8">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Yapılacaklar Listesi</h3>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        {!! nl2br(e($discovery->todo_list)) !!}
                    </div>
                </div>
                @endif

                <!-- Selected Items List -->
                @if($discovery->items->isNotEmpty())
                <div class="mb-8">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Malzemeler</h3>
                    <div class="bg-gray-50 rounded-lg border border-gray-200">
                        <ul class="divide-y divide-gray-200">
                            @foreach($discovery->items as $item)
                            <li class="p-4">
                                <div class="grid grid-cols-12 gap-4">
                                    <div class="col-span-6">
                                        <p class="font-medium">{{ $item->item }}</p>
                                        <p class="text-sm text-gray-500">{{ $item->brand }}</p>
                                    </div>
                                    <div class="col-span-3">
                                        <p class="text-sm text-gray-600">Miktar: {{ $item->pivot->quantity }}</p>
                                    </div>
                                    <div class="col-span-3 text-right">
                                        <p class="text-sm text-gray-600">
                                            Fiyat: {{ number_format($item->pivot->custom_price ?? $item->price, 2) }} TL
                                        </p>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <!-- Cost Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Masraflar Detayı</h3>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <div class="space-y-3">
                            <!-- Add Items Total at the top -->
                            @if($discovery->items->isNotEmpty())
                            <div class="flex justify-between border-b border-gray-200 pb-3">
                                <span class="text-gray-600">Malzeme Toplamı:</span>
                                <span>{{ number_format($discovery->items->sum(function($item) {
                                    return ($item->pivot->custom_price ?? $item->price) * $item->pivot->quantity;
                                }), 2) }} TL</span>
                            </div>
                            @endif

                            <div class="flex justify-between">
                                <span class="text-gray-600">Servis Masrafı:</span>
                                <span>{{ number_format($discovery->service_cost, 2) }} TL</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Ulaşım Masrafı:</span>
                                <span>{{ number_format($discovery->transportation_cost, 2) }} TL</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">İşçilik Masrafı:</span>
                                <span>{{ number_format($discovery->labor_cost, 2) }} TL</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Ekstra Masraflar:</span>
                                <span>{{ number_format($discovery->extra_fee, 2) }} TL</span>
                            </div>

                            @if($discovery->discount_rate > 0 || $discovery->discount_amount > 0)
                            <div class="flex justify-between text-red-600">
                                <span>İndirim:</span>
                                <span>-{{ number_format($discovery->discount_rate_amount + $discovery->discount_amount, 2) }} TL</span>
                            </div>
                            @endif

                            <!-- Add Payment Method before total -->
                            @if($discovery->payment_method)
                            <div class="flex justify-between border-t border-gray-200 pt-3">
                                <span class="text-gray-600">Ödeme Yöntemi:</span>
                                <span>{{ $discovery->payment_method }}</span>
                            </div>
                            @endif

                            <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-3 mt-3">
                                <span>Toplam:</span>
                                <span>{{ number_format($discovery->total_cost, 2) }} TL</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <h3 class="text-sm font-medium text-gray-700">Tamamlanma Süresi</h3>
                        <p class="mt-2">{{ $discovery->completion_time }} gün</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-700">Teklif Geçerlilik Tarihi</h3>
                        <p class="mt-2">{{ $discovery->offer_valid_until?->format('d.m.Y') }}</p>
                    </div>
                </div>

                <!-- Move Notes section to a better location and include both notes -->
                <div class="mb-8">
                    @if($discovery->note_to_customer)
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Müşteri Notu</h3>
                        <div class="bg-gray-50 p-6 rounded-lg">
                            {!! nl2br(e($discovery->note_to_customer)) !!}
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Images -->
                @if($discovery->images)
                <div class="mb-8">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Fotoğraflar</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($discovery->images as $image)
                        <div>
                            <img src="{{ asset('storage/' . $image) }}"
                                 class="w-full h-40 object-cover rounded-lg shadow-sm">
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
