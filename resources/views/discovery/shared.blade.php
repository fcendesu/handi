<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keşif Detayları - {{ $discovery->customer_name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        function imageViewer() {
            return {
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
                }
            }
        }
    </script>
</head>

<body class="bg-gray-100">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <!-- Success/Error Messages -->
                @if (session('success'))
                    <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 00-1.414 1.414l2 2a1 1 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
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
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $discovery->status === 'pending'
                                    ? 'bg-blue-100 text-blue-800'
                                    : ($discovery->status === 'in_progress'
                                        ? 'bg-yellow-100 text-yellow-800'
                                        : ($discovery->status === 'completed'
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-red-100 text-red-800')) }}">
                                {{ $discovery->status === 'pending'
                                    ? 'Beklemede'
                                    : ($discovery->status === 'in_progress'
                                        ? 'Sürmekte'
                                        : ($discovery->status === 'completed'
                                            ? 'Tamamlandı'
                                            : 'İptal Edildi')) }}
                            </span>
                            
                            @if ($discovery->status === 'pending' && $discovery->offer_valid_until)
                                <div class="mt-2">
                                    @if ($discovery->isOfferExpired())
                                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Teklif Süresi Doldu
                                        </div>
                                    @else
                                        @php
                                            $daysLeft = $discovery->getDaysUntilExpiry();
                                        @endphp
                                        @if ($daysLeft !== null)
                                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                                {{ $daysLeft <= 1 ? 'bg-red-100 text-red-800' : ($daysLeft <= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $daysLeft === 0 ? 'Bugün sona eriyor' : ($daysLeft === 1 ? '1 gün kaldı' : $daysLeft . ' gün kaldı') }}
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if ($discovery->status === 'pending' && !$discovery->isOfferExpired())
                            <div class="flex flex-col sm:flex-row gap-3">
                                <form action="{{ route('discovery.customer-approve', $discovery->share_token) }}"
                                    method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg transition duration-150 ease-in-out"
                                        onclick="return confirm('Bu keşifi onaylamak istediğinizden emin misiniz? Onayladıktan sonra çalışmalar başlayacaktır.')">
                                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Keşifi Onayla
                                    </button>
                                </form>

                                <form action="{{ route('discovery.customer-reject', $discovery->share_token) }}"
                                    method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-lg transition duration-150 ease-in-out"
                                        onclick="return confirm('Bu keşifi reddetmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')">
                                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Keşifi Reddet
                                    </button>
                                </form>
                            </div>
                        @elseif($discovery->status === 'pending' && $discovery->isOfferExpired())
                            <div class="text-sm text-red-600">
                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Teklif süresi {{ $discovery->offer_valid_until->format('d.m.Y') }} tarihinde dolmuştur
                            </div>
                        @elseif($discovery->status === 'in_progress')
                            <div class="text-sm text-gray-600">
                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Çalışmalar devam ediyor...
                            </div>
                        @elseif($discovery->status === 'completed')
                            <div class="text-sm text-green-600">
                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Çalışmalar tamamlandı
                            </div>
                        @else
                            <div class="text-sm text-red-600">
                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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
                        <div class="mt-2 space-y-2">
                            @if($discovery->property_id)
                                <!-- Property Address Display -->
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <div class="text-blue-700">{{ $discovery->property->full_address }}</div>
                                    @if($discovery->property->latitude && $discovery->property->longitude)
                                        <div class="mt-2">
                                            <a href="https://www.google.com/maps?q={{ $discovery->property->latitude }},{{ $discovery->property->longitude }}" 
                                               target="_blank"
                                               class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm font-medium transition duration-200">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                Google Maps'te Görüntüle
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <!-- Manual Address Display -->
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                    @php
                                        $addressParts = array_filter([
                                            $discovery->city,
                                            $discovery->district,
                                            $discovery->neighborhood,
                                            $discovery->address
                                        ]);
                                        $fullAddress = implode(', ', $addressParts);
                                    @endphp
                                    <div class="text-gray-900">{{ $fullAddress ?: 'Adres belirtilmemiş' }}</div>
                                    @if($discovery->latitude && $discovery->longitude)
                                        <div class="mt-2">
                                            <a href="https://www.google.com/maps?q={{ $discovery->latitude }},{{ $discovery->longitude }}" 
                                               target="_blank"
                                               class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm font-medium transition duration-200">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                Google Maps'te Görüntüle
                                            </a>
                                        </div>
                                    @elseif($fullAddress)
                                        <div class="mt-2">
                                            <a href="https://www.google.com/maps/search/{{ urlencode($fullAddress) }}" 
                                               target="_blank"
                                               class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm font-medium transition duration-200">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                Google Maps'te Ara
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Discovery Details -->
                <div class="mb-8">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Keşif Detayı</h3>
                    <div class="bg-gray-50 p-6 rounded-lg min-h-[200px]">
                        {!! nl2br(e($discovery->discovery)) !!}
                    </div>
                </div>

                <!-- Todo List section -->
                @if ($discovery->todo_list)
                    <div class="mb-8">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Yapılacaklar Listesi</h3>
                        <div class="bg-gray-50 p-6 rounded-lg">
                            {!! nl2br(e($discovery->todo_list)) !!}
                        </div>
                    </div>
                @endif

                <!-- Customer Notes -->
                @if ($discovery->note_to_customer)
                    <div class="mb-8">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Müşteri Notu</h3>
                        <div class="bg-gray-50 p-6 rounded-lg">
                            {!! nl2br(e($discovery->note_to_customer)) !!}
                        </div>
                    </div>
                @endif

                <!-- Work Group Display -->
                @if ($discovery->workGroup)
                    <div class="mb-8">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">İş Grubu</h3>
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="flex items-center">
                                <div class="p-3 bg-blue-500 rounded-full">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2m5-8a3 3 0 110-6 3 3 0 010 6zm5 3a4 4 0 00-8 0v3h8v-3z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-medium text-blue-900">{{ $discovery->workGroup->name }}
                                    </h4>
                                    @if ($discovery->workGroup->description)
                                        <p class="text-blue-700 mt-1">{{ $discovery->workGroup->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

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

                <!-- Selected Items List -->
                @if ($discovery->items->isNotEmpty())
                    <div class="mb-8">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Malzemeler</h3>
                        <div class="bg-gray-50 rounded-lg border border-gray-200">
                            <ul class="divide-y divide-gray-200">
                                @foreach ($discovery->items as $item)
                                    <li class="p-4">
                                        <div class="grid grid-cols-12 gap-4">
                                            <div class="col-span-6">
                                                <p class="font-medium">{{ $item->item }}</p>
                                                <p class="text-sm text-gray-500">{{ $item->brand }}</p>
                                            </div>
                                            <div class="col-span-3">
                                                <p class="text-sm text-gray-600">Miktar: {{ $item->pivot->quantity }}
                                                </p>
                                            </div>
                                            <div class="col-span-3 text-right">
                                                <p class="text-sm text-gray-600">
                                                    Fiyat:
                                                    {{ number_format($item->pivot->custom_price ?? $item->price, 2) }}
                                                    TL
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
                            @if ($discovery->items->isNotEmpty())
                                <div class="flex justify-between border-b border-gray-200 pb-3">
                                    <span class="text-gray-600">Toplam Malzeme:</span>
                                    <span>{{ number_format(
                                        $discovery->items->sum(function ($item) {
                                            return ($item->pivot->custom_price ?? $item->price) * $item->pivot->quantity;
                                        }),
                                        2,
                                    ) }}
                                        TL</span>
                                </div>
                            @endif

                            <div class="flex justify-between">
                                <span class="text-gray-600">Hizmet:</span>
                                <span>{{ number_format($discovery->service_cost, 2) }} TL</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Ulaşım:</span>
                                <span>{{ number_format($discovery->transportation_cost, 2) }} TL</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">İşçilik:</span>
                                <span>{{ number_format($discovery->labor_cost, 2) }} TL</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Görünmeyen Masraflar:</span>
                                <span>{{ number_format($discovery->extra_fee, 2) }} TL</span>
                            </div>

                            @if ($discovery->discount_rate > 0 || $discovery->discount_amount > 0)
                                <div class="flex justify-between text-red-600">
                                    <span>İndirim:</span>
                                    <span>-{{ number_format($discovery->discount_rate_amount + $discovery->discount_amount, 2) }}
                                        TL</span>
                                </div>
                            @endif

                            <!-- Add Payment Method before total -->
                            @if ($discovery->payment_method)
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

                <!-- Images -->
                @if ($discovery->images)
                    <div class="mb-8" x-data="imageViewer()" x-init="init()">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Fotoğraflar</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach ($discovery->images as $image)
                                <div class="relative group cursor-pointer" @click="viewImage('{{ asset('storage/' . $image) }}')">
                                    <img src="{{ asset('storage/' . $image) }}"
                                        class="w-full h-40 object-cover rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                                    <!-- Hover overlay -->
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-200 rounded-lg flex items-center justify-center">
                                        <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                        </svg>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Image Modal -->
                        <div x-show="showImageModal" 
                             x-cloak
                             @click.away="closeImageModal()"
                             class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 p-4">
                            <div class="relative max-w-7xl max-h-full">
                                <!-- Close button -->
                                <button @click="closeImageModal()" 
                                        class="absolute -top-4 -right-4 z-10 bg-white rounded-full p-2 shadow-lg hover:bg-gray-100 transition-colors">
                                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                
                                <!-- Image -->
                                <img :src="selectedImage" 
                                     class="max-w-full max-h-full object-contain rounded-lg shadow-2xl"
                                     @click.stop>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>

</html>
