@extends('layouts.navigation')

@section('title', 'Student Calendar')

@section('content')
<main class="p-8 bg-white min-h-screen">
    <h2 class="text-3xl font-black uppercase mb-8 tracking-tight">Student Calendar</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @forelse($events as $event)
        <div class="bg-white border-[3px] border-black p-6 rounded-3xl shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
            <div class="mb-4">
                <h4 class="text-2xl font-black text-red-600 uppercase leading-tight mb-1">{{ $event->event_title }}</h4>
                <p class="font-bold text-gray-500 italic text-sm">
                    <i class="fa-regular fa-calendar mr-1"></i>
                    {{ \Carbon\Carbon::parse($event->start_date)->format('F d, Y') }}
                </p>
            </div>

            <div class="mt-4 pt-4 border-t-[2px] border-dashed border-gray-300">
                <p class="text-[11px] font-black uppercase text-gray-500 mb-3 tracking-widest">
                    {{ $student->first_name ?? 'Student' }} {{ $student->last_name ?? '' }} Assigned Role(s):
                </p>
                
                <div class="flex flex-wrap gap-2">
                    @foreach($event->participants as $participation)
                        <span class="bg-[#8cc63f] text-black border-2 border-black px-4 py-2 rounded-xl font-black uppercase text-xs shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                            <i class="fa-solid fa-star text-white mr-1" style="text-shadow: 1px 1px 0 #000;"></i> 
                            {{ $participation->role ?: 'General Participant' }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
        @empty
            <div class="col-span-full flex flex-col justify-center items-center py-20 bg-gray-50 rounded-[40px] border-[3px] border-dashed border-gray-300">
                <i class="fa-solid fa-calendar-xmark text-6xl text-gray-200 mb-4"></i>
                <p class="text-gray-400 text-xl font-black italic">No event participation yet.</p>
            </div>
        @endforelse
    </div>
</main>
@endsection