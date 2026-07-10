@extends('layouts.navigation')

@section('title', 'Attendance')

@section('content')
<div class="flex-1 p-8 bg-white min-h-screen relative" x-data="{ 
    openModal: false, 
    isManaging: false,
    isPublishing: false,
    passwordModal: false
}">
    <div class="max-w-6xl mx-auto">
        
        <div class="relative flex justify-center items-center mb-8">
            <div class="text-center">
                <h2 class="text-4xl font-black text-black uppercase tracking-tight">Attendance</h2>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-8">
            @php
                $grades = [
                    ['id' => 'nursery',     'level' => 'NURSERY',     'name' => 'St. Mary'],
                    ['id' => 'kinder',      'level' => 'KINDER',      'name' => 'St. Bridget'],
                    ['id' => 'preparatory', 'level' => 'PREPARATORY', 'name' => 'St. Augustine'],
                    ['id' => 'grade-1',     'level' => 'GRADE 1',     'name' => 'Faith'],
                    ['id' => 'grade-2',     'level' => 'GRADE 2',     'name' => 'Hope'],
                    ['id' => 'grade-3',     'level' => 'GRADE 3',     'name' => 'Love'],
                    ['id' => 'grade-4',     'level' => 'GRADE 4',     'name' => 'Grace'],
                    ['id' => 'grade-5',     'level' => 'GRADE 5',     'name' => 'Light'],
                    ['id' => 'grade-6',     'level' => 'GRADE 6',     'name' => 'Wisdom'],
                ];
            @endphp

            @foreach($grades as $grade)
                <button type="button" 
                    onclick="window.location.href='{{ route('attendance.show', ['grade' => $grade['id']]) }}'"
                    class="border-2 border-black rounded-[40px] py-6 flex flex-col items-center group transition-all active:scale-95 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] relative"
                    :class="isManaging ? 'bg-green-100 border-green-600' : 'bg-[#e68a2d]'">
                    
                    <div x-show="isManaging" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center border-2 border-black" x-cloak>
                        <i class="fa-solid fa-xmark"></i>
                    </div>

                    <span class="text-4xl font-black text-black group-hover:-translate-y-1 group-hover:text-orange-500 transition-transform" 
                        style="-webkit-text-stroke: 1.5px white;">
                        {{ $grade['level'] }}
                    </span>
                    <span class="text-xl font-medium text-black group-hover:-translate-y-1 transition-transform">
                        {{ $grade['name'] }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    <div x-show="passwordModal" 
         x-transition:opacity
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" 
         x-cloak>
        
        <div @click.away="passwordModal = false" 
             class="bg-white border-[4px] border-black rounded-[2.5rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] relative">
            
            <button @click="passwordModal = false" class="absolute top-6 right-8 text-4xl font-black text-gray-400 hover:text-black transition-colors">&times;</button>

            <h2 class="text-3xl font-black italic uppercase tracking-tight mb-8">Change Password</h2>

            <form action="{{ route('user.password.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="space-y-5">
                    <div>
                        <label class="block font-bold uppercase text-black text-sm mb-2 tracking-widest">Current Password</label>
                        <input type="password" name="current_password" required 
                               class="w-full border-[3px] border-black rounded-2xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-green-400 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase text-black text-sm mb-2 tracking-widest">New Password</label>
                        <input type="password" name="password" required 
                               class="w-full border-[3px] border-black rounded-2xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-green-400 transition-all">
                    </div>

                    <div>
                        <label class="block font-bold uppercase text-black text-sm mb-2 tracking-widest">Confirm New Password</label>
                        <input type="password" name="password_confirmation" required 
                               class="w-full border-[3px] border-black rounded-2xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-green-400 transition-all">
                    </div>
                </div>

                <div class="flex justify-end items-center gap-8 mt-10">
                    <button type="button" @click="passwordModal = false" class="text-black font-black uppercase tracking-widest hover:text-gray-600 transition-colors">
                        Cancel
                    </button>
                    
                    <button type="submit" class="bg-[#22C55E] text-white font-black py-3 px-8 rounded-2xl border-[3px] border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] hover:brightness-95 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> UPDATE
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection