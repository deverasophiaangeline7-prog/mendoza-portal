<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .text-outline { -webkit-text-stroke: 1.5px white; } 
    </style>
</head>

<body class="bg-gray-100 h-screen overflow-hidden flex flex-col" x-data="{ isManaging: false, openAddModal: false }">

    <header class="bg-[#b91c1c] text-white py-3 px-6 shadow-md flex justify-between items-center relative z-50 flex-shrink-0">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('images/MAILogo.png') }}" class="h-10 w-10 bg-white p-1 rounded shadow" alt="Logo">
            <h1 class="text-2xl font-bold uppercase tracking-tight">Mendoza Academy, Inc.</h1>
        </div>
        
        <div class="flex items-center space-x-6 text-2xl">
            <x-top-icon-button>
                <i class="fa-solid fa-envelope relative">
                    <span class="absolute -top-2 -right-2 bg-yellow-400 text-red-700 text-xs rounded-full h-5 w-5 flex items-center justify-center border border-red-700 font-bold">1</span>
                </i>
            </x-top-icon-button>
            <x-top-icon-button><i class="fa-solid fa-bell"></i></x-top-icon-button>
            <i class="fa-solid fa-circle-user text-[#ffb31a] text-4xl"></i>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        
        <nav class="w-64 bg-[#b91c1c] text-white pt-4 flex-shrink-0 shadow-2xl z-40">
            <ul class="space-y-1">
                <x-sidebar-link href="{{ route('dashboard') }}" icon="fa-solid fa-chart-line">Dashboard</x-sidebar-link>
                <x-sidebar-link href="{{ route('students.index') }}" icon="fa-solid fa-user-graduate" :active="true">List of Students</x-sidebar-link>
                <x-sidebar-link href="#" icon="fa-solid fa-calendar-days">Student Calendar</x-sidebar-link>
                <x-sidebar-link href="{{ route('reportcard.index') }}" icon="fa-solid fa-star">Report Card</x-sidebar-link>
                <x-sidebar-link href="{{ route('attendance.index') }}" icon="fa-solid fa-calendar-check">Attendance</x-sidebar-link>
                @if(auth()->check() && auth()->user()->role === 'admin')
                    <x-sidebar-link href="{{ route('account.management') }}" icon="fa-solid fa-users-gear">Account Management</x-sidebar-link>
                @endif
            </ul>
        </nav>

        <main class="flex-1 bg-white flex flex-col items-center justify-center p-6 relative">
            <div class="w-full max-w-5xl">
                
                <div class="text-center mb-6">
                    <h2 class="text-4xl font-black text-black uppercase tracking-tight">
                        TOTAL STUDENTS: <span class="text-[#b91c1c]">{{ $totalStudents ?? 0 }}</span>
                    </h2>
                </div>

                <div class="grid grid-cols-3 gap-x-8 gap-y-6">
                    @foreach($sections as $section)
                        <div class="relative w-full">
                            
                            <form action="{{ route('sections.destroy', $section->id ?? $section->section_id) }}" method="POST" x-show="isManaging" x-cloak class="absolute -top-3 -right-3 z-50">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="event.stopPropagation(); return confirm('Are you sure you want to delete {{ $section->grade_level }} - {{ $section->section_name }}?')" class="bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center border-2 border-black shadow-sm animate-bounce hover:bg-red-700 hover:scale-110 transition-transform cursor-pointer">
                                    <i class="fa-solid fa-minus text-lg"></i>
                                </button>
                            </form>

                            <button type="button" 
                                onclick="window.location.href='/students/section/{{ $section->id ?? $section->section_id }}'"
                                class="w-full relative border-[3px] border-black rounded-[40px] py-6 flex flex-col items-center group transition-all active:translate-y-1 active:shadow-none shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] bg-[#ffb31a] hover:bg-[#ffc14d]">
                                
                                <span class="text-3xl font-black text-black tracking-wider text-outline uppercase group-hover:-translate-y-[2px] transition-transform">
                                    {{ is_numeric($section->grade_level) ? 'GRADE ' . $section->grade_level : $section->grade_level }}
                                </span>
                                <span class="text-lg font-medium text-black mt-1 group-hover:-translate-y-[2px] transition-transform">
                                    {{ $section->section_name }}
                                </span>
                            </button>

                        </div>
                    @endforeach
                </div>

                @if(auth()->check() && auth()->user()->role === 'admin')
                    <div class="flex justify-center items-center space-x-12 mt-8">
                        <button @click="openAddModal = true" class="flex items-center text-green-600 font-black text-xl hover:scale-110 transition-transform">
                            <span class="mr-2 text-2xl">+</span> Add a section
                        </button>
                        
                        <button @click="isManaging = !isManaging" 
                            class="flex items-center font-black text-xl hover:scale-110 transition-transform"
                            :class="isManaging ? 'text-gray-500' : 'text-red-600'">
                            <span class="mr-2 text-2xl" x-text="isManaging ? 'x' : '-'"></span> 
                            <span x-text="isManaging ? 'Cancel Editing' : 'Delete a section'"></span>
                        </button>
                    </div>
                @endif

            </div>
        </main>
    </div>

    <div x-show="openAddModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity">
        <div @click.away="openAddModal = false" class="bg-white border-[3px] border-black rounded-[30px] p-8 w-full max-w-md shadow-[10px_10px_0px_0px_rgba(0,0,0,1)]">
            
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-black uppercase text-black">New Section</h2>
                <button @click="openAddModal = false" class="text-gray-400 hover:text-red-600 text-2xl"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('sections.store') }}" method="POST">
                @csrf
                
                <div class="mb-5">
                    <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Grade Level</label>
                    <select name="grade_level" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold uppercase focus:outline-none focus:ring-4 focus:ring-yellow-400 bg-white cursor-pointer appearance-none">
                        <option value="" disabled selected>Select Grade Level</option>
                        <option value="NURSERY">Nursery</option>
                        <option value="KINDER">Kinder</option>
                        <option value="PREPARATORY">Preparatory</option>
                        <option value="GRADE 1">Grade 1</option>
                        <option value="GRADE 2">Grade 2</option>
                        <option value="GRADE 3">Grade 3</option>
                        <option value="GRADE 4">Grade 4</option>
                        <option value="GRADE 5">Grade 5</option>
                        <option value="GRADE 6">Grade 6</option>
                    </select>
                </div>

                <div class="mb-8">
                    <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Section Name</label>
                    <input type="text" name="section_name" placeholder="e.g. FAITH" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold uppercase focus:outline-none focus:ring-4 focus:ring-yellow-400">
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" @click="openAddModal = false" class="font-bold text-gray-500 hover:text-black uppercase tracking-wider px-4">Cancel</button>
                    
                    <button type="submit" class="bg-[#8cc63f] text-black font-black uppercase tracking-wider px-6 py-3 rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-[#9ee047] active:translate-y-1 active:shadow-none transition-all">
                        Save Section
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>