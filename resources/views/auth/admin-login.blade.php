<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Log In | {{ $siteSettings['school_name'] ?? config('app.name', 'Mendoza Academy') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset($siteSettings['favicon_path'] ?? 'images/MAILogo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-cover bg-center relative flex flex-col items-center justify-center px-4 py-12"
      style="background-image: url('{{ asset('images/MAIDinosaur.png') }}');">

    <div class="absolute inset-0 bg-black bg-opacity-40"></div>

    <div class="relative z-10 w-full max-w-md flex flex-col items-center">
        
        {{-- Direct Login Card (Exact Match of Parent/Teacher Card UI) --}}
        <div class="w-full p-8 bg-white rounded-2xl border-t-8 border-red-800 shadow-2xl text-center relative"
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

            <div class="mb-6">
                <h2 class="text-3xl font-black text-red-800 uppercase italic tracking-tighter">Admin Log In</h2>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mt-1">Authorized Personnel Only</p>
            </div>

            @if (session('status'))
                <div class="mb-5 p-3 bg-green-50 border border-green-300 text-green-700 rounded-xl text-xs font-bold text-center">
                    {{ session('status') }}
                </div>
            @endif

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
                    <x-input-label for="admin_id" class="text-red-800 font-black uppercase text-[11px] tracking-widest mb-1" :value="__('Username or Email')" />
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

        <p class="text-white/80 text-[10px] mt-8 uppercase font-bold tracking-[0.2em] text-center drop-shadow">
            &copy; 2026 {{ $siteSettings['school_name'] ?? 'Mendoza Academy, Inc.' }} | Student Monitoring System
        </p>
    </div>

</body>
</html>