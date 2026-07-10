@extends('layouts.navigation')

@section('title', 'Account Management')

@section('content')
<div class="flex-1 bg-white relative p-8 flex flex-col items-center justify-center min-h-screen w-full"
     x-data="{ finalizeModal: {{ $errors->has('admin_password') ? 'true' : 'false' }}, passwordModal: false }">

    <div class="absolute top-20 w-full max-w-md z-50">
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

    <div class="absolute top-6 right-8 z-50" x-data="{ syMenu: false }" @click.away="syMenu = false">
        <button @click="syMenu = !syMenu" class="inline-flex items-center border-[3px] border-black rounded-lg px-4 py-2 font-bold bg-white shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-gray-50 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all focus:outline-none">
            <span>SY {{ $activeYear ? $activeYear->school_year : 'N/A' }}</span>
            <i class="fa-solid fa-chevron-down ml-3 text-sm transition-transform duration-200" :class="syMenu ? 'rotate-180' : ''"></i>
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

    <div class="text-center mb-12">
        <h2 class="text-6xl font-black text-gray-900 mb-2 tracking-tight">Account Management</h2>
        <h3 class="text-4xl font-bold text-red-700 uppercase tracking-widest" style="text-shadow: 2px 2px 0px #000;">
            SY {{ $activeYear ? $activeYear->school_year : 'N/A' }}
        </h3>
    </div>

    <div class="w-full max-w-4xl mx-auto flex flex-col items-center">
        
        <div class="flex flex-wrap justify-center gap-8 w-full mb-8">
            <div class="relative" x-data="{ listOpen: false }" @click.away="listOpen = false">
                <button @click="listOpen = !listOpen" class="bg-[#e68a2d] hover:bg-yellow-500 text-black text-2xl font-black py-5 px-12 rounded-full border-[3px] border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] flex items-center transition-all active:translate-x-[2px] active:translate-y-[2px] active:shadow-none">
                    List of accounts
                    <i class="fa-solid fa-caret-down ml-4 transition-transform duration-300" :class="listOpen ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="listOpen" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-cloak 
                     class="absolute top-full mt-4 left-0 w-full bg-white border-[3px] border-black rounded-2xl overflow-hidden shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] z-20">
                    
                    <a href="{{ route('teacher.list') }}" class="block px-6 py-4 text-xl font-bold border-b-[3px] border-black hover:bg-yellow-100 transition-colors">
                        Teacher Accounts
                    </a>
                    <a href="{{ route('parent.list') }}" class="block px-6 py-4 text-xl font-bold hover:bg-yellow-100 transition-colors">
                        Parent Accounts
                    </a>
                </div>
            </div>

            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open" class="bg-[#e68a2d] hover:bg-yellow-500 text-black text-2xl font-black py-5 px-12 rounded-full border-[3px] border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] flex items-center transition-all active:translate-x-[2px] active:translate-y-[2px] active:shadow-none">
                    Create an account
                    <i class="fa-solid fa-caret-down ml-4 transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-cloak 
                     class="absolute top-full mt-4 left-0 w-full bg-white border-[3px] border-black rounded-2xl overflow-hidden shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] z-20">
                    <a href="{{ route('teacher.create') }}" class="block px-6 py-4 text-xl font-bold border-b-[3px] border-black hover:bg-yellow-100 transition-colors">Teacher Account</a>
                    <a href="{{ route('parent.create') }}" class="block px-6 py-4 text-xl font-bold hover:bg-yellow-100 transition-colors">Parent Account</a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8 max-w-3xl w-full">
            
            <a href="{{ route('admin.audit_logs') }}" 
               class="w-full bg-blue-500 hover:bg-blue-600 text-black text-2xl font-black py-5 rounded-full border-[3px] border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all flex items-center justify-center text-center">
                View Activity Logs
            </a>
        
            <button @click="finalizeModal = true" class="w-full bg-green-500 hover:bg-green-600 text-black text-2xl font-black py-5 px-8 rounded-full border-[3px] border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all">
                Finalize School Year
            </button>

            <button @click="passwordModal = true" class="col-span-2 w-full bg-[#ff3366] text-black font-black text-2xl py-5 px-8 rounded-full border-[3px] border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] hover:bg-[#ff1a53] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all flex items-center justify-center gap-3">
                Change User Password <i class="fa-solid fa-key ml-2"></i>
            </button>
        </div>

    </div>

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
                            name="user_id" 
                            class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-[#ff3366] transition-colors @error('user_id') border-red-500 bg-red-50 @else bg-white @enderror" 
                            value="{{ old('user_id') }}" 
                            required>
                            
                        @error('user_id') 
                            <p class="text-red-600 font-bold text-sm mt-2 flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">New Password</label>
                        <input type="password" name="password" x-model="newPassword" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-[#ff3366]">
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
</div>
@endsection