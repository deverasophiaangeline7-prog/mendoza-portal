@extends('layouts.navigation')

@section('title', 'Student Calendar')

@section('content')
<main class="p-8 bg-white min-h-screen">
    <h2 class="text-4xl font-black uppercase mb-8 tracking-tighter text-gray-900">Student Calendar</h2>

    <div id="calendar-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($events as $event)
        <div class="bg-white border-[3px] border-black p-6 rounded-[2rem] shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] flex flex-col">
            
            <!-- Reduced mb-4 to mb-2 to lessen the gap before the dashed line -->
            <div class="mb-2 flex-shrink-0">
                <h4 class="text-2xl font-black text-red-600 uppercase leading-tight mb-1">{{ $event->event_title }}</h4>
                
                <div class="flex flex-col gap-0">
                    <p class="font-bold text-gray-400 italic text-sm flex items-center gap-1.5">
                        <i class="fa-regular fa-calendar w-4"></i>
                        {{ \Carbon\Carbon::parse($event->start_date)->format('F d, Y') }}
                    </p>
                    
                    @php
                        $displayTime = 'No time set';
                        if($event->time) {
                            $timeParts = preg_split('/(\r\n|\n| - )/', $event->time);
                            $startTime = '';
                            if (isset($timeParts[0]) && trim($timeParts[0])) {
                                try { $startTime = \Carbon\Carbon::parse(trim($timeParts[0]))->format('h:i A'); } 
                                catch (\Exception $e) { $startTime = trim($timeParts[0]); }
                            }
                            $endTime = '';
                            if (isset($timeParts[1]) && trim($timeParts[1])) {
                                try { $endTime = \Carbon\Carbon::parse(trim($timeParts[1]))->format('h:i A'); } 
                                catch (\Exception $e) { $endTime = trim($timeParts[1]); }
                            }
                            $parsedTime = implode(' - ', array_filter([$startTime, $endTime]));
                            if ($parsedTime) {
                                $displayTime = $parsedTime;
                            }
                        }
                    @endphp
                    
                    <p class="font-bold text-gray-400 italic text-sm flex items-center gap-1.5">
                        <i class="fa-regular fa-clock w-4"></i>
                        {{ $displayTime }}
                    </p>
                </div>
            </div>

            <!-- Reduced mt-4 to mt-2 and pt-4 to pt-3 to pull the line up -->
            <div class="mt-2 pt-3 border-t-2 border-dashed border-black flex-1">
                <p class="text-[11px] font-black text-gray-500 mb-3 tracking-widest">
                    <span class="capitalize text-black">{{ strtolower($student->first_name ?? 'Student') }} {{ strtolower($student->last_name ?? '') }}</span> 
                    assigned role(s):
                </p>
                
                <div class="flex flex-wrap gap-1.5">
                    @foreach($event->participants as $participation)
                        <span class="inline-flex items-center gap-1.5 text-[11px] bg-[#8cc63f] text-black px-2 py-1 rounded font-bold capitalize">
                            <i class="fa-solid fa-star text-white"></i> 
                            {{ strtolower($participation->role ?: 'General Participant') }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
        @empty
            <div class="col-span-full flex flex-col justify-center items-center py-20 bg-gray-50 rounded-[2rem] border-[3px] border-dashed border-gray-300">
                <i class="fa-solid fa-calendar-xmark text-6xl text-gray-200 mb-4"></i>
                <p class="text-gray-400 text-xl font-black italic">No event participation yet.</p>
            </div>
        @endforelse
    </div>
</main>

<script>
    setInterval(() => {
        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newCalendar = doc.getElementById('calendar-container');
                const oldCalendar = document.getElementById('calendar-container');
                
                if (newCalendar && oldCalendar && newCalendar.innerHTML !== oldCalendar.innerHTML) {
                    oldCalendar.innerHTML = newCalendar.innerHTML;
                }
            })
            .catch(err => console.error('Sync error:', err));
    }, 3000); 
</script>
@endsection