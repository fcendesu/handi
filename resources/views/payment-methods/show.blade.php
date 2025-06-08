<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $paymentMethod->name }} - {{ config('app.name', 'Handi') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100">
    <!-- Navigation -->
    <x-navigation />

    <!-- Main Content -->
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <a href="{{ route('payment-methods.index') }}"
                                class="text-blue-600 hover:text-blue-800 mr-4">
                                ← Ödeme Yöntemlerine Dön
                            </a>
                            <h1 class="text-3xl font-bold text-gray-900">{{ $paymentMethod->name }}</h1>
                        </div>
                        <div class="flex space-x-3">
                            @can('update', $paymentMethod)
                                <a href="{{ route('payment-methods.edit', $paymentMethod) }}"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                    Ödeme Yöntemini Düzenle
                                </a>
                            @endcan
                            @can('delete', $paymentMethod)
                                <form action="{{ route('payment-methods.destroy', $paymentMethod) }}" method="POST"
                                    class="inline"
                                    onsubmit="return confirm('Bu ödeme yöntemini silmek istediğinizden emin misiniz?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                        Sil
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Payment Method Details -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Ödeme Yöntemi Detayları</h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">Ödeme yöntemi bilgileri ve kullanım
                                istatistikleri.</p>
                        </div>
                        <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                            <dl class="sm:divide-y sm:divide-gray-200">
                                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Ödeme Yöntemi Adı</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        {{ $paymentMethod->name }}</dd>
                                </div>

                                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Sahibi</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $paymentMethod->isSoloHandymanPaymentMethod() ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $paymentMethod->owner_name }}
                                        </span>
                                    </dd>
                                </div>

                                @if ($paymentMethod->description)
                                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">Açıklama</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                            {{ $paymentMethod->description }}</dd>
                                    </div>
                                @endif

                                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Oluşturulma Tarihi</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        {{ $paymentMethod->created_at->format('d.m.Y H:i') }}
                                    </dd>
                                </div>

                                @if ($paymentMethod->updated_at != $paymentMethod->created_at)
                                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">Son Güncellenme</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                            {{ $paymentMethod->updated_at->format('d.m.Y H:i') }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Usage Statistics -->
                    <div class="mt-8">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Kullanım İstatistikleri</h3>
                        <div class="bg-white shadow overflow-hidden sm:rounded-md">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="bg-blue-50 p-4 rounded-lg">
                                        <div class="text-2xl font-bold text-blue-600">
                                            {{ $paymentMethod->discoveries_count ?? 0 }}</div>
                                        <div class="text-sm text-gray-600">Keşif raporunda kullanım</div>
                                    </div>
                                    <div class="bg-green-50 p-4 rounded-lg">
                                        <div class="text-2xl font-bold text-green-600">
                                            {{ $paymentMethod->created_at->diffForHumans() }}
                                        </div>
                                        <div class="text-sm text-gray-600">Oluşturulduğu tarihten beri</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Related Discoveries -->
                    @if ($paymentMethod->discoveries && $paymentMethod->discoveries->count() > 0)
                        <div class="mt-8">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Bu Ödeme Yöntemini Kullanan
                                Keşif Raporları</h3>
                            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                                <ul class="divide-y divide-gray-200">
                                    @foreach ($paymentMethod->discoveries->take(10) as $discovery)
                                        <li>
                                            <a href="{{ route('discoveries.show', $discovery) }}"
                                                class="block hover:bg-gray-50">
                                                <div class="px-4 py-4 sm:px-6">
                                                    <div class="flex items-center justify-between">
                                                        <div class="text-sm font-medium text-blue-600 truncate">
                                                            {{ $discovery->customer_name }}
                                                        </div>
                                                        <div class="ml-2 flex-shrink-0 flex">
                                                            <span
                                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                                {{ $discovery->created_at->format('d.m.Y') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 sm:flex sm:justify-between">
                                                        <div class="sm:flex">
                                                            <div class="text-sm text-gray-500">
                                                                {{ Str::limit($discovery->discovery, 100) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                                @if ($paymentMethod->discoveries->count() > 10)
                                    <div class="bg-gray-50 px-4 py-3 text-center">
                                        <span class="text-sm text-gray-500">
                                            Ve {{ $paymentMethod->discoveries->count() - 10 }} keşif raporu daha...
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>

</html>
