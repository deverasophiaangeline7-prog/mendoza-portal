<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); }
    </style>
</head>
<body class="bg-gray-100" x-data="{ openModal: false }">

    <header class="hero-gradient text-white py-4 px-6 shadow-lg flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('images/MAILogo.png') }}" class="h-10 w-10 bg-white p-1 rounded shadow">
            <h1 class="text-2xl font-bold uppercase tracking-tight">Mendoza Academy, Inc.</h1>
        </div>
        
        <div class="flex items-center space-x-6 text-2xl">
            <i class="fa-solid fa-envelope relative">
                <span class="absolute -top-2 -right-2 bg-red-500 text-xs rounded-full h-5 w-5 flex items-center justify-center border-2 border-red-700">1</span>
            </i>
            <i class="fa-solid fa-bell"></i>
            
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" title="Logout" class="hover:scale-110 transition-transform focus:outline-none">
                    <i class="fa-solid fa-circle-user text-orange-400 text-4xl"></i>
                </button>
            </form>
        </div>
    </header>
    
    <div class="flex h-screen bg-gray-100">
        <div class="flex-1 p-8">
            
            <div class="flex items-center mb-8">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center mr-8 text-gray-700 hover:text-red-600 transition">
                    <div class="bg-white p-2 rounded-full shadow-sm mb-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </div>
                    <a href="{{ route('dashboard') }}" class="flex flex-col items-center mr-8 text-gray-700 hover:text-red-600 transition">
                    </a>

                <div class="flex items-center">
                    <div class="text-4xl mr-3">📦</div> 
                    <h1 class="text-4xl font-black tracking-tight text-black">Archives</h1>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-xl p-8 max-w-4xl">
                @if($archivedImages->isEmpty())
                    <div class="text-center py-10">
                        <p class="text-gray-400 italic">The archive is currently empty.</p>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach($archivedImages as $image)
                            <div class="flex items-center justify-between group">
                                <div class="flex items-center space-x-6">
                                    <div class="relative w-16 h-16 rounded-xl overflow-hidden shadow-md border-2 border-white">
                                        <img src="{{ asset('storage/' . $image->image_path) }}" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-blue-500 opacity-10"></div>
                                    </div>

                                    <div>
                                        <h3 class="text-xl font-bold text-gray-800 tracking-wide">
                                            {{ basename($image->image_path) }}
                                        </h3>
                                        <p class="text-xs text-gray-400 uppercase font-bold">Archived on {{ $image->updated_at->format('M d, Y') }}</p>
                                    </div>
                                </div>

                                <form action="{{ route('announcement-images.restore', $image->image_id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="opacity-0 group-hover:opacity-100 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-full font-bold text-xs uppercase tracking-widest transition-all shadow-lg shadow-green-200">
                                        Restore Image
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>

</body>
</html>