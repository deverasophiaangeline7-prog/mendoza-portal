<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Password Recovery | {{ $siteSettings['school_name'] ?? config('app.name', 'Mendoza Academy') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset($siteSettings['favicon_path'] ?? 'images/MAILogo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cover bg-center relative flex flex-col items-center justify-center px-4 py-12"
      style="background-image: url('{{ asset('images/MAIDinosaur.png') }}');">

    <div class="absolute inset-0 bg-black bg-opacity-40"></div>

    <div class="relative z-10 w-full max-w-lg flex flex-col items-center">
        
        <div class="bg-white p-10 md:p-12 rounded-[2rem] shadow-2xl w-full text-center border-t-8 border-[#9b1c1c]">
            
            <h2 class="text-3xl md:text-4xl font-[900] text-[#9b1c1c] uppercase italic tracking-tighter mb-4">
                Admin Password Recovery
            </h2>

            <div class="mb-8 text-[11px] font-bold uppercase tracking-widest text-gray-500 leading-relaxed">
                {{ __('Enter your registered administrator Gmail address to receive a password reset link.') }}
            </div>

            <x-auth-session-status class="mb-4 text-green-600 font-bold" :status="session('status')" />

            <form method="POST" action="{{ route('admin.password.email') }}" class="text-left">
                @csrf

                <div class="mb-8">
                    <x-input-label for="email" class="text-gray-700 font-black uppercase text-xs tracking-widest mb-2" :value="__('Email Address:')" />
                    <x-text-input id="email" 
                        class="block w-full border-gray-300 rounded-xl shadow-sm bg-gray-50 py-4" 
                        type="email" name="email" :value="old('email')" 
                        placeholder="example@gmail.com"
                        pattern="[a-zA-Z0-9._%+\-]+@[gG][mM][aA][iI][lL]\.[cC][oO][mM]"
                        title="Please enter a valid @gmail.com email address"
                        required autofocus />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="flex flex-col space-y-6">
                    <button type="submit" class="w-full bg-[#9b1c1c] text-white font-bold py-5 rounded-full shadow-lg hover:bg-red-900 transition-all uppercase text-lg tracking-widest active:scale-95">
                        {{ __('Send Reset Link') }}
                    </button>

                    <a href="{{ route('admin.login') }}" class="text-xs font-black uppercase tracking-widest text-gray-400 hover:text-[#9b1c1c] text-center transition-colors">
                        &larr; Back to Admin Login
                    </a>
                </div>
            </form>
        </div>

        <p class="mt-8 text-[10px] font-black uppercase text-white/80 drop-shadow-md tracking-[0.2em] text-center">
            &copy; 2026 {{ $siteSettings['school_name'] ?? 'Mendoza Academy, Inc.' }} | Student Monitoring System
        </p>
    </div>

</body>
</html>