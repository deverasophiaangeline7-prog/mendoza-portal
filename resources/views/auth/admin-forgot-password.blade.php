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
    <title>Admin Password Recovery | {{ $schoolName }}</title>

    <link rel="icon" type="image/png" href="{{ asset($faviconPath) }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="min-h-screen bg-cover bg-center relative flex flex-col items-center justify-center px-4 py-10"
      style="background-image: url('{{ asset('images/MAIDinosaur.png') }}');">

    <div class="absolute inset-0 bg-black/45 backdrop-blur-[2px]"></div>

    <div class="relative z-10 w-full max-w-md flex flex-col items-center">

        <div class="w-full bg-white p-8 md:p-10 rounded-[2rem] shadow-2xl text-center border-t-8"
             style="border-top-color: {{ $primaryColor }};">

            <div class="flex flex-col items-center mb-6">
                <img src="{{ asset($logoPath) }}" alt="Logo" class="h-14 w-14 object-contain mb-3 drop-shadow-sm">
                <span class="inline-flex items-center gap-1.5 bg-red-50 text-red-800 border border-red-200 text-[10px] font-black uppercase tracking-[0.2em] px-3 py-1 rounded-full mb-3">
                    <i class="fa-solid fa-key"></i> Admin Recovery
                </span>
                <h1 class="text-3xl font-black uppercase italic tracking-tighter leading-none" style="color: {{ $primaryColor }};">
                    Password Recovery
                </h1>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mt-2 leading-relaxed">
                    Enter your registered admin Gmail address to receive a reset link.
                </p>
            </div>

            @if (session('status'))
                <div class="mb-5 p-3.5 bg-green-50 border border-green-300 text-green-700 rounded-xl text-xs font-bold text-center">
                    <i class="fa-solid fa-circle-check mr-1"></i> {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.password.email') }}" class="text-left">
                @csrf

                <div class="mb-6">
                    <label for="email" class="block font-black uppercase text-[11px] tracking-widest mb-1.5" style="color: {{ $primaryColor }};">
                        Administrator Email
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="example@gmail.com"
                           pattern="[a-zA-Z0-9._%+\-]+@[gG][mM][aA][iI][lL]\.[cC][oO][mM]"
                           title="Please enter a valid @gmail.com email address"
                           class="block w-full px-4 py-3.5 border border-gray-300 focus:border-red-700 focus:ring-2 focus:ring-red-700/20 rounded-xl shadow-sm bg-gray-50 focus:bg-white focus:outline-none text-sm font-semibold text-gray-800 transition">
                    @error('email')
                        <p class="text-red-600 text-xs font-bold mt-2 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <button type="submit"
                        style="background-color: {{ $primaryColor }};"
                        class="w-full text-white font-black py-4 rounded-full shadow-lg hover:brightness-90 transition-all uppercase text-sm tracking-widest active:scale-95">
                    Send Reset Link
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-gray-100 text-center">
                <a href="{{ route('admin.login') }}" class="text-xs font-black uppercase tracking-widest text-gray-400 hover:text-red-800 transition-colors">
                    &larr; Back to Admin Log In
                </a>
            </div>
        </div>

        <p class="text-white/90 text-[10px] mt-8 uppercase font-bold tracking-[0.2em] text-center drop-shadow-md">
            &copy; 2026 {{ $schoolName }} | Student Monitoring System
        </p>
    </div>
</body>
</html>