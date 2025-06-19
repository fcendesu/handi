<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - {{ config('app.name', 'Handi') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-8">
        <div class="max-w-2xl w-full bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900">Join Handi</h2>
                <p class="text-gray-600 mt-2">Create your account and start managing handyman services</p>
            </div>

            <form method="POST" action="{{ route('register') }}" id="registrationForm">
                @csrf

                <!-- User Type Selection -->
                <div class="mb-8">
                    <label class="block text-gray-700 text-sm font-bold mb-4">What best describes you?</label>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="border rounded-lg p-6 cursor-pointer hover:border-blue-500 transition-colors user-type-card" 
                             data-type="solo_handyman">
                            <input type="radio" name="user_type" id="solo_handyman" value="solo_handyman" 
                                class="sr-only" {{ old('user_type') === 'solo_handyman' ? 'checked' : '' }} required>
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 text-lg">Solo Handyman</h3>
                                    <p class="text-sm text-gray-600 mt-1">Independent contractor working alone</p>
                                    <p class="text-xs text-gray-500 mt-2">Perfect for individual craftsmen and contractors</p>
                                </div>
                            </div>
                        </div>
                        <div class="border rounded-lg p-6 cursor-pointer hover:border-green-500 transition-colors user-type-card" 
                             data-type="company_admin">
                            <input type="radio" name="user_type" id="company_admin" value="company_admin" 
                                class="sr-only" {{ old('user_type') === 'company_admin' ? 'checked' : '' }}>
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 text-lg">Company Owner</h3>
                                    <p class="text-sm text-gray-600 mt-1">Creating or managing a handyman company</p>
                                    <p class="text-xs text-gray-500 mt-2">Manage teams, projects, and company operations</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @error('user_type')
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>



                <!-- Personal Information -->
                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Full Name</label>
                        <input type="text" name="name" id="name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            value="{{ old('name') }}" required autofocus>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                        <input type="email" name="email" id="email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            value="{{ old('email') }}" required>
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                        <input type="password" name="password" id="password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                    </div>
                </div>



                <!-- Company Admin Fields -->
                <div id="admin_company_section" class="mb-6 hidden">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                        <h3 class="font-semibold text-green-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Company Information
                        </h3>
                        <div class="space-y-4">
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="admin_company_name" class="block text-gray-700 text-sm font-bold mb-2">
                                        Company Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="admin_company_name" id="admin_company_name"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        value="{{ old('admin_company_name') }}">
                                    @error('admin_company_name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="admin_company_phone" class="block text-gray-700 text-sm font-bold mb-2">
                                        Business Phone <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" name="admin_company_phone" id="admin_company_phone"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        value="{{ old('admin_company_phone') }}">
                                    @error('admin_company_phone')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div>
                                <label for="admin_company_address" class="block text-gray-700 text-sm font-bold mb-2">
                                    Business Address <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="admin_company_address" id="admin_company_address"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    value="{{ old('admin_company_address') }}">
                                @error('admin_company_address')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="admin_company_email" class="block text-gray-700 text-sm font-bold mb-2">
                                    Business Email (Optional)
                                </label>
                                <input type="email" name="admin_company_email" id="admin_company_email"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    value="{{ old('admin_company_email') }}"
                                    placeholder="Defaults to your personal email">
                                @error('admin_company_email')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    Create Account
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-medium">Sign in here</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userTypeCards = document.querySelectorAll('.user-type-card');
            const adminSection = document.getElementById('admin_company_section');

            // Handle user type card selection
            userTypeCards.forEach(card => {
                card.addEventListener('click', function() {
                    const radio = card.querySelector('input[type="radio"]');
                    radio.checked = true;
                    
                    // Update visual selection
                    userTypeCards.forEach(c => {
                        c.classList.remove('border-blue-500', 'border-green-500', 'bg-blue-50', 'bg-green-50');
                        c.classList.add('border-gray-300');
                    });
                    
                    // Apply type-specific colors
                    const type = card.dataset.type;
                    card.classList.remove('border-gray-300');
                    if (type === 'solo_handyman') {
                        card.classList.add('border-blue-500', 'bg-blue-50');
                    } else if (type === 'company_admin') {
                        card.classList.add('border-green-500', 'bg-green-50');
                    }
                    
                    toggleSections();
                });
            });

            function toggleSections() {
                const selectedType = document.querySelector('input[name="user_type"]:checked')?.value;
                
                // Hide all sections first
                adminSection?.classList.add('hidden');

                // Show relevant section
                if (selectedType === 'company_admin') {
                    adminSection?.classList.remove('hidden');
                }
            }

            // Initial setup
            const checkedCard = document.querySelector('input[name="user_type"]:checked')?.closest('.user-type-card');
            if (checkedCard) {
                const type = checkedCard.dataset.type;
                checkedCard.classList.remove('border-gray-300');
                if (type === 'solo_handyman') {
                    checkedCard.classList.add('border-blue-500', 'bg-blue-50');
                } else if (type === 'company_admin') {
                    checkedCard.classList.add('border-green-500', 'bg-green-50');
                }
            }
            
            toggleSections();
        });
    </script>
</body>
</html>
