@extends('layouts.navigation')

@section('title', 'Parent Dashboard - Attendance')

@section('content')
<div class="flex-1 flex flex-col justify-between h-full min-h-screen bg-white">
    <div class="flex-1 p-4 flex justify-center items-center gap-8">
        
        <div class="border-2 border-black w-full max-w-xl shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] bg-white rounded-xl overflow-hidden">
            
            <div class="flex items-center justify-between px-6 py-3 border-b-2 border-black bg-gray-50">
                <a href="{{ route('parent.attendance', ['month' => $prevDate->month, 'year' => $prevDate->year]) }}" class="text-4xl font-black cursor-pointer hover:text-orange-500 transition-colors">&lt;</a>
                <h2 class="text-3xl font-black tracking-widest uppercase">{{ $monthName }}</h2>
                <a href="{{ route('parent.attendance', ['month' => $nextDate->month, 'year' => $nextDate->year]) }}" class="text-4xl font-black cursor-pointer hover:text-orange-500 transition-colors">&gt;</a>
            </div>

            <div class="grid grid-cols-7 text-center border-b-2 border-black py-2 bg-gray-100">
                @foreach(['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'] as $day)
                    <span class="text-[#b22222] font-black text-sm">{{ $day }}</span>
                @endforeach
            </div>

            <div class="grid grid-cols-7 p-3 gap-2">
                @for ($i = 0; $i < $firstDayOfWeek; $i++)
                    <div class="aspect-square border-none"></div>
                @endfor

                @foreach($days as $dayNum => $status)
                    @php
                        $statusClasses = match($status) {
                            'present' => 'bg-[#4ade80] border-black text-black',
                            'absent'  => 'bg-[#ef4444] border-black text-black',
                            'late'    => 'bg-[#facc15] border-black text-black',
                            'excused' => 'bg-[#60a5fa] border-black text-black',
                            'holiday' => 'bg-[#9ca3af] border-black text-black',
                            default   => 'bg-white border-black text-gray-300 shadow-none'
                        };
                    @endphp
                    
                    <div class="{{ $statusClasses }} border-[3px] aspect-square flex items-center justify-center text-xl font-black rounded-lg transition-all 
                        {{ $status !== 'none' ? 'shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : '' }}">
                        {{ $dayNum }}
                    </div>
                @endforeach
            </div>
        </div>

        <div class="w-56 pt-4 border-2 border-black p-4 rounded-xl shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] bg-gray-50">
            <h3 class="font-black text-xl mb-4 uppercase tracking-wider border-b-2 border-black pb-2">Legend</h3>
            <div class="space-y-3">
                @php
                    $legend = [
                        ['color' => 'bg-[#4ade80]', 'label' => 'Present'],
                        ['color' => 'bg-[#ef4444]', 'label' => 'Absent'],
                        ['color' => 'bg-[#facc15]', 'label' => 'Late'],
                        ['color' => 'bg-[#60a5fa]', 'label' => 'Excused'],
                        ['color' => 'bg-white', 'label' => 'Weekend/Holiday'],
                    ];
                @endphp
                @foreach($legend as $item)
                    <div class="flex items-center gap-3">
                        <span class="{{ $item['color'] }} w-6 h-6 rounded-full border-[3px] border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]"></span>
                        <span class="font-black text-sm uppercase">{{ $item['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <footer class="p-6 bg-white border-t-[3px] border-black flex justify-between items-center font-black text-xl shadow-[0px_-4px_0px_0px_rgba(0,0,0,1)] relative z-10 flex-shrink-0">
        <div class="uppercase tracking-wide text-black">
            {{ $student->last_name }}, {{ $student->first_name }} 
            {{ $student->middle_name ? substr($student->middle_name, 0, 1) . '.' : '' }}
        </div>
        <div class="uppercase tracking-wide text-[#ffb02e] drop-shadow-[1px_1px_0px_rgba(0,0,0,1)]">
            {{ $student->grade_level }} {{ $student->section ? '- ' . $student->section->section_name : '' }}
        </div>
    </footer>
</div>
@endsection