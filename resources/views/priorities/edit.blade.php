<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Öncelik Düzenle</title>
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
                        <h2 class="text-2xl font-semibold text-gray-800">Öncelik Düzenle</h2>
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

                    @if($priority->is_default)
                        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <p class="text-sm text-yellow-800">
                                    Bu varsayılan bir önceliktir. Dikkatli düzenleyin.
                                </p>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('priorities.update', $priority) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Öncelik Adı <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" required
                                       value="{{ old('name', $priority->name) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="örn: Acil, Normal, Düşük">
                            </div>

                            <!-- Level -->
                            <div>
                                <label for="level" class="block text-sm font-medium text-gray-700 mb-2">
                                    Seviye <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="level" id="level" required min="1"
                                       value="{{ old('level', $priority->level) }}"
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
                                       value="{{ old('color', $priority->color) }}"
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
                                      placeholder="Bu önceliğin ne zaman kullanılacağını açıklayın...">{{ old('description', $priority->description) }}</textarea>
                        </div>

                        <!-- Usage Info -->
                        @if($priority->discoveries()->count() > 0)
                            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-sm text-blue-800">
                                        Bu öncelik {{ $priority->discoveries()->count() }} keşifte kullanılıyor.
                                    </p>
                                </div>
                            </div>
                        @endif

                        <!-- Preview -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Önizleme</label>
                            <div class="flex items-center space-x-4">
                                <span id="preview-badge" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-white"
                                      style="background-color: {{ $priority->color }};">
                                    Seviye <span id="preview-level">{{ $priority->level }}</span>
                                </span>
                                <span id="preview-name" class="text-lg font-semibold">{{ $priority->name }}</span>
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
                                Güncelle
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
