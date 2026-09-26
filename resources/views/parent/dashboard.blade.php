@extends('layouts.navigation')

@section('title', 'Parent Dashboard')

@section('content')
<!-- We wrap the main content in a single div so all elements share the Alpine data -->
<div class="flex-1 flex flex-col min-h-screen" x-data="{ 
    currentMonth: {{ now()->month - 1 }}, 
    currentYear: {{ now()->year }}, 
    selectedDate: {{ now()->day }},
    monthNames: ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'],
    events: {{ json_encode($eventsData ?? new \stdClass()) }},
    
    get daysInMonth() { return new Date(this.currentYear, this.currentMonth + 1, 0).getDate(); },
    get startDay() { return new Date(this.currentYear, this.currentMonth, 1).getDay(); },
    get blanks() { return Array.from({ length: this.startDay }); },
    get days() { return Array.from({ length: this.daysInMonth }, (_, i) => i + 1); },
    getDateKey(day) {
        return `${this.currentYear}-${(this.currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
    },

    init() {
            setInterval(() => {
                fetch('/fetch-events?t=' + Date.now())
                    .then(response => response.json())
                    .then(data => {
                        this.events = { ...data }; 
                    })
                    .catch(error => console.error(error));
            }, 1000);
        }
}">

    <main class="flex-1 p-4 md:p-8 bg-white overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl md:text-3xl font-extrabold tracking-tight uppercase">
                Welcome, Parent of 
                @if(auth()->check() && auth()->user()->student)
                   {{ auth()->user()->student->first_name }} {{ auth()->user()->student->middle_name }} {{ auth()->user()->student->last_name }}!
                @elseif(auth()->check() && auth()->user()->parent)
                    {{ auth()->user()->parent->first_name }} {{ auth()->user()->parent->last_name }}!
                @else
                    Student!
                @endif
            </h2>
        </div>

        <!-- NEW UPGRADED CAROUSEL -->
        <div class="relative w-full h-80 bg-amber-700 rounded-3xl p-6 shadow-lg border-2 border-black mb-12"
             x-data="{
                 images: [
                     @if(isset($announcementImages) &&$announcementImages->count() > 0)
                         @foreach($announcementImages as$img)
                             { url: '{{ asset('storage/' . $img->image_path) }}', caption: '{{ addslashes($img->caption) }}' },
                         @endforeach
                     @endif
                 ],
                 currentIndex: 0,
                 get hasImage() { return this.images.length > 0; },
                 init() {
                     setInterval(() => {
                         fetch('{{ route('banner.fetch') }}')
                             .then(response => response.json())
                             .then(data => {
                                 if(data.has_image) {
                                     this.images = data.images;
                                     if (this.currentIndex >= this.images.length) this.currentIndex = 0;
                                 } else {
                                     this.images = [];
                                 }
                             });
                     }, 5000); 
                 },
                 next() {
                     if(this.images.length > 1) {
                         this.currentIndex = (this.currentIndex + 1) % this.images.length;
                     }
                 },
                 prev() {
                     if(this.images.length > 1) {
                         this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
                     }
                 }
             }">
            <div class="bg-blue-100 w-full h-full rounded-2xl border-4 border-amber-600 relative overflow-hidden flex items-center justify-center">
                
                <template x-if="hasImage">
                    <div class="absolute inset-0 transition-opacity duration-500">
                        <img :src="images[currentIndex].url" class="w-full h-full object-cover">
                        
                        <!-- Caption Pill -->
                        <template x-if="images[currentIndex].caption">
                            <div class="absolute bottom-6 left-6 bg-[#5A524D] text-white px-5 py-2.5 rounded-xl font-bold tracking-wide shadow-md" 
                                 x-text="images[currentIndex].caption"></div>
                        </template>

                        <!-- Navigation Arrows -->
                        <template x-if="images.length > 1">
                            <div>
                                <button @click="prev()" class="absolute left-6 top-1/2 -translate-y-1/2 bg-[#F3E6D5]/90 hover:bg-white text-black w-10 h-12 flex items-center justify-center rounded-2xl font-black text-xl shadow-md transition-all">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                                <button @click="next()" class="absolute right-6 top-1/2 -translate-y-1/2 bg-[#F3E6D5]/90 hover:bg-white text-black w-10 h-12 flex items-center justify-center rounded-2xl font-black text-xl shadow-md transition-all">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
                
                <template x-if="!hasImage">
                    <div class="text-center text-gray-400 italic font-bold">No Active Announcements</div>
                </template>
                
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
            <div>
                <h3 class="text-4xl font-black text-center mb-6 tracking-tighter uppercase">SCHOOL CALENDAR</h3>
                <div class="bg-[#b26905] rounded-[40px] p-6 border-[3px] border-black shadow-lg">
                    
                    <div class="flex justify-between items-center mb-4 px-2">
                        <button @click="currentMonth === 0 ? (currentMonth = 11, currentYear--) : currentMonth--" class="text-white text-3xl hover:scale-125 transition">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <div class="text-center">
                            <span class="text-white text-5xl font-black italic tracking-tighter block" style="text-shadow: 2px 2px 0px #800000;" x-text="monthNames[currentMonth]"></span>
                            <span class="text-white text-2xl font-black tracking-tighter" x-text="currentYear"></span>
                        </div>
                        <button @click="currentMonth === 11 ? (currentMonth = 0, currentYear++) : currentMonth++" class="text-white text-3xl hover:scale-125 transition">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>

                    <div class="bg-white rounded-2xl p-4 border-2 border-black">
                        <div class="grid grid-cols-7 gap-2 mb-4">
                            @foreach(['SUN','MON','TUE','WED','THU','FRI','SAT'] as $day)
                                <span class="text-[#b91c1c] text-center font-black text-sm">{{ $day }}</span>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-7 gap-2">
                            <template x-for="(blank, index) in blanks" :key="'blank-'+index">
                                <div class="aspect-square"></div>
                            </template>

                            <template x-for="day in days" :key="'day-'+day">
                                <button @click="selectedDate = day"
                                        class="aspect-square flex items-center justify-center rounded-lg border-2 font-black text-xl transition-all relative"
                                        :class="{
                                            'bg-red-500 text-white border-black shadow-[4px_4px_0px_rgba(0,0,0,1)] scale-110 z-10': selectedDate === day,
                                            'bg-white text-black border-gray-200 hover:bg-orange-100': selectedDate !== day,
                                            'ring-2 ring-red-600 ring-offset-1': events && events[getDateKey(day)]
                                        }"
                                        x-text="day">
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-4xl font-black text-center mb-6 tracking-tighter uppercase">EVENTS</h3>
                
                <div class="bg-white rounded-[40px] p-8 border-[3px] border-black shadow-lg min-h-[420px] flex flex-col justify-center text-center">
                    
                    <template x-if="events && events[getDateKey(selectedDate)]">
                        <div class="space-y-6">
                            <p class="font-black text-lg text-gray-800 uppercase">Name of the event:</p>
                            <h4 class="text-red-600 text-4xl font-black uppercase leading-tight" 
                                x-text="events[getDateKey(selectedDate)].name"></h4>
                            
                            <p class="font-black text-lg text-gray-800 uppercase">Time:</p>
                            <p class="text-red-600 text-2xl font-black italic" 
                                x-text="events[getDateKey(selectedDate)].time || (events[getDateKey(selectedDate)].start_time ? (events[getDateKey(selectedDate)].start_time + ' - ' + events[getDateKey(selectedDate)].end_time) : '')"></p>
                            
                            <div class="mt-4 border-t pt-4 border-dashed border-black">
                                <p class="font-black text-gray-800 uppercase text-sm">Description:</p>
                                <p class="font-normal italic text-red-600" 
                                   x-text="events[getDateKey(selectedDate)].ps"></p>
                            </div>
                        </div>
                    </template>

                    <template x-if="!events || !events[getDateKey(selectedDate)]">
                        <div class="flex flex-col items-center">
                             <i class="fa-solid fa-calendar-xmark text-6xl text-gray-200 mb-4"></i>
                             <p class="text-gray-400 text-2xl font-black italic">
                                No events scheduled for this day.
                            </p>
                        </div>
                    </template>

                </div>
            </div>
        </div>
    </main>
</div>
@endsection