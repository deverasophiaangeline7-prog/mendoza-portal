@extends('layouts.navigation')

@section('title', 'Report Card Menu')

@section('content')
<div class="flex-1 p-8 bg-white min-h-screen relative" x-data="{ passwordModal: {{ $errors->has('current_password') || $errors->has('password') ? 'true' : 'false' }} }">
    <div class="max-w-6xl mx-auto text-center">
        
        <h2 class="text-5xl font-black text-black mb-12 uppercase">
            REPORT CARD
        </h2>

        @if(auth()->user()->role === 'admin')    
            <div class="text-center mb-6">
                <h2 class="text-4xl font-black text-black uppercase tracking-tight">
                    TOTAL STUDENTS: <span class="text-[#b91c1c]">{{ \App\Models\Student::count() }}</span>
                </h2>
            </div>
        @endif

        <div class="text-center mb-12">
            <h3 class="text-3xl font-black text-black mb-12">
                Select Section:
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            @foreach($sections as $section)
                <a href="{{ route('reportcard.show', $section->section_id) }}"
                   class="bg-[#ffb31a] border-[3px] border-black rounded-[40px] py-8 flex flex-col items-center group transition-all shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:translate-y-1 hover:shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                   
                    <span class="text-4xl font-black text-black uppercase mb-1" style="-webkit-text-stroke: 1.5px white;">
                        {{ is_numeric($section->grade_level) ? 'GRADE ' . $section->grade_level : $section->grade_level }}
                    </span>

                    <span class="text-xl font-bold text-black uppercase italic tracking-wider">
                        {{ $section->section_name }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    @if(auth()->user()->role === 'teacher')
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