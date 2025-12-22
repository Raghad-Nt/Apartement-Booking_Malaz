<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Admin Panel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/admin.css'])
    
    <!-- Styles -->
    <style>
        .sidebar {
            min-height: 100vh;
        }
        .nav-link.active {
            background-color: #e5e7eb;
            border-radius: 0.375rem;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Page Heading -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Admin Panel
                </h2>
                <div class="flex items-center">
                    <span class="mr-4">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</span>
                    <a href="{{ route('logout') }}" 
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       class="text-sm text-gray-700 hover:text-gray-900">
                        Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </header>

        <div class="flex">
            <!-- Sidebar -->
            <aside class="w-64 bg-white shadow sidebar">
                <div class="p-4">
                    <h3 class="text-lg font-semibold mb-4">Navigation</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('admin.dashboard') }}" 
                               class="nav-link block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.users.pending') }}" 
                               class="nav-link block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded {{ request()->routeIs('admin.users.pending') ? 'active' : '' }}">
                                Pending Users
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.users.index') }}" 
                               class="nav-link block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">
                                Manage Users
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 p-6">
                @if(session('success'))
                    <div class="mb-4 px-4 py-3 bg-green-100 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 px-4 py-3 bg-red-100 text-red-700 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>