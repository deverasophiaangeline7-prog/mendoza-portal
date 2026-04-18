<x-guest-layout>
    <div class="flex-1 flex flex-col items-center justify-center w-full min-h-screen px-4">
        
        <div class="bg-white p-10 md:p-12 rounded-[2rem] shadow-2xl w-full max-w-lg text-center border-t-8 border-[#9b1c1c]">
            
            <h2 class="text-4xl font-[900] text-[#9b1c1c] uppercase italic tracking-tighter mb-4">
                Update Password
            </h2>

            <div class="mb-8 text-[11px] font-bold uppercase tracking-widest text-gray-500 leading-relaxed">
                {{ __('Almost there! Please enter your email and choose a secure new password for your account.') }}
            </div>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 text-xs font-bold uppercase tracking-widest">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.store') }}" class="text-left">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="mb-6">
                    <x-input-label for="email" class="text-gray-700 font-black uppercase text-xs tracking-widest mb-2" :value="__('Registered Email:')" />
                    <x-text-input id="email" 
                        class="block w-full border-gray-300 rounded-xl shadow-sm bg-gray-50 py-4" 
                        type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="mb-6">
                    <x-input-label for="password" class="text-gray-700 font-black uppercase text-xs tracking-widest mb-2" :value="__('New Password:')" />
                    <x-text-input id="password" 
                        class="block w-full border-gray-300 rounded-xl shadow-sm bg-gray-50 py-4" 
                        type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="mb-8">
                    <x-input-label for="password_confirmation" class="text-gray-700 font-black uppercase text-xs tracking-widest mb-2" :value="__('Confirm New Password:')" />
                    <x-text-input id="password_confirmation" 
                        class="block w-full border-gray-300 rounded-xl shadow-sm bg-gray-50 py-4" 
                        type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex flex-col space-y-6">
                    <button type="submit" class="w-full bg-[#9b1c1c] text-white font-bold py-5 rounded-full shadow-lg hover:bg-red-900 transition-all uppercase text-lg tracking-widest active:scale-95">
                        {{ __('Update Password') }}
                    </button>
                    
                    <a href="{{ route('login') }}" class="text-xs font-black uppercase tracking-widest text-gray-400 hover:text-[#9b1c1c] text-center transition-colors">
                        &larr; Back to Login
                    </a>
                </div>
            </form>
        </div>

        <p class="mt-10 text-[10px] font-black uppercase text-white drop-shadow-md tracking-[0.3em]">
            &copy; 2026 Mendoza Academy, Inc.
        </p>
    </div>
</x-guest-layout>