@extends('admin.layout')

@section('content')
<div class="container mx-auto">
    <h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Users Card -->
        <div class="bg-white rounded-lg shadow p-6 admin-card stats-card blue">
            <div class="flex items-center">
                <div class="rounded-full bg-blue-100 p-3 mr-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Total Users</p>
                    <p class="text-2xl font-bold">{{ $stats['total_users'] }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Users Card -->
        <div class="bg-white rounded-lg shadow p-6 admin-card stats-card yellow">
            <div class="flex items-center">
                <div class="rounded-full bg-yellow-100 p-3 mr-4">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Pending Users</p>
                    <p class="text-2xl font-bold">{{ $stats['pending_users'] }}</p>
                </div>
            </div>
        </div>

        <!-- Active Users Card -->
        <div class="bg-white rounded-lg shadow p-6 admin-card stats-card green">
            <div class="flex items-center">
                <div class="rounded-full bg-green-100 p-3 mr-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Active Users</p>
                    <p class="text-2xl font-bold">{{ $stats['active_users'] }}</p>
                </div>
            </div>
        </div>

        <!-- Total Revenue Card -->
        <div class="bg-white rounded-lg shadow p-6 admin-card stats-card purple">
            <div class="flex items-center">
                <div class="rounded-full bg-purple-100 p-3 mr-4">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Total Revenue</p>
                    <p class="text-2xl font-bold">${{ number_format($stats['total_revenue'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Apartments Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Apartments</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span>Total Apartments:</span>
                    <span class="font-medium">{{ $stats['total_apartments'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Available Apartments:</span>
                    <span class="font-medium">{{ $stats['available_apartments'] }}</span>
                </div>
            </div>
        </div>

        <!-- Bookings Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Bookings</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span>Total Bookings:</span>
                    <span class="font-medium">{{ $stats['total_bookings'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Pending Bookings:</span>
                    <span class="font-medium">{{ $stats['pending_bookings'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Confirmed Bookings:</span>
                    <span class="font-medium">{{ $stats['confirmed_bookings'] }}</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
            <div class="space-y-3">
                <a href="{{ route('admin.users.pending') }}" class="block w-full text-center py-2 px-4 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                    Review Pending Users
                </a>
                <a href="{{ route('admin.users.index') }}" class="block w-full text-center py-2 px-4 bg-green-600 text-white rounded hover:bg-green-700 transition">
                    Manage Users
                </a>
            </div>
        </div>
    </div>
</div>
@endsection