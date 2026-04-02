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
        [x-cloak] { display: none !important; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; }
    </style>
</head>

@if ($errors->any())
    <div class="bg-red-500 text-white p-4">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

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
                    <span>List of Students</span>
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
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-3xl font-extrabold">Welcome, Admin</h2>
                <a href="{{ route('announcement-images.archived') }}" class="hover:opacity-100 transition-opacity" title="View Archives">
                    <i class="fa-solid fa-box-archive text-3xl text-orange-800 opacity-80"></i>
                </a>
            </div>

            <div class="relative w-full h-96 bg-orange-400 rounded-3xl p-6 shadow-lg border-2 border-black" 
                 x-data="{ activeSlide: 0, slidesCount: {{ $announcementImages->count() }} }">
                
                <div class="bg-blue-100 w-full h-full rounded-2xl border-4 border-orange-300 relative overflow-hidden flex items-center justify-center">
                    
                    @if($announcementImages->count() > 0)
                        @foreach($announcementImages as $index => $image)
                            <div x-show="activeSlide === {{ $index }}" 
                                 x-transition:enter="transition ease-out duration-500"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute inset-0">
                                <img src="{{ asset('storage/' . $image->image_path) }}" class="w-full h-full object-cover">
                                
                                @if($image->caption)
                                    <div class="absolute bottom-4 left-4 bg-black/60 text-white px-4 py-2 rounded-xl backdrop-blur-sm">
                                        {{ $image->caption }}
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        @if($announcementImages->count() > 1)
                            <button @click="activeSlide = activeSlide === 0 ? slidesCount - 1 : activeSlide - 1" 
                                    class="absolute left-4 z-20 bg-white/40 hover:bg-white/80 p-2 rounded-full transition shadow-md">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <button @click="activeSlide = activeSlide === slidesCount - 1 ? 0 : activeSlide + 1" 
                                    class="absolute right-4 z-20 bg-white/40 hover:bg-white/80 p-2 rounded-full transition shadow-md">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        @endif
                    @else
                        <div class="text-center">
                            <i class="fa-solid fa-image text-5xl text-blue-200 mb-2"></i>
                            <p class="text-gray-400 italic font-bold">No Active Announcement Image</p>
                        </div>
                    @endif

                    <div class="absolute top-4 right-6 z-30" x-data="{ openDropdown: false }">
                        <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" 
                                class="text-xl font-black tracking-widest hover:scale-105 transition-all focus:outline-none bg-white/90 px-3 py-1 rounded-lg shadow-sm">
                            EDIT <i class="fa-solid fa-caret-down ml-1"></i>
                        </button>
                        
                        <div x-show="openDropdown" x-transition x-cloak
                             class="absolute right-0 mt-2 w-40 bg-white rounded-xl shadow-2xl border border-gray-200 py-2 text-right">
                            
                            <button @click="openModal = true; openDropdown = false" class="block w-full px-4 py-2 text-black font-bold hover:bg-gray-100 uppercase text-sm">
                                Add New
                            </button>
                            
                            @if($announcementImages->count() > 0)
                                @foreach($announcementImages as $index => $image)
                                    <form x-show="activeSlide === {{ $index }}" action="{{ route('announcement-images.archive', $image->image_id) }}" method="POST">
                                        @csrf 
                                        @method('PATCH')
                                        <button type="submit" class="block w-full px-4 py-2 text-red-600 font-bold hover:bg-gray-100 uppercase text-sm border-t border-gray-100">
                                            Archive Current
                                        </button>
                                    </form>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex justify-center space-x-2 mt-4">
                    @foreach($announcementImages as $index => $image)
                        <button @click="activeSlide = {{ $index }}" 
                                class="h-3 rounded-full transition-all duration-300"
                                :class="activeSlide === {{ $index }} ? 'bg-black w-8' : 'bg-gray-400 w-3'">
                        </button>
                    @endforeach
                </div>
            </div>
            <div x-show="openModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50" x-cloak>
                <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md border-4 border-orange-400">
                    <h3 class="text-2xl font-black mb-6 text-red-800 uppercase italic">Upload New Image</h3>
                    
                    <form action="{{ route('announcement-images.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="block font-bold text-gray-700 mb-2 uppercase text-xs">Select File</label>
                            <input type="file" name="image" required class="block w-full text-sm border-2 border-dashed border-gray-300 p-4 rounded-xl cursor-pointer hover:border-orange-400 transition">
                        </div>
                        
                        <div class="mb-6">
                            <label class="block font-bold text-gray-700 mb-2 uppercase text-xs">Caption</label>
                            <input type="text" name="caption" placeholder="Ex: School Holiday" class="w-full p-3 border-2 border-gray-200 rounded-xl focus:border-red-600 outline-none transition">
                        </div>

                        <div class="flex space-x-3">
                            <button type="button" @click="openModal = false" class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-300 transition">CANCEL</button>
                            <button type="submit" class="flex-1 px-4 py-3 bg-red-700 text-white font-bold rounded-xl hover:bg-red-800 shadow-lg transition">UPLOAD</button>
                        </div>
                    </form>
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
                        <a href="#" class="text-[#4ade80] text-3xl font-black italic hover:brightness-110 transition flex items-center" style="text-shadow: 1px 1px 0px black;">
                            <span class="mr-2">+</span> Add an event
                        </a>
                        
                        <div class="mt-6 space-y-4">
                            </div>
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