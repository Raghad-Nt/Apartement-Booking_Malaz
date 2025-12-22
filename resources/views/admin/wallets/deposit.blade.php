@extends('admin.layout')

@section('content')
<div class="container mx-auto">
    <h1 class="text-2xl font-bold mb-6">Deposit to User Wallet</h1>
    
    <div class="bg-white rounded-lg shadow p-6 max-w-md">
        <div class="mb-6">
            <h2 class="text-lg font-semibold">User Information</h2>
            <p><strong>Name:</strong> {{ $user->first_name }} {{ $user->last_name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Role:</strong> {{ ucfirst($user->role) }}</p>
            
            @php
                $wallet = $user->wallet;
                $balance = $wallet ? $wallet->balance : 0;
            @endphp
            <p><strong>Current Balance:</strong> ${{ number_format($balance, 2) }}</p>
        </div>
        
        <form method="POST" action="{{ route('admin.wallet.deposit', $user) }}">
            @csrf
            <div class="mb-4">
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Deposit Amount</label>
                <input type="number" 
                       name="amount" 
                       id="amount" 
                       step="0.01" 
                       min="0.01" 
                       required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                @error('amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="flex space-x-3">
                <button type="submit" class="btn-admin-success">
                    Process Deposit
                </button>
                <a href="{{ route('admin.dashboard') }}" class="btn-admin-primary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection