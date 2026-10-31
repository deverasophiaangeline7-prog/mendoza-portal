@extends('layouts.navigation')

@section('title', 'Report Card Menu')

@section('content')
<div class="flex-1 p-4 md:p-8 bg-white min-h-screen relative flex flex-col items-center" x-data="{ passwordModal: {{ $errors->has('current_password') || $errors->has('password') ? 'true' : 'false' }} }">
    <div class="max-w-6xl w-full text-center flex flex-col items-center">
        
        <!-- Tightened header group -->
        <h2 class="text-3xl md:text-4xl font-black text-black uppercase tracking-tight mb-2">
            REPORT CARD
        </h2>

        @if(auth()->user()->role === 'admin')    
            <div class="text-center mb-3">
                <h3 class="text-xl md:text-2xl font-black text-black uppercase tracking-tight">
                    TOTAL STUDENTS: <span class="text-[#b91c1c]">{{ \App\Models\Student::count() }}</span>
                </h3>
            </div>
        @endif

        <div class="text-center mb-6 md:mb-8">
            <h3 class="text-xl md:text-2xl font-bold text-black">
                Select Section:
            </h3>
        </div>

        <!-- Sections Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-4 md:gap-y-6 gap-x-6 md:gap-x-10 w-full max-w-6xl justify-items-center px-2">
            @foreach($sections as $section)
                @php
                    $isNKP = in_array(strtoupper($section->grade_level), ['NURSERY', 'KINDER', 'KINDERGARTEN', 'PREPARATORY', 'PREP', 'NKP']);
                    $displayLevel = $isNKP ? strtoupper($section->grade_level) : (stripos($section->grade_level, 'GRADE') !== false ? strtoupper($section->grade_level) : 'GRADE ' . $section->grade_level);
                @endphp
                
                <a href="{{ route('reportcard.show', $section->section_id) }}"
                   class="bg-[#e68a2d] w-full max-w-[350px] py-5 rounded-[30px] border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-y-1 hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all text-center flex flex-col justify-center items-center group active:scale-95">
                   
                    <span class="text-2xl md:text-3xl font-black text-black uppercase tracking-tight transition-transform group-hover:-translate-y-1" 
                          style="text-shadow: 2px 2px 0 #fff, -2px -2px 0 #fff, 2px -2px 0 #fff, -2px 2px 0 #fff, 0 2px 0 #fff, 2px 0 0 #fff, 0 -2px 0 #fff, -2px 0 0 #fff;">
                        {{ $displayLevel }}
                    </span>

                    <span class="text-base md:text-lg font-medium text-black transition-transform group-hover:-translate-y-1 mt-0.5">
                        {{ $section->section_name }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection