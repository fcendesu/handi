<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Yeni Öncelik Oluştur</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <x-navigation />

    <!-- Main Content -->
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">Yeni Öncelik Oluştur</h2>
                        <a href="{{ route('priorities.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Geri Dön
                        </a>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('priorities.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Öncelik Adı <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" required
                                       value="{{ old('name') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="örn: Acil, Normal, Düşük">
                            </div>

                            <!-- Level -->
                            <div>
                                <label for="level" class="block text-sm font-medium text-gray-700 mb-2">
                                    Seviye <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="level" id="level" required min="1"
                                       value="{{ old('level', 1) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="1">
                                <p class="text-xs text-gray-500 mt-1">Yüksek sayı = Yüksek öncelik</p>
                            </div>
                        </div>

                        <!-- Color -->
                        <div class="mt-6">
                            <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                                Rozet Rengi <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center space-x-4">
                                <input type="color" name="color" id="color" required
                                       value="{{ old('color', '#6B7280') }}"
                                       class="h-10 w-20 border border-gray-300 rounded-md cursor-pointer">
                                <div class="flex space-x-2">
                                    <button type="button" onclick="setColor('#EF4444')" class="w-8 h-8 bg-red-500 rounded-full border-2 border-gray-300 hover:border-gray-500"></button>
                                    <button type="button" onclick="setColor('#F59E0B')" class="w-8 h-8 bg-yellow-500 rounded-full border-2 border-gray-300 hover:border-gray-500"></button>
                                    <button type="button" onclick="setColor('#10B981')" class="w-8 h-8 bg-green-500 rounded-full border-2 border-gray-300 hover:border-gray-500"></button>
                                    <button type="button" onclick="setColor('#3B82F6')" class="w-8 h-8 bg-blue-500 rounded-full border-2 border-gray-300 hover:border-gray-500"></button>
                                    <button type="button" onclick="setColor('#8B5CF6')" class="w-8 h-8 bg-purple-500 rounded-full border-2 border-gray-300 hover:border-gray-500"></button>
                                    <button type="button" onclick="setColor('#6B7280')" class="w-8 h-8 bg-gray-500 rounded-full border-2 border-gray-300 hover:border-gray-500"></button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Keşiflerde görünecek rozet rengini seçin</p>
                        </div>

                        <!-- Description -->
                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Açıklama
                            </label>
                            <textarea name="description" id="description" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="Bu önceliğin ne zaman kullanılacağını açıklayın...">{{ old('description') }}</textarea>
                        </div>

                        <!-- Preview -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Önizleme</label>
                            <div class="flex items-center space-x-4">
                                <span id="preview-badge" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-white"
                                      style="background-color: #6B7280;">
                                    Seviye <span id="preview-level">1</span>
                                </span>
                                <span id="preview-name" class="text-lg font-semibold">Öncelik Adı</span>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="mt-8 flex justify-end space-x-4">
                            <a href="{{ route('priorities.index') }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded">
                                İptal
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                                Oluştur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setColor(color) {
            document.getElementById('color').value = color;
            updatePreview();
        }

        function updatePreview() {
            const name = document.getElementById('name').value || 'Öncelik Adı';
            const level = document.getElementById('level').value || '1';
            const color = document.getElementById('color').value || '#6B7280';

            document.getElementById('preview-name').textContent = name;
            document.getElementById('preview-level').textContent = level;
            document.getElementById('preview-badge').style.backgroundColor = color;
        }

        // Update preview on input changes
        document.getElementById('name').addEventListener('input', updatePreview);
        document.getElementById('level').addEventListener('input', updatePreview);
        document.getElementById('color').addEventListener('input', updatePreview);

        // Initial preview update
        updatePreview();
    </script>
</body>
</html>
