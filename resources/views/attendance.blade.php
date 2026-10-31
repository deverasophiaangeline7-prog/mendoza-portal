@extends('layouts.navigation')

@section('title', 'Attendance')

@section('content')
<div class="flex-1 p-4 md:p-8 bg-white min-h-screen relative flex flex-col items-center" x-data="{ 
    openModal: false, 
    isManaging: false,
    isPublishing: false,
    passwordModal: false
}">
    <div class="max-w-6xl w-full text-center flex flex-col items-center">
        
        <!-- Main Title (Matches Report Card size: text-3xl md:text-5xl) -->
        <h2 class="text-3xl md:text-5xl font-black text-black mb-2 md:mb-3 uppercase tracking-tight">
            Attendance
        </h2>

        <!-- Subtitle (Matches Report Card spacing and font size) -->
        <h3 class="text-xl md:text-3xl font-black text-black mb-6 md:mb-8">
            Select Section:
        </h3>

        <!-- Sections Grid (Exact match to Report Card layout & spacing) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 md:gap-x-14 gap-y-6 md:gap-y-12 w-full max-w-6xl justify-items-center px-2">
            @foreach($sections as $section)
                @php
                    $isNKP = in_array(strtoupper($section->grade_level), ['NURSERY', 'KINDER', 'KINDERGARTEN', 'PREPARATORY', 'PREP', 'NKP']);
                    $displayLevel = $isNKP ? strtoupper($section->grade_level) : (stripos($section->grade_level, 'GRADE') !== false ? strtoupper($section->grade_level) : 'GRADE ' . $section->grade_level);
                @endphp

                <button type="button" 
                    onclick="window.location.href='{{ route('attendance.show', ['grade' => $section->section_id]) }}'"
                    class="border-2 border-black w-full max-w-[350px] rounded-[40px] py-6 flex flex-col items-center group transition-all active:scale-95 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] relative"
                    :class="isManaging ? 'bg-green-100 border-green-600' : 'bg-[#e68a2d]'">
                    
                    <div x-show="isManaging" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center border-2 border-black" x-cloak>
                        <i class="fa-solid fa-xmark"></i>
                    </div>

                    <!-- Text Stroke style matches Report Card text rendering completely -->
                    <span class="text-3xl md:text-4xl font-black text-black group-hover:-translate-y-1 group-hover:text-amber-700 transition-transform uppercase" 
                        style="-webkit-text-stroke: 1.5px white;">
                        {{ $displayLevel }}
                    </span>
                    <span class="text-lg md:text-xl font-medium text-black group-hover:-translate-y-1 transition-transform mt-1">
                        {{ $section->section_name }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Password Modal -->
    <div x-show="passwordModal" 
         x-transition:opacity
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" 
         x-cloak>
        
        <div @click.away="passwordModal = false" 
             class="bg-white border-[4px] border-black rounded-[2.5rem] p-6 md:p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] relative">
            
            <button @click="passwordModal = false" class="absolute top-4 right-6 md:top-6 md:right-8 text-4xl font-black text-gray-400 hover:text-black transition-colors">&times;</button>

            <h2 class="text-2xl md:text-3xl font-black italic uppercase tracking-tight mb-8">Change Password</h2>

            <form action="{{ route('user.password.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="space-y-5">
                    <div>
                        <label class="block font-bold uppercase text-black text-sm mb-2 tracking-widest">Current Password</label>
                        <input type="password" name="current_password" required 
                               class="w-full border-[3px] border-black rounded-2xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-green-400 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase text-black text-sm mb-2 tracking-widest">New Password</label>
                        <input type="password" name="password" required 
                               class="w-full border-[3px] border-black rounded-2xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-green-400 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase text-black text-sm mb-2 tracking-widest">Confirm New Password</label>
                        <input type="password" name="password_confirmation" required 
                               class="w-full border-[3px] border-black rounded-2xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-green-400 transition-all">
                    </div>
                </div>

                <div class="flex justify-end items-center gap-4 md:gap-8 mt-10">
                    <button type="button" @click="passwordModal = false" class="text-black font-black uppercase tracking-widest hover:text-gray-600 transition-colors">
                        Cancel
                    </button>
                    
                    <button type="submit" class="bg-[#22C55E] text-white font-black py-3 px-6 md:px-8 rounded-2xl border-[3px] border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] hover:brightness-95 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> UPDATE
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection