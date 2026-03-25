<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    {{ __("Welcome back, ") }} **{{ Auth::user()->name }}**! 
                    {{ __("You're logged in as ") }} 
                    <span class="font-bold uppercase text-indigo-600">
                        {{ Auth::user()->role }}
                    </span>
                </div>
            </div>

            @if(Auth::user()->role === 'admin')
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">User Management (Mendoza Academy)</h3>
                    <a href="{{ route('admin.users.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        + Add New User
                    </a>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email/LRN</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4">Board of Trustees</td>
                            <td class="px-6 py-4 text-sm text-gray-500">admin@mendoza.edu.ph</td>
                            <td class="px-6 py-4"><span class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs uppercase">Admin</span></td>
                            <td class="px-6 py-4 text-indigo-600 hover:text-indigo-900 cursor-pointer">Edit</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif

            @if(Auth::user()->role === 'parent')
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                <p class="text-gray-600 italic">Accessing Student Records for LRN: **{{ Auth::user()->lrn }}**...</p>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>