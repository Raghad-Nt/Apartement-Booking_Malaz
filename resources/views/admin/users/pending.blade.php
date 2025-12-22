@extends('admin.layout')

@section('content')
<div class="container mx-auto">
    <h1 class="text-2xl font-bold mb-6">Pending Users</h1>
    
    <div class="mb-4">
        <a href="{{ route('admin.dashboard') }}" class="btn-admin-primary">
            ← Back to Dashboard
        </a>
    </div>
    
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mobile</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $user->first_name }} {{ $user->last_name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($user->role) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->mobile }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <form method="POST" action="{{ route('admin.users.approve', $user) }}" class="inline-block">
                            @csrf
                            <button type="submit" 
                                    class="btn-admin-success"
                                    onclick="return confirm('Are you sure you want to approve this user?')">
                                Approve
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.reject', $user) }}" class="inline-block">
                            @csrf
                            <button type="submit" 
                                    class="btn-admin-danger"
                                    onclick="return confirm('Are you sure you want to reject this user? This will delete the user account.')">
                                Reject
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No pending users found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
    <div class="mt-6">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection