@extends('layouts.navigation')

@section('title', 'Advisory Class')

@section('content')
<div class="flex-1 flex flex-col relative w-full" x-data="{ isManaging: false, openAddModal: false, passwordModal: {{ $errors->has('current_password') || $errors->has('password') ? 'true' : 'false' }} }">

    <main class="flex-1 p-12 bg-white flex flex-col items-center min-h-screen">
        
        <h2 class="text-5xl font-black text-black mb-12 uppercase tracking-tight text-center">
            ADVISORY CLASS
        </h2>

        @if(auth()->user()->role === 'admin')    
            <div class="text-center mb-6">
                <h2 class="text-4xl font-black text-black uppercase tracking-tight">
                    TOTAL STUDENTS: <span class="text-[#b91c1c]">{{ $totalStudents ?? \App\Models\Student::count() }}</span>
                </h2>
            </div>
        @endif

        <div class="text-center mb-12">
            <h3 class="text-3xl font-black text-black">Select Section:</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-12 gap-x-14 w-full max-w-6xl justify-items-center">
            @foreach($sections as $section)
                @php
                    $isNKP = in_array(strtoupper($section->grade_level), ['NURSERY', 'KINDER', 'KINDERGARTEN', 'PREPARATORY', 'PREP', 'NKP']);
                    $displayLevel = $isNKP ? strtoupper($section->grade_level) : (stripos($section->grade_level, 'GRADE') !== false ? strtoupper($section->grade_level) : 'GRADE ' . $section->grade_level);
                @endphp
                
                <div class="relative w-full max-w-[350px]">
                    <form action="{{ route('sections.destroy', $section->id ?? $section->section_id) }}" method="POST" x-show="isManaging" x-cloak class="absolute -top-3 -right-3 z-50">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="event.stopPropagation(); return confirm('Are you sure you want to delete {{ $section->grade_level }} - {{ $section->section_name }}?')" class="bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center border-2 border-black shadow-sm animate-bounce hover:bg-red-700 hover:scale-110 transition-transform cursor-pointer">
                            <i class="fa-solid fa-minus text-lg"></i>
                        </button>
                    </form>

                    <button type="button" 
                        onclick="window.location.href='/students/section/{{ $section->id ?? $section->section_id }}'"
                        class="bg-[#e68a2d] w-[350px] py-6 rounded-[40px] border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-y-1 hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all text-center flex flex-col justify-center items-center group active:scale-95">
                        
                        <span class="text-4xl font-black text-black uppercase tracking-tight transition-transform group-hover:-translate-y-1" 
                            style="-webkit-text-stroke: 1.5px white;">
                            {{ $displayLevel }}
                        </span>

                        <span class="text-xl font-medium text-black transition-transform group-hover:-translate-y-1 mt-1">
                            {{ $section->section_name }}
                        </span>
                    </button>
                </div>
            @endforeach
        </div>

        @if(auth()->check() && auth()->user()->role === 'admin')
            <div class="flex justify-center items-center space-x-12 mt-12">
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

    </main>

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
                    <select name="grade_level" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-yellow-400 bg-white cursor-pointer appearance-none">
                        <option value="" disabled selected>Select Grade Level</option>
                        <option value="NURSERY">Nursery</option>
                        <option value="KINDER">Kinder</option>
                        <option value="PREPARATORY">Preparatory</option>
                        <option value="1">Grade 1</option>
                        <option value="2">Grade 2</option>
                        <option value="3">Grade 3</option>
                        <option value="4">Grade 4</option>
                        <option value="5">Grade 5</option>
                        <option value="6">Grade 6</option>
                    </select>
                </div>

                <div class="mb-8">
                    <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Section Name</label>
                    <input type="text" name="section_name" placeholder="e.g. FAITH" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-yellow-400">
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
</div>
@endsection