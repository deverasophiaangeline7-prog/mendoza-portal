@extends('layouts.navigation')

@section('title', 'Report Card Menu')

@section('content')
<div class="p-4 md:p-8 flex flex-col items-center w-full min-h-screen bg-white" x-data="{ passwordModal: {{ $errors->has('current_password') || $errors->has('password') ? 'true' : 'false' }} }">
    
    <!-- Main Title & Inline Admin Count (Matches Attendance layout structure exactly) -->
    <div class="flex flex-col md:flex-row items-center justify-center gap-2 mb-2 md:mb-3">
        <h2 class="text-3xl md:text-5xl font-black text-black uppercase tracking-tight">
            REPORT CARD
        </h2>
        @if(auth()->user()->role === 'admin')    
            <span class="text-xs md:text-sm font-bold text-gray-700 uppercase tracking-wider bg-gray-100 px-3 py-1 rounded-full border border-black/20">
                Total: <span class="text-[#b91c1c] font-black">{{ \App\Models\Student::count() }}</span>
            </span>
        @endif
    </div>

    <!-- Subtitle (Exact size and margin match to Attendance) -->
    <h3 class="text-xl md:text-3xl font-black text-black mb-6 md:mb-8">
        Select Section:
    </h3>

    <!-- Sections Grid (Exact match to Attendance layout & spacing) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 md:gap-x-14 gap-y-6 md:gap-y-12 w-full max-w-6xl justify-items-center px-2">
        @foreach($sections as $section)
            @php
                $isNKP = in_array(strtoupper($section->grade_level), ['NURSERY', 'KINDER', 'KINDERGARTEN', 'PREPARATORY', 'PREP', 'NKP']);
                $displayLevel = $isNKP ? strtoupper($section->grade_level) : (stripos($section->grade_level, 'GRADE') !== false ? strtoupper($section->grade_level) : 'GRADE ' . $section->grade_level);
            @endphp
            
            <a href="{{ route('reportcard.show', $section->section_id) }}"
               class="bg-[#e68a2d] w-full max-w-[350px] py-6 rounded-[40px] border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-y-1 hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all text-center flex flex-col justify-center items-center group active:scale-95">
               
                <span class="text-3xl md:text-4xl font-black text-black uppercase tracking-tight transition-transform group-hover:-translate-y-1" 
                      style="-webkit-text-stroke: 1.5px white;">
                    {{ $displayLevel }}
                </span>

                <span class="text-lg md:text-xl font-medium text-black transition-transform group-hover:-translate-y-1 mt-1">
                    {{ $section->section_name }}
                </span>
            </a>
        @endforeach
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