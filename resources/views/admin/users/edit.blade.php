<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit User: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700">Full Name</label>
                        <input type="text" name="name" value="{{ $user->name }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700">Role</label>
                        <select name="role" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="teacher" {{ $user->role == 'teacher' ? 'selected' : '' }}>Teacher</option>
                            <option value="parent" {{ $user->role == 'parent' ? 'selected' : '' }}>Parent</option>
                        </select>
                    </div>

                    <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label class="block font-medium text-sm text-gray-700">Account Status</label>
                        <select name="status" class="w-full border-gray-300 rounded-md shadow-sm mt-1">
                            <option value="active" {{ ($user->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="archived" {{ ($user->status ?? '') == 'archived' ? 'selected' : '' }}>Archived (Hide from Dashboard)</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-2 italic">Note: Archiving will hide this user from the main table but keep their records in the database.</p>
                    </div>

                    <div class="flex items-center justify-end">
                        <a href="{{ route('dashboard') }}" class="mr-4 text-sm text-gray-600 underline hover:text-gray-900">Cancel</a>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 shadow-sm">
                            Update Account
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>