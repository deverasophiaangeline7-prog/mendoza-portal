<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New User - Mendoza Academy') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Full Name')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                        @error('name')
                            <p class="text-red-500 text-xs mt-1 italic">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <x-input-label for="role" :value="__('Role')" />
                        <select name="role" id="role" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                            <option value="parent" {{ old('role') == 'parent' ? 'selected' : '' }}>Parent</option>
                            <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                            
                        </select>
                    </div>

                    <div class="mt-4">
                        <label id="lrn-label" class="block font-medium text-sm text-gray-700">
                            {{ __('Child\'s LRN') }}
                        </label>
                        <input type="text" 
                               name="lrn" 
                               id="lrn" 
                               value="{{ old('lrn') }}" 
                               class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 @error('lrn') border-red-500 ring-1 ring-red-500 @enderror">
                        
                        @error('lrn')
                            <p class="text-red-500 text-xs mt-1 italic">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <label class="block font-medium text-sm text-gray-700">
                            {{ __('Email (Optional for Parents)') }}
                        </label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email') }}" 
                               class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 @error('email') border-red-500 ring-1 ring-red-500 @enderror">
                        
                        @error('email')
                            <p class="text-red-500 text-xs mt-1 italic">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <x-input-label for="password" :value="__('Temporary Password')" />
                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
                        @error('password')
                            <p class="text-red-500 text-xs mt-1 italic">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button class="ml-4">
                            {{ __('Register User') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const lrnLabel = document.getElementById('lrn-label');

            function updateLabel() {
                if (roleSelect.value === 'teacher') {
                    lrnLabel.innerText = "Teacher's ID / Institutional Email";
                } else if (roleSelect.value === 'parent') {
                    lrnLabel.innerText = "Child's LRN";
                } else {
                    lrnLabel.innerText = "Admin ID";
                }
            }

            roleSelect.addEventListener('change', updateLabel);
            updateLabel(); // Run on load
        });
    </script>
</x-app-layout>