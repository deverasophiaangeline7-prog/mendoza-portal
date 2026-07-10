@extends('layouts.navigation')

@section('title', 'Student Profile')

@section('content')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 8px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
</style>

<div class="flex-1 flex flex-col h-full overflow-hidden relative" x-data="{ passwordModal: false }">
    
    <main class="flex-1 bg-white p-10 relative overflow-y-auto custom-scrollbar">
        
        <div class="max-w-4xl mx-auto w-full mt-4">
            
            <div class="flex flex-col md:flex-row items-center justify-between gap-8 mb-12 pl-4">
                
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <div class="w-44 h-44 bg-amber-700 border-[4px] border-black rounded-[2rem] shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] overflow-hidden flex items-center justify-center flex-shrink-0 rotate-[-2deg]">
                    @if($student->user && $student->user->profile_photo_path)
                        <img src="{{ asset('storage/' . $student->user->profile_photo_path) }}" 
                            class="w-full h-full object-cover">
                    @else
                        <i class="fa-solid fa-user-graduate text-7xl text-black"></i>
                    @endif
                    </div>

                    <div class="text-center md:text-left">
                        <h2 class="text-6xl font-black uppercase italic tracking-tighter leading-none text-black">
                            {{ $student->last_name }}, {{ $student->first_name }} {{ $student->ext_name ?? '' }}
                        </h2>
                        <div class="font-bold text-gray-400 mt-4 italic uppercase tracking-widest text-2xl">
                            {{ $student->grade_level }} - {{ $student->section->section_name ?? 'NO SECTION' }}
                        </div>
                    </div>
                </div>

                <button @click="passwordModal = true" class="bg-[#111] text-white font-black uppercase tracking-widest px-6 py-3 rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-gray-800 active:translate-y-1 active:shadow-none transition-all flex items-center gap-2 flex-shrink-0">
                    <i class="fa-solid fa-key text-yellow-400"></i> Change Password
                </button>

            </div>

            <div class="bg-white border-[5px] border-black p-10 rounded-[3rem] shadow-[20px_20px_0px_0px_rgba(0,0,0,1)] mb-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-20 gap-y-12">
                    
                    <div class="space-y-10">
                        <div>
                            <label class="block font-black text-red-600 uppercase text-[11px] tracking-[0.25em] mb-3">Learner Reference Number (LRN)</label>
                            <p class="text-3xl font-black uppercase italic">{{ $student->lrn }}</p>
                        </div>

                        <div>
                            <label class="block font-black text-red-600 uppercase text-[11px] tracking-[0.25em] mb-3">Class Adviser</label>
                            <p class="text-3xl font-black uppercase italic">
                                @if($student->section?->teacher?->teacher)
                                    {{ $student->section->teacher->teacher->first_name }} {{ $student->section->teacher->teacher->last_name }}
                                @else
                                    NOT ASSIGNED
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="space-y-10">
                        <div>
                            <label class="block font-black text-red-600 uppercase text-[11px] tracking-[0.25em] mb-3">Biological Sex</label>
                            <p class="text-3xl font-black uppercase italic">{{ $student->gender }}</p>
                        </div>

                        <div>
                            <label class="block font-black text-red-600 uppercase text-[11px] tracking-[0.25em] mb-3">Date of Birth</label>
                            <p class="text-3xl font-black uppercase italic">{{ \Carbon\Carbon::parse($student->birth_date)->format('F d, Y') }}</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <div x-show="passwordModal" 
         x-data="{ currentPassword: '', newPassword: '', confirmPassword: '' }"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak>
        
        <div @click.away="passwordModal = false; currentPassword = ''; newPassword = ''; confirmPassword = ''" class="bg-white border-4 border-black rounded-[2rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] relative">
            
            <div class="flex justify-between items-start mb-6">
                <h2 class="text-3xl font-black uppercase text-black italic">Change Password</h2>
                <button @click="passwordModal = false; currentPassword = ''; newPassword = ''; confirmPassword = ''" class="text-gray-400 hover:text-red-600 text-3xl transition-colors"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form action="{{ route('password.update') }}" method="POST" 
                  @submit.prevent="if(newPassword === confirmPassword && currentPassword !== newPassword) $el.submit()">
                @csrf
                @method('PUT')
                
                <div class="space-y-5 mb-8">
                    <div>
                        <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Current Password</label>
                        <input type="password" name="current_password" x-model="currentPassword" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-red-400">
                    </div>
                    
                    <div>
                        <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">New Password</label>
                        <div class="relative">
                            <input type="password" name="password" x-model="newPassword" required 
                                   class="w-full border-2 rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 transition-colors"
                                   :class="(currentPassword !== '' && newPassword !== '' && currentPassword === newPassword) ? 'border-red-500 focus:ring-red-500 bg-red-50' : 'border-black focus:ring-red-400 bg-white'">
                        </div>
                        
                        <p x-show="currentPassword !== '' && newPassword !== '' && currentPassword === newPassword" 
                           x-transition 
                           class="text-red-600 font-bold text-sm mt-2 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i> Cannot be the same as current password
                        </p>
                    </div>

                    <div>
                        <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Confirm New Password</label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" x-model="confirmPassword" required 
                                   class="w-full border-2 rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 transition-colors"
                                   :class="(confirmPassword !== '' && newPassword !== confirmPassword) ? 'border-red-500 focus:ring-red-500 bg-red-50' : 'border-black focus:ring-red-400 bg-white'">
                        </div>
                        
                        <p x-show="confirmPassword !== '' && newPassword !== confirmPassword" 
                           x-transition 
                           class="text-red-600 font-bold text-sm mt-2 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i> Passwords do not match
                        </p>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" @click="passwordModal = false; currentPassword = ''; newPassword = ''; confirmPassword = ''" class="font-bold text-gray-500 hover:text-black uppercase tracking-wider px-4 transition-colors">Cancel</button>
                    
                    <button type="submit" 
                            :disabled="(confirmPassword !== '' && newPassword !== confirmPassword) || (currentPassword !== '' && newPassword !== '' && currentPassword === newPassword)"
                            :class="((confirmPassword !== '' && newPassword !== confirmPassword) || (currentPassword !== '' && newPassword !== '' && currentPassword === newPassword)) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-600 active:translate-y-1 active:shadow-none'"
                            class="bg-[#34C759] text-white font-black uppercase tracking-wider px-6 py-3 rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] transition-all flex items-center">
                        <i class="fa-solid fa-check mr-2"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection