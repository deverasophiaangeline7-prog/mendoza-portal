<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Password Recovery | {{ $siteSettings['school_name'] ?? 'Mendoza Academy, Inc.' }}</title>

    <link rel="icon" type="image/png" href="{{ asset($siteSettings['favicon_path'] ?? 'images/MAILogo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-neutral-900 to-[#3b0000] flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-md bg-white rounded-3xl border-[4px] border-black shadow-[10px_10px_0px_0px_rgba(0,0,0,0.9)] overflow-hidden">
        
        <div class="px-8 py-6 text-center border-b-[4px] border-black" style="background-color: {{ $siteSettings['primary_color'] ?? '#6d0101' }};">
            <span class="inline-flex items-center gap-1.5 bg-black/40 text-amber-300 text-[10px] font-black uppercase tracking-[0.2em] px-3 py-1 rounded-full border border-amber-400/40 mb-2">
                <i class="fa-solid fa-key"></i> Admin Recovery
            </span>
            <h1 class="text-2xl font-black text-white uppercase italic tracking-tight">Password Recovery</h1>
            <p class="text-[11px] font-bold text-red-200 uppercase tracking-widest mt-1">Administrators &amp; Superadmin Only</p>
        </div>

        <div class="p-8">
            <p class="text-xs font-bold text-gray-600 text-center uppercase tracking-wider leading-relaxed mb-6">
                Enter your registered administrator Gmail address to receive a password reset link.
            </p>

            @if (session('status'))
                <div class="mb-5 p-3 bg-green-100 border-2 border-green-600 text-green-800 rounded-xl text-xs font-bold text-center">
                    <i class="fa-solid fa-circle-check mr-1"></i> {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.password.email') }}">
                @csrf

                <div class="mb-6">
                    <label for="email" class="block text-gray-900 font-black uppercase text-xs tracking-widest mb-1.5">
                        Administrator Email
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="admin@gmail.com"
                           pattern="[a-zA-Z0-9._%+\-]+@[gG][mM][aA][iI][lL]\.[cC][oO][mM]"
                           title="Please enter a valid @gmail.com email address"
                           class="w-full px-4 py-3 border-2 border-black rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#6d0101] font-bold text-sm">
                    @error('email')
                        <p class="text-red-600 text-xs font-bold mt-2 flex items-center gap-1">
                            <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <button type="submit" class="w-full bg-gray-950 hover:bg-black text-white font-black py-3.5 rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_#6d0101] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all uppercase text-sm tracking-widest">
                    Send Reset Link
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-gray-200 text-center">
                <a href="{{ route('admin.login') }}" class="text-xs font-black uppercase tracking-widest text-gray-500 hover:text-[#6d0101] transition-colors">
                    &larr; Back to Admin Portal
                </a>
            </div>
        </div>
    </div>

    <p class="text-white/50 text-[10px] mt-8 uppercase font-bold tracking-[0.25em] text-center">
        &copy; 2026 {{ $siteSettings['school_name'] ?? 'Mendoza Academy, Inc.' }}
    </p>
</body>
</html>