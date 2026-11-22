<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Portal | {{ $siteSettings['school_name'] ?? 'Mendoza Academy, Inc.' }}</title>

    <link rel="icon" type="image/png" href="{{ asset($siteSettings['favicon_path'] ?? 'images/MAILogo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-neutral-900 to-[#3b0000] flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-md bg-white rounded-3xl border-[4px] border-black shadow-[10px_10px_0px_0px_rgba(0,0,0,0.9)] overflow-hidden">
        
        {{-- Top Security Header --}}
        <div class="px-8 py-6 text-center border-b-[4px] border-black" style="background-color: {{ $siteSettings['primary_color'] ?? '#6d0101' }};">
            <div class="flex justify-center mb-3">
                <div class="bg-white p-2 rounded-2xl border-2 border-black shadow-md">
                    <img src="{{ asset($siteSettings['logo_path'] ?? 'images/MAILogo.png') }}" alt="Logo" class="h-12 w-12 object-contain">
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 bg-black/40 text-amber-300 text-[10px] font-black uppercase tracking-[0.2em] px-3 py-1 rounded-full border border-amber-400/40 mb-2">
                <i class="fa-solid fa-shield-halved"></i> Restricted Access
            </span>
            <h1 class="text-2xl font-black text-white uppercase italic tracking-tight">Admin Portal</h1>
            <p class="text-[11px] font-bold text-red-200 uppercase tracking-widest mt-1">Authorized Personnel Only</p>
        </div>

        {{-- Form Body --}}
        <div class="p-8">
            @if (session('status'))
                <div class="mb-5 p-3 bg-green-100 border-2 border-green-600 text-green-800 rounded-xl text-xs font-bold text-center">
                    <i class="fa-solid fa-circle-check mr-1"></i> {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}">
                @csrf

                <div class="mb-5">
                    <label for="admin_id" class="block text-gray-900 font-black uppercase text-xs tracking-widest mb-1.5">
                        Admin Email / Username
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                            <i class="fa-solid fa-user-shield"></i>
                        </span>
                        <input id="admin_id" type="text" name="login_id" value="{{ old('login_id') }}" required autofocus autocomplete="username"
                               placeholder="admin@gmail.com"
                               class="w-full pl-10 pr-4 py-3 border-2 border-black rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#6d0101] font-bold text-sm">
                    </div>
                    @error('login_id')
                        <p class="text-red-600 text-xs font-bold mt-2 flex items-center gap-1">
                            <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="mb-5" x-data="{ show: false }">
                    <label for="admin_pass" class="block text-gray-900 font-black uppercase text-xs tracking-widest mb-1.5">
                        Password
                    </label>
                    <div class="relative flex items-center">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input id="admin_pass" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                               placeholder="••••••••"
                               class="w-full pl-10 pr-11 py-3 border-2 border-black rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#6d0101] font-bold text-sm">
                        <button type="button" @click="show = !show" class="absolute right-3.5 text-gray-400 hover:text-black focus:outline-none">
                            <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-600 text-xs font-bold mt-2 flex items-center gap-1">
                            <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label for="remember_admin" class="inline-flex items-center cursor-pointer">
                        <input id="remember_admin" type="checkbox" name="remember" class="rounded border-2 border-black text-[#6d0101] focus:ring-[#6d0101]">
                        <span class="ml-2 text-[11px] font-black uppercase tracking-wider text-gray-600">Remember me</span>
                    </label>

                    <a href="{{ route('admin.password.request') }}" class="text-[11px] font-black uppercase tracking-wider text-[#6d0101] hover:underline">
                        Forgot Password?
                    </a>
                </div>

                <button type="submit" class="w-full bg-gray-950 hover:bg-black text-white font-black py-3.5 rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_#6d0101] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all uppercase text-sm tracking-widest">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i> Sign In to Admin Portal
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-gray-200 text-center">
                <a href="{{ url('/') }}" class="text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-gray-700 transition-colors">
                    &larr; Return to School Website
                </a>
            </div>
        </div>
    </div>

    <p class="text-white/50 text-[10px] mt-8 uppercase font-bold tracking-[0.25em] text-center">
        &copy; 2026 {{ $siteSettings['school_name'] ?? 'Mendoza Academy, Inc.' }} &middot; All login attempts are monitored
    </p>
</body>
</html>