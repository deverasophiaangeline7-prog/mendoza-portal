@php
    $loginHeading = \App\Models\SiteContent::where('page', 'login')->where('key', 'heading')->value('value') ?? 'STUDENT MONITORING<br><span class="text-white">SYSTEM</span>';
@endphp

<x-guest-layout>
    <div class="relative flex-1 bg-cover bg-center flex flex-col items-center justify-center overflow-hidden w-full py-12" 
         style="background-image: url('{{ asset('images/MAIDinosaur.png') }}');">
        
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>

        <div class="relative z-10 w-full flex flex-col items-center px-4 justify-center h-full">
            
            <div class="text-center mb-8">
                <h2 class="text-white text-4xl md:text-7xl font-black uppercase leading-none drop-shadow-2xl italic tracking-tighter">
                    {!! $loginHeading !!}
                </h2>
            </div>

            <div class="flex flex-col items-center justify-center w-full" x-data="{}">
                
                @if (session('status'))
                    <div class="mb-8 flex justify-center w-full max-w-lg">
                        <div class="bg-green-600 px-10 py-5 rounded-[2rem] border-[6px] border-white shadow-2xl text-center">
                            <span class="text-white font-black uppercase text-[12px] tracking-[0.3em] drop-shadow-lg">
                                {{ session('status') }}
                            </span>
                        </div>
                    </div>
                @endif

                <div class="text-center text-[11px] font-bold uppercase tracking-[0.2em] text-white px-2 drop-shadow-lg mb-4">
                    <span>Authorized Personnel Only</span>
                </div>

                <div class="flex w-full justify-center">
                    <button @click="$dispatch('open-modal', 'admin-login')" class="w-64 bg-red-700 text-white font-black py-4 rounded-full shadow-xl hover:bg-red-800 transition-all uppercase text-sm tracking-widest border border-red-900 active:scale-95">
                        Admin Log In
                    </button>
                </div>

                    <x-modal name="admin-login" :show="true" maxWidth="md" focusable>                    <div class="p-8 bg-white rounded-2xl border-t-8 border-red-800 shadow-2xl text-center relative"
                         x-data="{
                             lockout: {{ (int) ($lockoutSeconds ?? 0) }},
                             stage: {{ (int) ($lockoutStage ?? 0) }},
                             init() {
                                 if (this.lockout > 0) {
                                     const timer = setInterval(() => {
                                         if (this.lockout > 0) {
                                             this.lockout--;
                                         } else {
                                             clearInterval(timer);
                                         }
                                     }, 1000);
                                 }
                             },
                             formatTime(sec) {
                                 const m = Math.floor(sec / 60);
                                 const s = sec % 60;
                                 return `${m}:${String(s).padStart(2, '0')}`;
                             }
                         }">
                        <button @click="$dispatch('close-modal', 'admin-login')" class="absolute top-4 right-4 text-gray-400 hover:text-red-800 transition-colors">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <div class="mb-6">
                            <h2 class="text-3xl font-black text-red-800 uppercase italic tracking-tighter">Admin Log In</h2>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mt-1">Authorized Personnel Only</p>
                        </div>

                        {{-- LIVE PROGRESSIVE LOCKOUT WARNING BANNER --}}
                        <div x-show="lockout > 0" x-cloak
                             class="mb-5 p-4 bg-red-50 border-2 border-red-400 text-red-800 rounded-2xl text-left flex items-start gap-3 shadow-sm">
                            <i class="fa-solid fa-lock text-red-600 text-lg mt-0.5"></i>
                            <div class="text-xs leading-relaxed">
                                <p class="font-black uppercase tracking-wider text-red-700"
                                   x-text="stage >= 2 ? 'Extended Security Lockout (10 Mins)' : 'Temporary Lockout (3 Mins)'"></p>
                                <p class="font-semibold text-red-700/90 mt-0.5">
                                    Too many failed login attempts. Please try again in
                                    <span class="font-black underline tabular-nums" x-text="formatTime(lockout)"></span>.
                                </p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.login.store') }}">
                            @csrf
                            
                            <div class="mb-5 text-left">
                                <x-input-label for="admin_id" class="text-red-800 font-black uppercase text-[11px] tracking-widest mb-1" :value="__('Admin Email or Username')" />
                                <x-text-input id="admin_id"
                                    class="block mt-1 w-full border-gray-300 focus:border-red-700 focus:ring-red-700 rounded-md shadow-sm bg-gray-50 disabled:opacity-60 disabled:cursor-not-allowed"
                                    type="text" name="login_id" :value="old('login_id')"
                                    x-bind:disabled="lockout > 0"
                                    required autofocus autocomplete="username" />
                                <div x-show="lockout === 0">
                                    <x-input-error :messages="$errors->get('login_id')" class="mt-2" />
                                </div>
                            </div>

                            <div class="mb-6 text-left" x-data="{ show: false }">
                                <x-input-label for="admin_pass" class="text-red-800 font-black uppercase text-[11px] tracking-widest mb-1" :value="__('Password')" />
                                <div class="relative flex items-center">
                                    <x-text-input id="admin_pass" 
                                        class="block mt-1 w-full border-gray-300 focus:border-red-700 focus:ring-red-700 rounded-md shadow-sm bg-gray-50 pr-10 disabled:opacity-60 disabled:cursor-not-allowed" 
                                        ::type="show ? 'text' : 'password'" 
                                        x-bind:disabled="lockout > 0"
                                        name="password" required autocomplete="current-password" />
                                    
                                    <button type="button" @click="show = !show" class="absolute right-3 mt-1 text-gray-400 hover:text-red-800 focus:outline-none transition-colors">
                                        <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <div class="block mb-6 text-left">
                                <label for="remember_me_admin" class="inline-flex items-center cursor-pointer">
                                    <input id="remember_me_admin" type="checkbox" class="rounded border-gray-300 text-red-800 shadow-sm focus:ring-red-800" name="remember">
                                    <span class="ml-2 text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-red-800 transition-colors">{{ __('Remember me') }}</span>
                                </label>
                            </div>

                            <button type="submit"
                                    x-bind:disabled="lockout > 0"
                                    class="w-full bg-red-800 text-white font-black py-4 rounded-full shadow-lg hover:bg-red-900 transition-all uppercase text-sm tracking-widest disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="lockout === 0">{{ __('Log In') }}</span>
                                <span x-show="lockout > 0" x-cloak>Locked (<span x-text="formatTime(lockout)"></span>)</span>
                            </button>
                        </form>

                        <div class="mt-4">
                            <a href="{{ route('admin.password.request') }}" class="text-sm font-black uppercase tracking-widest text-[#9b1c1c] hover:underline">
                                {{ __('Forgot Password?') }}
                            </a>
                        </div>
                    </div>
                </x-modal>
            </div>

            <p class="text-white/80 text-[10px] mt-12 uppercase font-bold tracking-[0.2em] text-center">
                &copy; 2026 {{ $siteSettings['school_name'] ?? 'Mendoza Academy, Inc.' }} | Student Monitoring System
            </p>
        </div>
    </div>
</x-guest-layout>