<x-guest-layout>
    <div class="flex-1 flex flex-col items-center justify-center w-full" x-data="{}">
        
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <div class="flex justify-between text-[11px] font-bold uppercase tracking-[0.2em] text-white px-2 drop-shadow-lg w-full max-w-lg mb-4">
            <span>Are you a parent/teacher?</span>
            <span>Are you a staff admin?</span>
        </div>

        <div class="flex space-x-4 w-full justify-center">
            <button @click="$dispatch('open-modal', 'parent-login')" class="w-48 md:w-64 bg-red-700 text-white font-black py-4 rounded-full shadow-xl hover:bg-red-800 transition-all uppercase text-sm tracking-widest border border-red-900 active:scale-95">
                Log In
            </button>
    
            <button @click="$dispatch('open-modal', 'admin-login')" class="w-48 md:w-64 bg-red-700 text-white font-black py-4 rounded-full shadow-xl hover:bg-red-800 transition-all uppercase text-sm tracking-widest border border-red-900 active:scale-95">
                Admin Log In
            </button>
        </div>

        <x-modal name="parent-login" :show="$errors->has('login_id')" maxWidth="md" focusable>
            <div class="p-8 bg-white rounded-2xl border-t-8 border-red-800 shadow-2xl text-center relative">
                <button @click="$dispatch('close-modal', 'parent-login')" class="absolute top-4 right-4 text-gray-400 hover:text-red-800 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="mb-6">
                    <h2 class="text-3xl font-black text-red-800 uppercase italic tracking-tighter">Parent/Teacher Log In</h2>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mt-1">Child's LRN for parents | Email for Teachers</p>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-5 text-left">
                        <x-input-label for="parent_id" class="text-red-800 font-black uppercase text-[11px] tracking-widest mb-1" :value="__('Child\'s LRN or Email')" />
                        <x-text-input id="parent_id" class="block mt-1 w-full border-gray-300 focus:border-red-700 focus:ring-red-700 rounded-md shadow-sm bg-gray-50" type="text" name="login_id" required autofocus />
                        <x-input-error :messages="$errors->get('login_id')" class="mt-2" />
                    </div>

                    <div class="mb-6 text-left" x-data="{ show: false }">
                        <x-input-label for="parent_pass" class="text-red-800 font-black uppercase text-[11px] tracking-widest mb-1" :value="__('Password')" />
                        <div class="relative">
                            <x-text-input id="parent_pass" 
                                class="block mt-1 w-full border-gray-300 focus:border-red-700 focus:ring-red-700 rounded-md shadow-sm bg-gray-50" 
                                ::type="show ? 'text' : 'password'" 
                                name="password" required />
                            
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <span x-show="!show" class="text-[10px] font-bold uppercase tracking-widest text-gray-400 hover:text-red-800">Show</span>
                                <span x-show="show" class="text-[10px] font-bold uppercase tracking-widest text-red-800" x-cloak>Hide</span>
                            </button>
                        </div>
                    </div>

                    <div class="block mb-6 text-left">
                        <label for="remember_me_parent" class="inline-flex items-center cursor-pointer">
                            <input id="remember_me_parent" type="checkbox" class="rounded border-gray-300 text-red-800 shadow-sm focus:ring-red-800" name="remember">
                            <span class="ml-2 text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-red-800 transition-colors">{{ __('Remember me') }}</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-red-800 text-white font-black py-4 rounded-full shadow-lg hover:bg-red-900 transition-all uppercase text-sm tracking-widest">
                        {{ __('Log In') }}
                    </button>
                </form>

                <div x-data="{ resetSent: false }" class="mt-4">
        <button type="button" @click="resetSent = true" class="text-[10px] font-black uppercase tracking-widest text-red-800 hover:underline">
        {{ __('Forgot Password?') }}
        </button>

        <div x-show="resetSent" x-transition x-cloak class="mt-3 p-3 bg-red-50 border border-red-100 rounded-xl">
        <p class="text-[11px] font-bold text-red-800 uppercase tracking-tighter leading-tight">
            For password reset,<br>please report directly to the campus.
        </p>
        </div>

</div>
            </div>
        </x-modal>

        <x-modal name="admin-login" :show="false" maxWidth="md" focusable>
            <div class="p-8 bg-gray-50 rounded-2xl border-t-8 border-gray-900 shadow-2xl text-center relative">
                <button @click="$dispatch('close-modal', 'admin-login')" class="absolute top-4 right-4 text-gray-400 hover:text-black transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="mb-6">
                    <h2 class="text-3xl font-black text-gray-900 uppercase italic tracking-tighter">Admin Log In</h2>
                    <p class="text-[10px] font-bold text-red-700 uppercase tracking-[0.2em] mt-1">Authorized Personnel Only</p>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <input type="hidden" name="login_type" value="admin">
                    <div class="mb-5 text-left">
                        <x-input-label for="admin_id" class="text-gray-900 font-black uppercase text-[11px] tracking-widest mb-1" :value="__('Username')" />
                        <x-text-input id="admin_id" class="block mt-1 w-full border-gray-400 focus:border-black focus:ring-black rounded-md shadow-sm bg-white" type="text" name="login_id" required />
                    </div>

                    <div class="mb-6 text-left" x-data="{ show: false }">
                        <x-input-label for="admin_pass" class="text-gray-900 font-black uppercase text-[11px] tracking-widest mb-1" :value="__('Password')" />
                        <div class="relative">
                            <x-text-input id="admin_pass" 
                                class="block mt-1 w-full border-gray-400 focus:border-black focus:ring-black rounded-md shadow-sm bg-white" 
                                ::type="show ? 'text' : 'password'" 
                                name="password" required />
                            
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <span x-show="!show" class="text-[10px] font-bold uppercase tracking-widest text-gray-400 hover:text-black">Show</span>
                                <span x-show="show" class="text-[10px] font-bold uppercase tracking-widest text-black" x-cloak>Hide</span>
                            </button>
                        </div>
                    </div>

                    <div class="block mb-6 text-left">
                        <label for="remember_me_admin" class="inline-flex items-center cursor-pointer">
                            <input id="remember_me_admin" type="checkbox" class="rounded border-gray-400 text-gray-900 shadow-sm focus:ring-black" name="remember">
                            <span class="ml-2 text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-black transition-colors">{{ __('Remember me') }}</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-gray-900 text-white font-black py-4 rounded-full shadow-lg hover:bg-black transition-all uppercase text-sm tracking-widest">
                        {{ __('Log In') }}
                    </button>
                </form>

                <div x-data="{ resetSent: false }" class="mt-4">
                    <button type="button" @click="resetSent = true" class="text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-black hover:underline">
        {{ __('Forgot Password?') }}
                    </button>

                <div x-show="resetSent" x-transition x-cloak class="mt-3 p-3 bg-white border border-gray-200 rounded-xl shadow-sm">
                    <p class="text-[11px] font-bold text-gray-900 uppercase tracking-tighter leading-tight">
            Log In confirmation has been sent<br>to your school e-mail address.
                    </p>
        </div>
        </div>

            </div>
        </x-modal>

    </div>
</x-guest-layout>