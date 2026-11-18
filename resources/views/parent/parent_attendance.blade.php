@extends('layouts.navigation')

@section('title', 'Parent Dashboard - Attendance')

@section('content')
<div class="flex-1 flex flex-col justify-between h-full min-h-screen bg-white" 
     x-data="calendarNav()" 
     @popstate.window="navigate(window.location.href)">
    
    <div class="flex-1 p-4 flex flex-col lg:flex-row justify-center items-center lg:items-start gap-8 mt-4 md:mt-8">
        
        <!-- Add an ID to the calendar container so we know what to replace -->
        <div id="calendar-container" 
             class="border-[3px] border-black w-full max-w-xl shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] bg-white rounded-xl overflow-hidden relative"
             :class="{ 'opacity-50 pointer-events-none': loading }"
             style="transition: opacity 0.3s ease;">
            
            <!-- Loading Spinner (Optional) -->
            <div x-show="loading" class="absolute inset-0 z-50 flex items-center justify-center bg-white bg-opacity-70">
                <span class="font-black text-xl animate-pulse">Loading...</span>
            </div>

            <div class="flex items-center justify-between px-4 sm:px-6 py-3 border-b-[3px] border-black bg-gray-50">
                <!-- Change hrefs to use @click.prevent -->
                <a href="{{ route('parent.attendance', ['month' => $prevDate->month, 'year' => $prevDate->year]) }}" 
                   @click.prevent="navigate($el.href)"
                   class="text-3xl sm:text-4xl font-black cursor-pointer hover:-translate-x-1 transition-transform">&lt;</a>
                
                <h2 class="text-2xl sm:text-3xl font-black tracking-widest uppercase">{{ $monthName }}</h2>
                
                <a href="{{ route('parent.attendance', ['month' => $nextDate->month, 'year' => $nextDate->year]) }}" 
                   @click.prevent="navigate($el.href)"
                   class="text-3xl sm:text-4xl font-black cursor-pointer hover:translate-x-1 transition-transform">&gt;</a>
            </div>

            <div class="grid grid-cols-7 text-center border-b-[3px] border-black py-2 bg-gray-100">
                @foreach(['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'] as $day)
                    <span class="text-[#b22222] font-black text-[10px] sm:text-sm">{{ $day }}</span>
                @endforeach
            </div>

            <div class="grid grid-cols-7 p-2 sm:p-3 gap-1 sm:gap-2"
                 x-data="{ 
                    attendanceData: {{ json_encode($rawAttendance) }},
                    getClass(status) {
                        if (status === 'present') return 'bg-[#4ade80] border-black text-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] sm:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]';
                        if (status === 'absent') return 'bg-[#ef4444] border-black text-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] sm:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]';
                        if (status === 'late') return 'bg-[#facc15] border-black text-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] sm:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]';
                        if (status === 'excused') return 'bg-[#60a5fa] border-black text-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] sm:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]';
                        return 'bg-white border-black text-gray-300 shadow-none';
                    },
                    init() {
                        // Clear any existing intervals if the component reloads
                        if(window.attendanceInterval) clearInterval(window.attendanceInterval);
                        
                        window.attendanceInterval = setInterval(() => {
                            fetch('{{ route('attendance.fetch') }}?month={{ $currentDate->month }}&year={{ $currentDate->year }}&t=' + Date.now())
                                .then(res => res.json())
                                .then(data => { this.attendanceData = { ...data }; })
                                .catch(err => console.error('Error fetching attendance:', err));
                        }, 5000);
                    }
                 }">
                
                @for ($i = 0; $i < $firstDayOfWeek; $i++)
                    <div class="aspect-square border-none"></div>
                @endfor

                @foreach($days as $dayNum => $status)
                    @php
                        $dateString = $currentDate->copy()->day($dayNum)->format('Y-m-d');
                    @endphp
                    <div class="border-2 sm:border-[3px] aspect-square flex items-center justify-center text-sm sm:text-xl font-black rounded-lg transition-all"
                         :class="getClass(attendanceData['{{ $dateString }}'] || 'none')">
                        {{ $dayNum }}
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Legend Section remains unchanged -->
        <!-- ... -->
    </div>
</div>

<!-- Alpine Script to handle the smooth DOM replacement -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('calendarNav', () => ({
            loading: false,
            
            async navigate(url) {
                if (this.loading) return;
                this.loading = true;
                
                try {
                    // Fetch the new page HTML
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    
                    const html = await response.text();
                    
                    // Parse the new HTML string into a DOM element
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Extract just the calendar container from the fetched page
                    const newCalendar = doc.getElementById('calendar-container');
                    
                    if (newCalendar) {
                        // Swap out the old calendar with the new one
                        document.getElementById('calendar-container').innerHTML = newCalendar.innerHTML;
                        
                        // Update the browser URL bar so refreshing works, without reloading the page
                        window.history.pushState({}, '', url);
                    }
                } catch (error) {
                    console.error('Failed to load new month:', error);
                    // Fallback to standard page load if fetch fails
                    window.location.href = url;
                } finally {
                    this.loading = false;
                }
            }
        }));
    });
</script>
@endsection