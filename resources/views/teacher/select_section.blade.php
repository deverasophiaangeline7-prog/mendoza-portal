@extends('layouts.navigation') <!-- nde ko alam san to-->

@section('title', 'Attendance Selection')

@section('content')
<div class="p-12 flex flex-col items-center w-full min-h-screen bg-white">
    <h2 class="text-5xl font-black text-black mb-12 uppercase tracking-tight">Attendance</h2>
    <h3 class="text-3xl font-black text-black mb-12">Select Section:</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-12 gap-x-14 w-full max-w-6xl justify-items-center">
        @foreach($sections as $section)
            @php 
                $slug = strtolower(str_replace([' ', 'garten'], ['', ''], $section->grade_level)); 
            @endphp
            
            <a href="{{ route('attendance.show', $slug) }}" 
               class="bg-[#ffaf2e] w-[350px] py-8 rounded-[40px] border-[3px] border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] hover:translate-y-1 hover:shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] transition-all text-center group flex flex-col justify-center items-center">
                
                <div class="font-black text-4xl uppercase tracking-tighter text-black group-hover:scale-110 transition-transform" 
                     style="text-shadow: 2px 2px 0 #fff, -1px -1px 0 #fff, 1px -1px 0 #fff, -1px 1px 0 #fff, 1px 1px 0 #fff;">
                    {{ str_replace('garten', '', $section->grade_level) }}
                </div>

                <div class="font-bold text-xl text-black mt-2 tracking-widest uppercase bg-white/50 px-4 py-1 rounded-full mt-4 border-2 border-black">
                    {{ $section->section_name }}
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection