@extends('layouts.navigation')

@section('title', 'Report Card Menu')

@section('content')
<div class="flex-1 p-4 md:p-12 bg-white min-h-screen relative flex flex-col items-center" x-data="{ passwordModal: {{ $errors->has('current_password') || $errors->has('password') ? 'true' : 'false' }} }">
    <div class="max-w-6xl w-full text-center">
        
        <h2 class="text-3xl md:text-5xl font-black text-black mb-8 md:mb-12 uppercase tracking-tight">
            REPORT CARD
        </h2>

        @if(auth()->user()->role === 'admin')    
            <div class="text-center mb-6">
                <h2 class="text-2xl md:text-4xl font-black text-black uppercase tracking-tight">
                    TOTAL STUDENTS: <span class="text-[#b91c1c]">{{ \App\Models\Student::count() }}</span>
                </h2>
            </div>
        @endif

        <div class="text-center mb-8 md:mb-12">
            <h3 class="text-2xl md:text-3xl font-black text-black">
                Select Section:
            </h3>
        </div>

        <!-- Added px-2 to prevent buttons from touching screen edges on mobile -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-6 md:gap-y-12 gap-x-6 md:gap-x-14 w-full max-w-6xl justify-items-center px-2">
            @foreach($sections as $section)
                @php
                    $isNKP = in_array(strtoupper($section->grade_level), ['NURSERY', 'KINDER', 'KINDERGARTEN', 'PREPARATORY', 'PREP', 'NKP']);
                    $displayLevel = $isNKP ? strtoupper($section->grade_level) : (stripos($section->grade_level, 'GRADE') !== false ? strtoupper($section->grade_level) : 'GRADE ' . $section->grade_level);
                @endphp
                <!-- Changed hardcoded w-[350px] to w-full max-w-[350px] for better mobile scaling -->
                <a href="{{ route('reportcard.show', $section->section_id) }}"
                   class="bg-[#e68a2d] w-full max-w-[350px] py-6 rounded-[40px] border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-y-1 hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all text-center flex flex-col justify-center items-center group active:scale-95">
                   
                    <!-- Removed -webkit-text-stroke and added custom text-shadow for a clean white outline -->
                    <span class="text-3xl md:text-4xl font-black text-black uppercase tracking-tight transition-transform group-hover:-translate-y-1" 
                          style="text-shadow: 2px 2px 0 #fff, -2px -2px 0 #fff, 2px -2px 0 #fff, -2px 2px 0 #fff, 0 2px 0 #fff, 2px 0 0 #fff, 0 -2px 0 #fff, -2px 0 0 #fff;">
                        {{ $displayLevel }}
                    </span>

                    <span class="text-lg md:text-xl font-medium text-black transition-transform group-hover:-translate-y-1 mt-1">
                        {{ $section->section_name }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection