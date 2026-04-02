<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy, Inc.</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('images/MAILogo.png') }}">

    <style>
        .hero-gradient {
            background: linear-gradient(to right, #d32f2f, #8b0000);
        }
        [x-cloak] { display: none !important; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; }
    </style>
</head>
<body class="bg-gray-100">

<header class="hero-gradient text-white py-4 px-6 shadow-lg">
    <div class="container mx-auto flex flex-wrap justify-between items-center">
        <div class="flex items-center space-x-3">
            <div class="p-1 rounded shadow-sm bg-white">
                <img src="{{ asset('images/MAILogo.png') }}" alt="Logo" class="h-10 w-10">
            </div>
            <h1 class="text-2xl font-bold tracking-tight uppercase">Mendoza Academy, Inc.</h1>
        </div>
    
        <div class="flex items-center space-x-6 text-2xl">
            <div class="relative cursor-pointer hover:text-orange-400 transition">
                <i class="fa-solid fa-envelope"></i>
                <span class="absolute -top-2 -right-2 bg-red-500 text-xs rounded-full h-5 w-5 flex items-center justify-center border-2 border-[#b91c1c]">1</span>
            </div>

            <div class="cursor-pointer hover:text-orange-400 transition">
                <i class="fa-solid fa-bell"></i>
            </div>
            
            <div class="group relative flex items-center">
                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <button type="submit" title="Logout" class="flex items-center space-x-2 hover:text-orange-400 transition focus:outline-none">
                        <i class="fa-solid fa-circle-user text-orange-400 text-3xl"></i>
                        <span class="text-sm font-bold uppercase hidden md:inline">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<div class="flex min-h-screen">
    <nav class="w-64 bg-[#b91c1c] text-white pt-4">
        <ul class="space-y-1">
            <li class="bg-orange-400 mx-2 rounded-lg">
                <a href="#" class="flex items-center p-3 space-x-3">
                    <i class="fa-solid fa-chart-line"></i>
                    <span class="font-semibold">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition">
                    <i class="fa-solid fa-user-graduate"></i>
                    <span>Student Information</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>Student Calendar</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition">
                    <i class="fa-solid fa-star"></i>
                    <span>Report Card</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition">
                    <i class="fa-solid fa-wallet"></i>
                    <span>Tuition Fee</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Attendance</span>
                </a>
            </li>
        </ul>
    </nav>

    <main class="flex-1 p-8 bg-white">
        <h2 class="text-3xl font-bold mb-6">Welcome, {{ Auth::user()->name ?? 'Y/N' }}</h2>

        <div class="relative w-full h-96 bg-orange-400 rounded-3xl p-6 shadow-lg border-2 border-black mb-8" 
             x-data="{ activeSlide: 0, total: {{ $announcementImages->count() }} }">
            
            <div class="bg-blue-100 w-full h-full rounded-2xl border-4 border-orange-300 relative overflow-hidden flex items-center justify-center">
                
                @forelse($announcementImages as $index => $image)
                    <div x-show="activeSlide === {{ $index }}" 
                         x-cloak
                         x-transition:enter="transition duration-500"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         class="absolute inset-0">
                        <img src="{{ asset('storage/' . $image->image_path) }}" class="w-full h-full object-cover">
                        
                        @if($image->caption)
                            <div class="absolute bottom-4 left-4 bg-black/50 text-white px-4 py-1 rounded-lg backdrop-blur-sm">
                                {{ $image->caption }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center text-gray-400">
                        <i class="fa-solid fa-bullhorn text-5xl mb-2"></i>
                        <p class="italic">No current announcements</p>
                    </div>
                @endforelse

                @if($announcementImages->count() > 1)
                    <button @click="activeSlide = activeSlide === 0 ? total - 1 : activeSlide - 1" class="absolute left-4 z-10 bg-white/30 p-2 rounded-full hover:bg-white/60 transition">
                        <i class="fa-solid fa-chevron-left text-black"></i>
                    </button>
                    <button @click="activeSlide = activeSlide === total - 1 ? 0 : activeSlide + 1" class="absolute right-4 z-10 bg-white/30 p-2 rounded-full hover:bg-white/60 transition">
                        <i class="fa-solid fa-chevron-right text-black"></i>
                    </button>
                @endif
            </div>

            <div class="flex justify-center space-x-2 mt-4">
                @foreach($announcementImages as $index => $image)
                    <button @click="activeSlide = {{ $index }}" 
                            class="h-3 rounded-full transition-all duration-300"
                            :class="activeSlide === {{ $index }} ? 'bg-green-600 w-6' : 'bg-gray-400 w-3'">
                    </button>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-2 gap-12 mt-12">
                
                <div>
                    <h3 class="text-4xl font-black text-center mb-6 tracking-tighter uppercase">SCHOOL CALENDAR</h3>
                    <div class="bg-[#d97706] rounded-[40px] p-6 border-[3px] border-black shadow-lg">
                        <div class="flex justify-between items-center mb-4 px-2">
                            <span class="text-white text-5xl font-black italic tracking-tighter leading-none" style="text-shadow: 2px 2px 0px #800000;">MARCH</span>
                            <span class="text-white text-5xl font-black tracking-tighter leading-none">2026</span>
                        </div>
                        
                        <div class="bg-white rounded-2xl p-4 border-2 border-black">
                            <div class="calendar-grid mb-4">
                                @foreach(['SUN','MON','TUE','WED','THU','FRI','SAT'] as $day)
                                    <span class="text-[#b91c1c] text-center font-black text-sm">{{ $day }}</span>
                                @endforeach
                            </div>

                            <div class="calendar-grid">
                                @for ($i = 1; $i <= 31; $i++)
                                    <div class="aspect-square flex items-center justify-center rounded-lg border-2 border-gray-200 font-black text-xl 
                                        {{ $i == 25 ? 'bg-red-500 text-white border-black shadow-md' : 'bg-white text-black' }}">
                                        {{ $i }}
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-4xl font-black text-center mb-6 tracking-tighter uppercase">EVENTS</h3>
                    <div class="bg-[#d97706] rounded-[40px] p-8 border-[3px] border-black shadow-lg min-h-[400px]">
                    </div>
                </div>

            </div>
        </main>
    </div>

    <div x-show="openModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50" x-cloak>
        <div class="bg-white p-8 rounded-3xl w-full max-w-md border-4 border-orange-400">
            <form action="{{ route('announcement-images.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="image" class="mb-4 w-full">
                <input type="text" name="caption" placeholder="Caption" class="w-full p-2 border rounded mb-4">
                <div class="flex space-x-2">
                    <button type="button" @click="openModal = false" class="bg-gray-200 px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-red-700 text-white px-4 py-2 rounded">Upload</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>