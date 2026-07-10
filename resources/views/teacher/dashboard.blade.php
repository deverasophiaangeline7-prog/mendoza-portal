@extends('layouts.navigation')

@section('title', 'Teacher Dashboard')

@section('content')
<!-- We wrap the main content and modal in a single div so they share the Alpine data -->
<div class="flex-1 flex flex-col min-h-screen" x-data="{ 
    currentMonth: {{ now()->month - 1 }}, 
    currentYear: {{ now()->year }}, 
    selectedDate: {{ now()->day }},
    monthNames: ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'],
    events: {{ json_encode($eventsData ?? new \stdClass()) }}, 
    passwordModal: {{ $errors->has('current_password') || $errors->has('password') ? 'true' : 'false' }},
    get daysInMonth() { return new Date(this.currentYear, this.currentMonth + 1, 0).getDate(); },
    get startDay() { return new Date(this.currentYear, this.currentMonth, 1).getDay(); },
    get blanks() { return Array.from({ length: this.startDay }); },
    get days() { return Array.from({ length: this.daysInMonth }, (_, i) => i + 1); }
}">

    <main class="flex-1 p-8 bg-white overflow-y-auto">
        
        @if(session('success'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 4000)"
                 x-transition 
                 class="w-full mb-8 flex items-center justify-between bg-[#8cc63f] border-[3px] border-black p-4 rounded-2xl shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                
                <div class="flex items-center">
                    <i class="fa-solid fa-circle-check text-2xl mr-3 text-black"></i>
                    <p class="font-black uppercase tracking-wider text-black">
                        {{ session('success') }}
                    </p>
                </div>
                
                <button @click="show = false" class="text-black hover:scale-125 transition-transform">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-extrabold tracking-tight uppercase">
                Welcome, Teacher 
                @if(auth()->check() && auth()->user()->teacher)
                    {{ auth()->user()->teacher->first_name }} {{ auth()->user()->teacher->last_name }}!
                @else
                    {{ auth()->user()->username ?? 'User' }}!
                @endif
            </h2>
        </div>

        <div class="relative w-full h-80 bg-amber-700 rounded-3xl p-6 shadow-lg border-2 border-black mb-12">
            <div class="bg-blue-100 w-full h-full rounded-2xl border-4 border-amber-600 relative overflow-hidden flex items-center justify-center">
                @if(isset($announcementImages) && $announcementImages->count() > 0)
                    <div class="absolute inset-0">
                        <img src="{{ asset('storage/' . $announcementImages->first()->image_path) }}" class="w-full h-full object-cover">
                    </div>
                @else
                    <div class="text-center text-gray-400 italic font-bold">No Active Announcements</div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-12">
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
                        <!-- Upgraded to native Tailwind Grid so the numbers align perfectly -->
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
                                            'ring-2 ring-red-600 ring-offset-1': events && events[currentYear + '-' + (currentMonth + 1).toString().padStart(2, '0') + '-' + day.toString().padStart(2, '0')]
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
                    
                    <template x-if="events && events[currentYear + '-' + (currentMonth + 1).toString().padStart(2, '0') + '-' + selectedDate.toString().padStart(2, '0')]">
                        <div class="space-y-6">
                            <p class="font-black text-lg text-gray-800 uppercase">Name of the event:</p>
                            <h4 class="text-red-600 text-4xl font-black uppercase leading-tight" 
                                x-text="events[currentYear + '-' + (currentMonth + 1).toString().padStart(2, '0') + '-' + selectedDate.toString().padStart(2, '0')].name"></h4>
                            
                            <p class="font-black text-lg text-gray-800 uppercase">Time:</p>
                            <p class="text-red-600 text-2xl font-black italic" 
                                x-text="events[currentYear + '-' + (currentMonth + 1).toString().padStart(2, '0') + '-' + selectedDate.toString().padStart(2, '0')].time"></p>
                            
                            <div class="mt-4 border-t pt-4 border-dashed border-black">
                                <p class="font-black text-gray-800 uppercase text-sm">Description:</p>
                                <p class="font-normal italic text-red-600" 
                                   x-text="events[currentYear + '-' + (currentMonth + 1).toString().padStart(2, '0') + '-' + selectedDate.toString().padStart(2, '0')].ps"></p>
                            </div>
                        </div>
                    </template>
                    
                    <template x-if="!events || !events[currentYear + '-' + (currentMonth + 1).toString().padStart(2, '0') + '-' + selectedDate.toString().padStart(2, '0')]">
                        <div class="flex flex-col items-center">
                             <i class="fa-solid fa-calendar-xmark text-6xl text-gray-200 mb-4"></i>
                             <p class="text-gray-400 text-2xl font-black italic">No events scheduled for this day.</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </main>

    @if(auth()->check() && auth()->user()->role === 'teacher')
    <div x-show="passwordModal" 
         x-transition:opacity
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" 
         x-cloak>
        
        <div @click.away="passwordModal = false" 
             class="bg-white border-[4px] border-black rounded-[2.5rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] relative"
             x-data="{ currentPassword: '', newPassword: '', confirmPassword: '' }">
            
            <button @click="passwordModal = false" class="absolute top-6 right-8 text-4xl font-black text-gray-400 hover:text-black transition-colors">&times;</button>

            <h2 class="text-3xl font-black italic uppercase tracking-tight mb-8">Change Password</h2>

            <form action="{{ route('user.password.update') }}" method="POST" @submit.prevent="if(newPassword === confirmPassword && currentPassword !== '') $el.submit()">
                @csrf
                @method('PUT')
                
                <div class="space-y-5">
                    <div>
                        <label class="block font-bold uppercase text-black text-sm mb-2 tracking-widest">Current Password</label>
                        <input type="password" name="current_password" x-model="currentPassword" required 
                               class="w-full border-[3px] rounded-2xl px-4 py-3 font-bold focus:outline-none focus:ring-4 transition-all @error('current_password') border-red-500 bg-red-50 focus:ring-red-400 @else border-black focus:ring-green-400 bg-white @enderror">
                        
                        @error('current_password')
                            <p class="text-red-500 font-bold text-sm mt-2 flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-bold uppercase text-black text-sm mb-2 tracking-widest">New Password</label>
                        <input type="password" name="password" x-model="newPassword" required 
                               class="w-full border-[3px] border-black rounded-2xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-green-400 transition-all bg-white">
                    </div>

                    <div>
                        <label class="block font-bold uppercase text-black text-sm mb-2 tracking-widest">Confirm New Password</label>
                        <input type="password" name="password_confirmation" x-model="confirmPassword" required 
                               class="w-full border-[3px] rounded-2xl px-4 py-3 font-bold focus:outline-none focus:ring-4 transition-all"
                               :class="(confirmPassword !== '' && newPassword !== confirmPassword) ? 'border-red-500 focus:ring-red-500 bg-red-50' : 'border-black focus:ring-green-400 bg-white'">
                        
                        <p x-show="confirmPassword !== '' && newPassword !== confirmPassword" x-transition class="text-red-500 font-bold text-sm mt-2 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i> Passwords do not match!
                        </p>
                    </div>
                </div>

                <div class="flex justify-end items-center gap-8 mt-10">
                    <button type="button" @click="passwordModal = false" class="text-black font-black uppercase tracking-widest hover:text-gray-600 transition-colors">
                        Cancel
                    </button>
                    
                    <button type="submit" 
                            :disabled="currentPassword === '' || newPassword === '' || confirmPassword === '' || newPassword !== confirmPassword"
                            :class="(currentPassword === '' || newPassword === '' || confirmPassword === '' || newPassword !== confirmPassword) ? 'opacity-50 cursor-not-allowed' : 'hover:brightness-95 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none'"
                            class="bg-[#22C55E] text-white font-black py-3 px-8 rounded-2xl border-[3px] border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] transition-all flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> UPDATE
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection