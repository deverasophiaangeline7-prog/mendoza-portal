@php
    $primaryColor = $siteSettings['primary_color'] ?? '#9b1c1c';
    $schoolName = $siteSettings['school_name'] ?? 'Mendoza Academy, Inc.';
    $logoPath = $siteSettings['logo_path'] ?? 'images/MAILogo.png';
    $faviconPath = $siteSettings['favicon_path'] ?? 'images/MAILogo.png';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Log In | {{ $schoolName }}</title>

    <link rel="icon" type="image/png" href="{{ asset($faviconPath) }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-screen bg-cover bg-center relative flex flex-col items-center justify-center px-4 py-10"
      style="background-image: url('{{ asset('images/MAIDinosaur.png') }}');">

    {{-- Soft Overlay matching Parent/Teacher Login --}}
    <div class="absolute inset-0 bg-black/45 backdrop-blur-[2px]"></div>

    <div class="relative z-10 w-full max-w-md flex flex-col items-center"
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

        {{-- Main White Card matching Parent/Teacher Login UI --}}
        <div class="w-full bg-white p-8 md:p-10 rounded-[2rem] shadow-2xl text-center border-t-8"
             style="border-top-color: {{ $primaryColor }};">

            {{-- School Logo & Header --}}
            <div class="flex flex-col items-center mb-6">
                <img src="{{ asset($logoPath) }}" alt="Logo" class="h-14 w-14 object-contain mb-3 drop-shadow-sm">
                <span class="inline-flex items-center gap-1.5 bg-red-50 text-red-800 border border-red-200 text-[10px] font-black uppercase tracking-[0.2em] px-3 py-1 rounded-full mb-3">
                    <i class="fa-solid fa-shield-halved"></i> Restricted Access
                </span>
                <h1 class="text-3xl font-black uppercase italic tracking-tighter leading-none" style="color: {{ $primaryColor }};">
                    Admin Log In
                </h1>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mt-2">
                    Authorized Personnel Only
                </p>
            </div>

            {{-- Success Status --}}
            @if (session('status'))
                <div class="mb-5 p-3.5 bg-green-50 border border-green-300 text-green-700 rounded-xl text-xs font-bold text-center">
                    <i class="fa-solid fa-circle-check mr-1"></i> {{ session('status') }}
                </div>
            @endif

            {{-- LIVE PROGRESSIVE LOCKOUT WARNING BANNER --}}
            <div x-show="lockout > 0" x-cloak
                 class="mb-6 p-4 bg-red-50 border-2 border-red-400 text-red-800 rounded-2xl text-left flex items-start gap-3 shadow-sm">
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

            <form method="POST" action="{{ route('admin.login.store') }}" class="text-left">
                @csrf

                <div class="mb-5">
                    <label for="admin_id" class="block font-black uppercase text-[11px] tracking-widest mb-1.5" style="color: {{ $primaryColor }};">
                        Admin Email / Username
                    </label>
                    <input id="admin_id" type="text" name="login_id" value="{{ old('login_id') }}"
                           required autofocus autocomplete="username"
                           :disabled="lockout > 0"
                           placeholder="admin@gmail.com"
                           class="block w-full px-4 py-3.5 border border-gray-300 focus:border-red-700 focus:ring-2 focus:ring-red-700/20 rounded-xl shadow-sm bg-gray-50 focus:bg-white focus:outline-none text-sm font-semibold text-gray-800 disabled:opacity-60 disabled:cursor-not-allowed transition">
                    @error('login_id')
                        <p x-show="lockout === 0" class="text-red-600 text-xs font-bold mt-2 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="mb-5" x-data="{ show: false }">
                    <label for="admin_pass" class="block font-black uppercase text-[11px] tracking-widest mb-1.5" style="color: {{ $primaryColor }};">
                        Password
                    </label>
                    <div class="relative flex items-center">
                        <input id="admin_pass" :type="show ? 'text' : 'password'" name="password"
                               required autocomplete="current-password"
                               :disabled="lockout > 0"
                               placeholder="••••••••"
                               class="block w-full px-4 py-3.5 pr-11 border border-gray-300 focus:border-red-700 focus:ring-2 focus:ring-red-700/20 rounded-xl shadow-sm bg-gray-50 focus:bg-white focus:outline-none text-sm font-semibold text-gray-800 disabled:opacity-60 disabled:cursor-not-allowed transition">
                        <button type="button" @click="show = !show" class="absolute right-3.5 text-gray-400 hover:text-red-800 focus:outline-none transition-colors">
                            <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-600 text-xs font-bold mt-2 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label for="remember_admin" class="inline-flex items-center cursor-pointer">
                        <input id="remember_admin" type="checkbox" name="remember" class="rounded border-gray-300 text-red-800 shadow-sm focus:ring-red-800">
                        <span class="ml-2 text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-red-800 transition-colors">Remember me</span>
                    </label>

                    <a href="{{ route('admin.password.request') }}" class="text-[11px] font-black uppercase tracking-widest hover:underline" style="color: {{ $primaryColor }};">
                        Forgot Password?
                    </a>
                </div>

                <button type="submit"
                        :disabled="lockout > 0"
                        style="background-color: {{ $primaryColor }};"
                        class="w-full text-white font-black py-4 rounded-full shadow-lg hover:brightness-90 transition-all uppercase text-sm tracking-widest active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100">
                    <span x-show="lockout === 0">Log In</span>
                    <span x-show="lockout > 0" x-cloak>Locked (<span x-text="formatTime(lockout)"></span>)</span>
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-gray-100 text-center">
                <a href="{{ url('/') }}" class="text-xs font-black uppercase tracking-widest text-gray-400 hover:text-red-800 transition-colors">
                    &larr; Return to School Website
                </a>
            </div>
        </div>

        <p class="text-white/90 text-[10px] mt-8 uppercase font-bold tracking-[0.2em] text-center drop-shadow-md">
            &copy; 2026 {{ $schoolName }} | Student Monitoring System
        </p>
    </div>
</body>
</html>