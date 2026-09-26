@extends('layouts.navigation')

@section('title', 'Teacher Profile')

@section('content')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 8px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
</style>

<div class="flex-1 flex flex-col h-full overflow-hidden relative" x-data="{ passwordModal: false, photoModal: false }">
    
    <main class="flex-1 bg-white p-10 relative overflow-y-hidden custom-scrollbar">
        
        <div class="max-w-4xl mx-auto w-full mt-4">
            
            <div class="flex flex-col md:flex-row items-center justify-between gap-8 mb-12 pl-4">
                
                <div class="flex flex-col md:flex-row items-center gap-8">
                    
                    {{-- Profile Picture Container with Edit Button --}}
                    <div class="relative flex-shrink-0 mb-4 md:mb-0">
                        <div class="w-44 h-44 bg-amber-700 border-[4px] border-black rounded-[2rem] shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] overflow-hidden flex items-center justify-center rotate-[-2deg]">
                        @if(auth()->user()->profile_photo_path)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}" 
                                 class="w-full h-full object-cover">
                        @else
                            <i class="fa-solid fa-user-tie text-7xl text-black"></i>
                        @endif
                        </div>
                        <button @click="photoModal = true" class="absolute -bottom-2 -right-2 bg-white text-black border-[3px] border-black rounded-full w-12 h-12 flex items-center justify-center shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-gray-100 active:translate-y-[2px] active:translate-x-[2px] active:shadow-none transition-all z-10 text-xl">
                            <i class="fa-solid fa-camera"></i>
                        </button>
                    </div>

                    <div class="text-center md:text-left">
                        <h2 class="text-6xl font-black uppercase italic tracking-tighter leading-none text-black">
                            {{ auth()->user()->name }}
                        </h2>
                        <div class="font-bold text-gray-400 mt-4 italic uppercase tracking-widest text-2xl">
                            FACULTY MEMBER
                        </div>
                    </div>
                </div>

                <button @click="passwordModal = true" class="bg-[#111] text-white font-black uppercase tracking-widest px-6 py-3 rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-gray-800 active:translate-y-1 active:shadow-none transition-all flex items-center gap-2 flex-shrink-0">
                    <i class="fa-solid fa-key text-yellow-400"></i> Change Password
                </button>

            </div>

            <div class="bg-white border-[5px] border-black p-10 rounded-[3rem] shadow-[20px_20px_0px_0px_rgba(0,0,0,1)] mb-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-20 gap-y-12 min-w-0">
                    
                    <div class="space-y-10 min-w-0">
                        <div>
                            <label class="block font-black text-red-600 uppercase text-[11px] tracking-[0.25em] mb-3">Teacher ID / Username</label>
                            <p class="text-3xl font-black uppercase italic break-all">
                                {{ auth()->user()->username ?? auth()->user()->lrn ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <label class="block font-black text-red-600 uppercase text-[11px] tracking-[0.25em] mb-3">Email Address</label>
                            <p class="text-3xl font-black uppercase italic break-all">
                                {{ auth()->user()->email ?? 'NOT ASSIGNED' }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-10 min-w-0">
                        <div>
                            <label class="block font-black text-red-600 uppercase text-[11px] tracking-[0.25em] mb-3">Account Status</label>
                            <p class="text-3xl font-black uppercase italic break-all text-green-600">
                                {{ auth()->user()->status ?? 'ACTIVE' }}
                            </p>
                        </div>

                        <div>
                            <label class="block font-black text-red-600 uppercase text-[11px] tracking-[0.25em] mb-3">Account Role</label>
                            <p class="text-3xl font-black uppercase italic break-all">
                                {{ auth()->user()->role ?? 'TEACHER' }}
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    {{-- PHOTO UPDATE MODAL --}}
    <div x-show="photoModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak>
        <div @click.away="photoModal = false" class="bg-white border-4 border-black rounded-[2rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] relative">
            
            <div class="flex justify-between items-start mb-6">
                <h2 class="text-3xl font-black uppercase text-black italic">Update Photo</h2>
                <button @click="photoModal = false" class="text-gray-400 hover:text-red-600 text-3xl transition-colors"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="space-y-5 mb-8">
                    <div>
                        <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Select New Image</label>
                        <input type="file" name="profile_photo" accept="image/jpeg,image/png,image/jpg" required class="w-full border-2 border-black rounded-xl p-2 font-bold focus:outline-none focus:ring-4 focus:ring-red-400 bg-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-2 file:border-black file:text-sm file:font-black file:bg-[#ffb31a] file:text-black hover:file:bg-[#e68a2d] file:transition-colors cursor-pointer">
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" @click="photoModal = false" class="font-bold text-gray-500 hover:text-black uppercase tracking-wider px-4 transition-colors">Cancel</button>
                    
                    <button type="submit" class="bg-[#34C759] text-white font-black uppercase tracking-wider px-6 py-3 rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-green-600 active:translate-y-[2px] active:translate-x-[2px] active:shadow-none transition-all flex items-center">
                        <i class="fa-solid fa-upload mr-2"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- PASSWORD UPDATE MODAL (With Eye Toggles & Corrected Route) --}}
    <div x-show="passwordModal" 
         x-data="{ currentPassword: '', newPassword: '', confirmPassword: '' }"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak>
        
        <div @click.away="passwordModal = false; currentPassword = ''; newPassword = ''; confirmPassword = ''" class="bg-white border-4 border-black rounded-[2rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] relative">
            
            <button @click="passwordModal = false; currentPassword = ''; newPassword = ''; confirmPassword = ''" class="absolute top-4 right-6 text-3xl font-black text-gray-400 hover:text-red-600 transition-colors">&times;</button>
            
            <h2 class="text-3xl font-black mb-6 uppercase tracking-tight text-center text-black italic">Change Password</h2>
            
            <!-- FIXED ROUTE: user.password.update -->
            <form action="{{ route('user.password.update') }}" method="POST" 
                  @submit.prevent="if(newPassword === confirmPassword && currentPassword !== newPassword) $el.submit()">
                @csrf
                @method('PUT')
                
                <div class="space-y-5 mb-8">
                    <div x-data="{ show: false }">
                        <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Current Password</label>
                        <div class="relative flex items-center">
                            <input :type="show ? 'text' : 'password'" name="current_password" x-model="currentPassword" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-red-400 pr-10">
                            <button type="button" @click="show = !show" class="absolute right-3 text-gray-400 hover:text-black">
                                <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div x-data="{ show: false }">
                        <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">New Password</label>
                        <div class="relative flex items-center">
                            <input :type="show ? 'text' : 'password'" name="password" x-model="newPassword" required 
                                   class="w-full border-2 rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 transition-colors pr-10"
                                   :class="(currentPassword !== '' && newPassword !== '' && currentPassword === newPassword) ? 'border-red-500 focus:ring-red-500 bg-red-50' : 'border-black focus:ring-red-400 bg-white'">
                            <button type="button" @click="show = !show" class="absolute right-3 text-gray-400 hover:text-black">
                                <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        
                        <p x-show="currentPassword !== '' && newPassword !== '' && currentPassword === newPassword" 
                           x-transition 
                           class="text-red-600 font-bold text-sm mt-2 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i> Cannot be the same as current password
                        </p>
                    </div>

                    <div x-data="{ show: false }">
                        <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Confirm New Password</label>
                        <div class="relative flex items-center">
                            <input :type="show ? 'text' : 'password'" name="password_confirmation" x-model="confirmPassword" required 
                                   class="w-full border-2 rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 transition-colors pr-10"
                                   :class="(confirmPassword !== '' && newPassword !== confirmPassword) ? 'border-red-500 focus:ring-red-500 bg-red-50' : 'border-black focus:ring-red-400 bg-white'">
                            <button type="button" @click="show = !show" class="absolute right-3 text-gray-400 hover:text-black">
                                <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        
                        <p x-show="confirmPassword !== '' && newPassword !== confirmPassword" 
                           x-transition 
                           class="text-red-600 font-bold text-sm mt-2 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i> Passwords do not match
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="submit" 
                            :disabled="(confirmPassword !== '' && newPassword !== confirmPassword) || (currentPassword !== '' && newPassword !== '' && currentPassword === newPassword)"
                            :class="((confirmPassword !== '' && newPassword !== confirmPassword) || (currentPassword !== '' && newPassword !== '' && currentPassword === newPassword)) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-600 active:translate-y-1 active:shadow-none'"
                            class="bg-[#34C759] text-white font-black uppercase tracking-wider px-6 py-4 rounded-xl border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] transition-all flex items-center justify-center text-xl">
                        <i class="fa-solid fa-check mr-2"></i> Update Password
                    </button>
                    <button type="button" @click="passwordModal = false; currentPassword = ''; newPassword = ''; confirmPassword = ''" class="w-full bg-gray-100 text-black font-black py-4 rounded-xl border-[3px] border-black hover:bg-gray-200 transition-all text-lg uppercase">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection