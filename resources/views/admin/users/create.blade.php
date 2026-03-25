<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New User - Mendoza Academy') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Full Name')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" required autofocus />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="role" :value="__('Role')" />
                        <select name="role" id="role" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                            <option value="parent">Parent (Uses LRN)</option>
                            <option value="teacher">Teacher (Uses Email/ID)</option>
                        </select>
                    </div>

                    <div class="mt-4" id="lrn_field">
                        <x-input-label for="lrn" :value="__('Child\'s LRN')" />
                        <x-text-input id="lrn" class="block mt-1 w-full" type="text" name="lrn" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="email" :value="__('Email (Optional)')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="password" :value="__('Temporary Password')" />
                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
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
</x-app-layout>