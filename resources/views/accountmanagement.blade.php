@extends('layouts.navigation')

@section('title', 'Account Management')

@section('content')
<div class="flex-1 bg-white relative p-4 md:p-8 flex flex-col items-center min-h-screen w-full pt-16 md:pt-8"
     x-data="{ 
        finalizeModal: {{ $errors->has('admin_password') ? 'true' : 'false' }}, 
        passwordModal: false, 
        termScheduleModal: {{ $errors->hasAny(['term1_start', 'term1_end', 'term2_start', 'term2_end', 'term3_start', 'term3_end']) ? 'true' : 'false' }} 
     }">

    <div class="absolute top-4 md:top-20 w-full max-w-md z-50 px-4">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border-[3px] border-black text-green-800 font-bold rounded-lg shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] flex items-center justify-between">
                <span><i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}</span>
                <button @click="$el.parentElement.remove()" class="ml-4 hover:text-green-900">&times;</button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border-[3px] border-black text-red-800 font-bold rounded-lg shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] flex items-center justify-between">
                <span><i class="fa-solid fa-circle-xmark mr-2"></i> {{ session('error') }}</span>
                <button @click="$el.parentElement.remove()" class="ml-4 hover:text-red-900">&times;</button>
            </div>
        @endif
    </div>

    <!-- Responsive Header & Dropdown Section -->
    <div class="w-full max-w-4xl flex flex-col md:flex-row items-center justify-between mb-8 md:mb-12 gap-6 relative z-30">
        
        <div class="text-center md:text-left order-2 md:order-1">
            <h2 class="text-4xl md:text-6xl font-black text-gray-900 mb-2 tracking-tight leading-none">Account Management</h2>
            <h3 class="text-2xl md:text-4xl font-bold text-red-700 uppercase tracking-widest" style="text-shadow: 2px 2px 0px #000;">
                SY {{ $activeYear ? $activeYear->school_year : 'N/A' }}
            </h3>
        </div>

        <div class="relative order-1 md:order-2 self-end md:self-auto" x-data="{ syMenu: false }" @click.away="syMenu = false">
            <button @click="syMenu = !syMenu" class="inline-flex items-center border-[3px] border-black rounded-lg px-4 py-2 font-bold bg-white shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-gray-50 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all focus:outline-none text-sm md:text-base">
                <span>SY {{ $activeYear ? $activeYear->school_year : 'N/A' }}</span>
                <i class="fa-solid fa-chevron-down ml-3 transition-transform duration-200" :class="syMenu ? 'rotate-180' : ''"></i>
            </button>

            <div x-show="syMenu" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-cloak
                 class="absolute right-0 mt-2 w-48 bg-white border-[3px] border-black rounded-xl overflow-hidden shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] text-left flex flex-col">
                
                <div class="px-4 py-2 bg-gray-100 text-xs font-black uppercase text-gray-500 border-b-[3px] border-black">Past School Years</div>
                
                @if(isset($archivedYears) && $archivedYears->count() > 0)
                    @foreach($archivedYears as $year)
                        <a href="{{ route('archives.reportcards', $year->id) }}" class="block px-4 py-3 font-bold text-black hover:bg-yellow-100 border-b-[3px] border-black last:border-b-0 transition-colors">
                            SY {{ $year->school_year }}
                        </a>
                    @endforeach
                @else
                    <div class="px-4 py-3 font-bold text-gray-400 text-sm">No past years yet.</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Button Actions -->
    <div class="w-full max-w-4xl mx-auto flex flex-col items-center">
        
        <div class="flex flex-col md:flex-row justify-center gap-4 md:gap-8 w-full mb-8">
            <div class="relative w-full md:w-auto" x-data="{ listOpen: false }" @click.away="listOpen = false">
                <button @click="listOpen = !listOpen" class="w-full md:w-auto justify-center bg-[#e68a2d] hover:bg-yellow-500 text-black text-lg md:text-2xl font-black py-4 md:py-5 px-8 md:px-12 rounded-full border-[3px] border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] flex items-center transition-all active:translate-x-[2px] active:translate-y-[2px] active:shadow-none">
                    List of accounts
                    <i class="fa-solid fa-caret-down ml-4 transition-transform duration-300" :class="listOpen ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="listOpen" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-cloak 
                     class="absolute top-full mt-4 left-0 w-full bg-white border-[3px] border-black rounded-2xl overflow-hidden shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] z-20">
                    <a href="{{ route('teacher.list') }}" class="block px-6 py-4 text-lg md:text-xl font-bold border-b-[3px] border-black hover:bg-yellow-100 transition-colors">
                        Teacher Accounts
                    </a>
                    <a href="{{ route('parent.list') }}" class="block px-6 py-4 text-lg md:text-xl font-bold hover:bg-yellow-100 transition-colors">
                        Parent Accounts
                    </a>
                </div>
            </div>

            <div class="relative w-full md:w-auto" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open" class="w-full md:w-auto justify-center bg-[#e68a2d] hover:bg-yellow-500 text-black text-lg md:text-2xl font-black py-4 md:py-5 px-8 md:px-12 rounded-full border-[3px] border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] flex items-center transition-all active:translate-x-[2px] active:translate-y-[2px] active:shadow-none">
                    Create an account
                    <i class="fa-solid fa-caret-down ml-4 transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-cloak 
                     class="absolute top-full mt-4 left-0 w-full bg-white border-[3px] border-black rounded-2xl overflow-hidden shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] z-20">
                    <a href="{{ route('teacher.create') }}" class="block px-6 py-4 text-lg md:text-xl font-bold border-b-[3px] border-black hover:bg-yellow-100 transition-colors">Teacher Account</a>
                    <a href="{{ route('parent.create') }}" class="block px-6 py-4 text-lg md:text-xl font-bold hover:bg-yellow-100 transition-colors">Parent Account</a>
                </div>
            </div>
        </div>

        <!-- 4 Grid Buttons -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-8 max-w-4xl w-full mb-12">
            
            <a href="{{ route('admin.audit_logs') }}" 
               class="w-full bg-blue-500 hover:bg-blue-600 text-black text-lg md:text-2xl font-black py-4 md:py-5 px-4 md:px-8 rounded-full border-[3px] border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all flex items-center justify-center text-center">
                View Activity Logs
            </a>
        
            <button @click="finalizeModal = true" class="w-full bg-green-500 hover:bg-green-600 text-black text-lg md:text-2xl font-black py-4 md:py-5 px-4 md:px-8 rounded-full border-[3px] border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all flex items-center justify-center text-center">
                Finalize School Year
            </button>

            <button @click="passwordModal = true" class="w-full bg-[#ff3366] hover:bg-[#ff1a53] text-black font-black text-lg md:text-2xl py-4 md:py-5 px-4 md:px-8 rounded-full border-[3px] border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all flex items-center justify-center gap-3">
                Change User Password <i class="fa-solid fa-key ml-1 md:ml-2"></i>
            </button>

            <button @click="termScheduleModal = true" class="w-full bg-purple-400 hover:bg-purple-500 text-black font-black text-lg md:text-2xl py-4 md:py-5 px-4 md:px-8 rounded-full border-[3px] border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all flex items-center justify-center gap-3">
                Term Schedule <i class="fa-solid fa-calendar-days ml-1 md:ml-2"></i>
            </button>
        </div>

    </div>

    <!-- Modals Section remains unchanged below this point -->
    <div x-show="finalizeModal" 
         x-transition:opacity
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" 
         x-cloak>
        <div class="bg-white border-4 border-black rounded-[2rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] relative">
            
            <button @click="finalizeModal = false" class="absolute top-4 right-6 text-3xl font-black text-gray-400 hover:text-black transition-colors">&times;</button>

            <div class="text-center">
                <i class="fa-solid fa-triangle-exclamation text-6xl text-red-600 mb-4 drop-shadow-md"></i>
                <h2 class="text-3xl font-black mb-2 uppercase tracking-tight">Are you sure?</h2>
                <p class="text-lg font-bold text-gray-600 mb-6 leading-tight">
                    Finalizing will archive all records for <span class="text-red-600 underline">SY {{ $activeYear ? $activeYear->school_year : 'N/A' }}</span>. This action cannot be undone.
                </p>

                <form action="{{ route('admin.finalize_year') }}" method="POST">
                    @csrf
                    
                    <div class="mb-6 text-left">
                        <label for="admin_password" class="block text-sm font-black uppercase tracking-wider text-black mb-2">
                            Enter Admin Password to Confirm:
                        </label>
                        <input type="password" name="admin_password" id="admin_password" required
                               placeholder="********"
                               class="w-full border-[3px] border-black rounded-lg px-4 py-3 font-bold text-lg focus:outline-none shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] focus:ring-4 focus:ring-red-400/50 transition-all">
                        
                        @error('admin_password')
                            <p class="text-red-600 text-sm font-bold mt-2"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-3">
                        <button type="submit" class="w-full bg-green-500 text-black font-black py-4 rounded-xl border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-green-600 active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all text-xl uppercase">
                            Yes, Finalize Year
                        </button>
                        <button type="button" @click="finalizeModal = false" class="w-full bg-gray-100 text-black font-black py-4 rounded-xl border-[3px] border-black hover:bg-gray-200 transition-all text-lg">
                            CANCEL
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div x-show="passwordModal" 
         x-data="{ userId: '', newPassword: '', confirmPassword: '' }"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" 
         x-cloak>
        <div @click.away="passwordModal = false; userId = ''; newPassword = ''; confirmPassword = ''" class="bg-white border-4 border-black rounded-[2rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] relative">
            
            <button @click="passwordModal = false; userId = ''; newPassword = ''; confirmPassword = ''" class="absolute top-4 right-6 text-3xl font-black text-gray-400 hover:text-black transition-colors">&times;</button>

            <h2 class="text-3xl font-black mb-6 uppercase tracking-tight text-center text-black italic">Reset Password</h2>

            <form action="{{ route('admin.password.reset') }}" method="POST" 
                  @submit.prevent="if(newPassword === confirmPassword) $el.submit()">
                @csrf
                @method('PUT')
                
                <div class="space-y-5 mb-8">
                    <div>
                        <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">
                            User ID <span class="text-red-600">*</span>
                        </label>
                        <input type="text" 
                            name="login_id" 
                            class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-[#ff3366] transition-colors @error('login_id') border-red-500 bg-red-50 @else bg-white @enderror" 
                            value="{{ old('login_id') }}" 
                            required>
                            
                        @error('login_id') 
                            <p class="text-red-600 font-bold text-sm mt-2 flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">New Password</label>
                        <input type="password" name="password" x-model="newPassword" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-[#ff3366]">
                        @error('password')
                            <p class="text-red-600 font-bold text-sm mt-2 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Confirm New Password</label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" x-model="confirmPassword" required 
                                   class="w-full border-2 rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 transition-colors"
                                   :class="(confirmPassword !== '' && newPassword !== confirmPassword) ? 'border-red-500 focus:ring-red-500 bg-red-50' : 'border-black focus:ring-[#ff3366] bg-white'">
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
                            :disabled="confirmPassword !== '' && newPassword !== confirmPassword"
                            :class="(confirmPassword !== '' && newPassword !== confirmPassword) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#ff1a53] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none'"
                            class="w-full bg-[#ff3366] text-black font-black py-4 rounded-xl border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] transition-all text-xl uppercase">
                        <i class="fa-solid fa-key mr-2"></i> Reset Password
                    </button>
                    <button type="button" @click="passwordModal = false; userId = ''; newPassword = ''; confirmPassword = ''" class="w-full bg-gray-100 text-black font-black py-4 rounded-xl border-[3px] border-black hover:bg-gray-200 transition-all text-lg font-bold">
                        CANCEL
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- START: Term Schedule Modal -->
    <div x-show="termScheduleModal" 
         x-transition:opacity
         class="fixed inset-0 z-[100] flex items-start md:items-center justify-center p-4 bg-black/80 backdrop-blur-sm overflow-y-auto" 
         x-cloak>
        
        <!-- Added mt-12 md:my-8 so it doesn't touch the very top edge of the phone -->
        <div @click.away="termScheduleModal = false" class="bg-purple-400 border-[4px] border-black rounded-[2rem] p-5 md:p-8 max-w-4xl w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] relative mt-16 mb-8 md:my-8 flex-shrink-0">
            
            <button @click="termScheduleModal = false" class="absolute top-3 md:top-4 right-5 md:right-6 text-4xl md:text-5xl font-black text-black hover:text-gray-700 transition-colors leading-none">&times;</button>

            <div class="flex items-center justify-between mb-6 md:mb-8 border-b-[4px] border-black pb-4 pr-6 md:pr-8 mt-4 md:mt-0">
                <div>
                    <h3 class="text-2xl md:text-3xl font-black uppercase tracking-widest text-black leading-tight">Academic Term Schedule</h3>
                    <p class="font-bold text-gray-800 text-sm md:text-lg mt-1 md:mt-2">Set grading periods to automatically lock/unlock teacher grade sheets.</p>
                </div>
                <i class="fa-solid fa-calendar-days text-5xl text-black hidden sm:block"></i>
            </div>

            <form action="{{ route('admin.terms.update') }}" method="POST">
                @csrf
                @method('PUT') 

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Term 1 -->
                    <div class="bg-white border-[3px] border-black p-4 md:p-5 rounded-[1.5rem] shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                        <h4 class="text-xl font-black uppercase mb-4 text-center bg-gray-100 border-[3px] border-black rounded-xl py-2">Term 1</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold uppercase mb-1 tracking-wider">Start Date</label>
                                <input type="date" name="term1_start" required
                                       value="{{ old('term1_start', $activeYear ? $activeYear->term1_start : '') }}" 
                                       class="w-full border-[3px] border-black rounded-lg px-3 py-2 font-bold focus:outline-none focus:ring-4 focus:ring-blue-400/50 transition-all shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] @error('term1_start') border-red-500 bg-red-50 @else bg-white @enderror">
                                @error('term1_start') <p class="text-red-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold uppercase mb-1 tracking-wider">End Date</label>
                                <input type="date" name="term1_end" required
                                       value="{{ old('term1_end', $activeYear ? $activeYear->term1_end : '') }}" 
                                       class="w-full border-[3px] border-black rounded-lg px-3 py-2 font-bold focus:outline-none focus:ring-4 focus:ring-blue-400/50 transition-all shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] @error('term1_end') border-red-500 bg-red-50 @else bg-white @enderror">
                                @error('term1_end') <p class="text-red-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Term 2 -->
                    <div class="bg-white border-[3px] border-black p-4 md:p-5 rounded-[1.5rem] shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                        <h4 class="text-xl font-black uppercase mb-4 text-center bg-gray-100 border-[3px] border-black rounded-xl py-2">Term 2</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold uppercase mb-1 tracking-wider">Start Date</label>
                                <input type="date" name="term2_start" required
                                       value="{{ old('term2_start', $activeYear ? $activeYear->term2_start : '') }}" 
                                       class="w-full border-[3px] border-black rounded-lg px-3 py-2 font-bold focus:outline-none focus:ring-4 focus:ring-blue-400/50 transition-all shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] @error('term2_start') border-red-500 bg-red-50 @else bg-white @enderror">
                                @error('term2_start') <p class="text-red-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold uppercase mb-1 tracking-wider">End Date</label>
                                <input type="date" name="term2_end" required
                                       value="{{ old('term2_end', $activeYear ? $activeYear->term2_end : '') }}" 
                                       class="w-full border-[3px] border-black rounded-lg px-3 py-2 font-bold focus:outline-none focus:ring-4 focus:ring-blue-400/50 transition-all shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] @error('term2_end') border-red-500 bg-red-50 @else bg-white @enderror">
                                @error('term2_end') <p class="text-red-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Term 3 -->
                    <div class="bg-white border-[3px] border-black p-4 md:p-5 rounded-[1.5rem] shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                        <h4 class="text-xl font-black uppercase mb-4 text-center bg-gray-100 border-[3px] border-black rounded-xl py-2">Term 3</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold uppercase mb-1 tracking-wider">Start Date</label>
                                <input type="date" name="term3_start" required
                                       value="{{ old('term3_start', $activeYear ? $activeYear->term3_start : '') }}" 
                                       class="w-full border-[3px] border-black rounded-lg px-3 py-2 font-bold focus:outline-none focus:ring-4 focus:ring-blue-400/50 transition-all shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] @error('term3_start') border-red-500 bg-red-50 @else bg-white @enderror">
                                @error('term3_start') <p class="text-red-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold uppercase mb-1 tracking-wider">End Date</label>
                                <input type="date" name="term3_end" required
                                       value="{{ old('term3_end', $activeYear ? $activeYear->term3_end : '') }}" 
                                       class="w-full border-[3px] border-black rounded-lg px-3 py-2 font-bold focus:outline-none focus:ring-4 focus:ring-blue-400/50 transition-all shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] @error('term3_end') border-red-500 bg-red-50 @else bg-white @enderror">
                                @error('term3_end') <p class="text-red-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="submit" class="w-full bg-[#00e5ff] hover:bg-[#00cce6] text-black text-lg md:text-2xl font-black py-4 md:py-5 rounded-full border-[3px] border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all flex items-center justify-center gap-2 md:gap-3">
                        Save Term Schedule <i class="fa-solid fa-floppy-disk"></i>
                    </button>
                    <button type="button" @click="termScheduleModal = false" class="w-full bg-white text-black text-lg md:text-xl font-black py-3 md:py-4 rounded-full border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-gray-100 active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all">
                        CANCEL
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Term Schedule Modal -->

</div>
@endsection