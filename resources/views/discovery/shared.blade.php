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
