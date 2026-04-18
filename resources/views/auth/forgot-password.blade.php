<x-guest-layout>
    <div class="flex-1 flex flex-col items-center justify-center w-full min-h-screen px-4">
        
        <div class="bg-white p-10 md:p-12 rounded-[2rem] shadow-2xl w-full max-w-lg text-center border-t-8 border-[#9b1c1c]">
            
            <h2 class="text-4xl font-[900] text-[#9b1c1c] uppercase italic tracking-tighter mb-4">
                Password Recovery
            </h2>

            <div class="mb-8 text-[11px] font-bold uppercase tracking-widest text-gray-500 leading-relaxed">
                {{ __('Enter your Student LRN (for Parents) or your registered Email address to receive a password reset link.') }}
            </div>

            <x-auth-session-status class="mb-4 text-green-600 font-bold" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="text-left">
                @csrf

                <div class="mb-8">
                    <x-input-label for="lrn" class="text-gray-700 font-black uppercase text-xs tracking-widest mb-2" :value="__('LRN or Email Address:')" />
                    <x-text-input id="lrn" 
                        class="block w-full border-gray-300 rounded-xl shadow-sm bg-gray-50 py-4" 
                        type="text" name="lrn" :value="old('lrn')" required autofocus />
                    <x-input-error :messages="$errors->get('lrn')" class="mt-2" />
                </div>

                <div class="flex flex-col space-y-6">
                    <button type="submit" class="w-full bg-[#9b1c1c] text-white font-bold py-5 rounded-full shadow-lg hover:bg-red-900 transition-all uppercase text-lg tracking-widest active:scale-95">
                        {{ __('Send Reset Link') }}
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