@extends('layouts.navigation')

@section('title', 'Attendance Selection')

@section('content')
<div class="p-12 flex flex-col items-center w-full min-h-screen bg-white">
    <h2 class="text-5xl font-black text-black mb-12 uppercase tracking-tight">Attendance</h2>
    <h3 class="text-3xl font-black text-black mb-12">Select Section:</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-12 gap-x-14 w-full max-w-6xl justify-items-center">
        @foreach($sections as $section)
            @php 
                $isNKP = in_array(strtoupper($section->grade_level), ['NURSERY', 'KINDER', 'KINDERGARTEN', 'PREPARATORY', 'PREP', 'NKP']);
                $displayLevel = $isNKP ? strtoupper($section->grade_level) : (stripos($section->grade_level, 'GRADE') !== false ? strtoupper($section->grade_level) : 'GRADE ' . $section->grade_level);
            @endphp
            
            <a href="{{ route('attendance.show', $section->section_id) }}" 
               class="bg-[#e68a2d] w-[350px] py-6 rounded-[40px] border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-y-1 hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all text-center flex flex-col justify-center items-center group active:scale-95">
                
                <span class="text-4xl font-black text-black uppercase tracking-tight transition-transform group-hover:-translate-y-1" 
                    style="-webkit-text-stroke: 1.5px white;">
                    {{ $displayLevel }}
                </span>

                <span class="text-xl font-medium text-black transition-transform group-hover:-translate-y-1 mt-1">
                    {{ $section->section_name }}
                </span>
            </a>
        @endforeach
    </div>
</div>
@endsection