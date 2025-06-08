<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ödeme Yöntemleri - {{ config('app.name', 'Handi') }}</title>
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
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-3xl font-bold text-gray-900">Ödeme Yöntemleri</h1>
                        @can('create', App\Models\PaymentMethod::class)
                            <a href="{{ route('payment-methods.create') }}"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                Yeni Ödeme Yöntemi Ekle
                            </a>
                        @endcan
                    </div>

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                        @if ($paymentMethods->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Ödeme Yöntemi
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Sahibi
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Oluşturulma Tarihi
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                İşlemler
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($paymentMethods as $paymentMethod)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $paymentMethod->name }}</div>
                                                    @if ($paymentMethod->description)
                                                        <div class="text-sm text-gray-500">
                                                            {{ Str::limit($paymentMethod->description, 50) }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $paymentMethod->owner_name }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $paymentMethod->created_at->format('d.m.Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex space-x-2">
                                                        @can('view', $paymentMethod)
                                                            <a href="{{ route('payment-methods.show', $paymentMethod) }}"
                                                                class="text-blue-600 hover:text-blue-900">Görüntüle</a>
                                                        @endcan
                                                        @can('update', $paymentMethod)
                                                            <a href="{{ route('payment-methods.edit', $paymentMethod) }}"
                                                                class="text-indigo-600 hover:text-indigo-900">Düzenle</a>
                                                            @endcan @can('delete', $paymentMethod)
                                                            <form
                                                                action="{{ route('payment-methods.destroy', $paymentMethod) }}"
                                                                method="POST" class="inline"
                                                                onsubmit="return confirm('Bu ödeme yöntemini silmek istediğinizden emin misiniz?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="text-red-600 hover:text-red-900">
                                                                    Sil
                                                                </button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if ($paymentMethods->hasPages())
                                <div class="px-6 py-4 border-t border-gray-200">
                                    {{ $paymentMethods->links() }}
                                </div>
                            @endif
                        @else
                            <div class="p-6 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                    viewBox="0 0 48 48">
                                    <path
                                        d="M8 14v20c0 4.418 7.163 8 16 8 1.381 0 2.721-.087 4-.252M8 14c0 4.418 7.163 8 16 8s16-3.582 16-8M8 14c0-4.418 7.163-8 16-8s16 3.582 16 8m0 0v14m0-4c0 4.418-7.163 8-16 8S8 28.418 8 24m32 10v6m0 0v6m0-6h6m-6 0h-6"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Ödeme yöntemi bulunamadı</h3>
                                <p class="mt-1 text-sm text-gray-500">Başlamak için yeni bir ödeme yöntemi oluşturun.
                                </p>
                                @can('create', App\Models\PaymentMethod::class)
                                    <div class="mt-6">
                                        <a href="{{ route('payment-methods.create') }}"
                                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Yeni Ödeme Yöntemi Ekle
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
