<nav class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center space-x-8">
                <h1 class="text-xl font-bold">{{ config('app.name', 'Handi') }}</h1>
                <a href="{{ route('dashboard') }}"
                   class="text-gray-700 hover:text-gray-900 font-medium {{ request()->routeIs('dashboard') ? 'text-blue-600' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('discovery') }}"
                   class="text-gray-700 hover:text-gray-900 font-medium {{ request()->routeIs('discovery') ? 'text-blue-600' : '' }}">
                    Discovery
                </a>
                <a href="{{ route('items') }}"
                   class="text-gray-700 hover:text-gray-900 font-medium {{ request()->routeIs('items') ? 'text-blue-600' : '' }}">
                    Items
                </a>
            </div>
            <div class="flex items-center">
                <span class="text-gray-700 mr-4">Welcome</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="bg-red-500 text-gray-700 px-4 py-2 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
