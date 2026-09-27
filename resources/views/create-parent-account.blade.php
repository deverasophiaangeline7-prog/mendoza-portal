@extends('layouts.navigation')

@section('title', 'Create Parent Account')

@section('content')
<style>
    .form-input-pill {
        border: 2px solid black;
        border-radius: 0.75rem;
        height: 2.5rem;
        padding: 0 0.75rem;
        width: 100%;
    }
</style>

<main class="flex-1 p-4 md:p-12 bg-white">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-8 md:mb-10">
            <h2 class="text-3xl md:text-5xl font-black text-black leading-tight">Create an account</h2>
            <p class="text-xl md:text-3xl font-bold text-black mt-1 md:mt-2">Parent</p>
        </div>

        <form action="{{ route('account.parent.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-16 gap-y-6" 
                 x-data="{ 
                     pw: '', 
                     pw_confirm: '',
                     strength() {
                         if(!this.pw) return '';
                         if(this.pw.length < 8) return 'Weak';
                         let l = /[a-zA-Z]/.test(this.pw);
                         let n = /\d/.test(this.pw);
                         let s = /[^a-zA-Z0-9]/.test(this.pw);
                         if(l && n && s) return 'Strong';
                         if((l&&n)||(l&&s)||(n&&s)) return 'Mid';
                         return 'Weak';
                     }
                 }">
                
                {{-- LEFT COLUMN --}}
                <div class="space-y-5">
                    <div class="flex flex-col">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <label class="w-full md:w-40 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">LRN: <span class="text-red-600">*</span></label>
                            <input type="text" name="lrn" class="form-input-pill @error('lrn') border-red-600 @enderror" value="{{ old('lrn') }}" required maxlength="12" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12)">
                        </div>
                        @error('lrn') <span class="text-red-600 text-sm ml-0 md:ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <label class="w-full md:w-40 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">Last name: <span class="text-red-600">*</span></label>
                            <input type="text" name="last_name" class="form-input-pill @error('last_name') border-red-600 @enderror" value="{{ old('last_name') }}" oninput="this.value = this.value.replace(/[^a-zA-Z\s'-]/g, '').toLowerCase().replace(/\b\w/g, char => char.toUpperCase())" required>
                        </div>
                        @error('last_name') <span class="text-red-600 text-sm ml-0 md:ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <label class="w-full md:w-40 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">First name: <span class="text-red-600">*</span></label>
                            <input type="text" name="first_name" class="form-input-pill @error('first_name') border-red-600 @enderror" value="{{ old('first_name') }}" oninput="this.value = this.value.replace(/[^a-zA-Z\s'-]/g, '').toLowerCase().replace(/\b\w/g, char => char.toUpperCase())" required>
                        </div>
                        @error('first_name') <span class="text-red-600 text-sm ml-0 md:ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <label class="w-full md:w-40 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">Middle name:</label>
                            <input type="text" name="middle_name" class="form-input-pill" value="{{ old('middle_name') }}" oninput="this.value = this.value.replace(/[^a-zA-Z\s'-]/g, '').toLowerCase().replace(/\b\w/g, char => char.toUpperCase())">
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center">
                        <label class="w-full md:w-40 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">Ext. name:</label>
                        <input type="text" name="ext_name" class="form-input-pill" value="{{ old('ext_name') }}" oninput="this.value = this.value.replace(/[^a-zA-Z\s'-]/g, '').toLowerCase().replace(/\b\w/g, char => char.toUpperCase())">
                    </div>

                    <div class="flex flex-col" x-data="{ fileError: false }">
                        <div class="flex flex-col md:flex-row md:items-start">
                            <label class="w-full md:w-40 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0 md:mt-1">Profile Photo:</label>
                            <div class="flex flex-col w-full">
                                <input type="file" name="profile_photo" id="profile_photo" accept=".png, .jpg, .jpeg" class="form-input-pill bg-white py-1 transition-colors" :class="fileError ? 'border-red-600 ring-1 ring-red-600' : 'border-black'" @change="const file = $event.target.files[0]; if (file) { const type = file.type; const validTypes = ['image/png', 'image/jpg', 'image/jpeg']; fileError = !validTypes.includes(type); if(fileError) { $event.target.value = ''; } }">
                                <p class="text-[10px] text-gray-500 font-bold mt-1 uppercase tracking-wider">Max size: 2MB (.png, .jpg, .jpeg only)</p>
                                <template x-if="fileError"><span class="text-red-600 text-sm font-bold italic mt-1">The profile photo field must be an image.</span></template>
                            </div>
                        </div>
                        @error('profile_photo') <span class="text-red-600 text-sm ml-0 md:ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- RIGHT COLUMN --}}
                <div class="space-y-5">
                    <div class="flex flex-col md:flex-row md:items-center">
                        <label class="w-full md:w-40 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">Sex: <span class="text-red-600">*</span></label>
                        <select name="gender" class="form-input-pill bg-white cursor-pointer focus:outline-none @error('gender') border-red-600 @enderror" required>
                            <option value="" disabled selected>Select Sex</option>
                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>

                    <div class="flex flex-col">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <label class="w-full md:w-40 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">Birthdate: <span class="text-red-600">*</span></label>
                            @php
                                $activeSy = \App\Models\SchoolYear::where('status', 'active')->first();
                                $targetYear = now()->year; 
                                if ($activeSy) {
                                    $syText = $activeSy->school_year; 
                                    preg_match('/\d{4}/', $syText, $matches);
                                    $targetYear = $matches[0] ?? now()->year; 
                                }
                                $minDate = \Carbon\Carbon::create($targetYear - 100, 1, 1)->format('Y-m-d');
                                $maxDate = \Carbon\Carbon::create($targetYear - 3, 12, 31)->format('Y-m-d'); 
                            @endphp
                            <input type="date" name="birthdate" class="form-input-pill @error('birthdate') border-red-600 @enderror" value="{{ old('birthdate') }}" min="{{ $minDate }}" max="{{ $maxDate }}" required>
                        </div>
                        @error('birthdate')
                            <span class="text-red-600 text-[10px] font-black uppercase italic mt-1 ml-0 md:ml-40 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1 text-xs"></i>{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <label class="w-full md:w-40 flex-shrink-0 font-bold text-base md:text-xl leading-tight mb-1 md:mb-0">Grade &<br class="hidden md:block">Section: <span class="text-red-600">*</span></label>
                            <select name="section_id" class="border-2 border-black rounded-xl p-2 w-full font-bold bg-white focus:outline-none" @change="$el.form.grade_level.value = $el.options[$el.selectedIndex].getAttribute('data-grade')" required>
                                <option value="">Select Grade & Section</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->section_id }}" data-grade="{{ $section->grade_level }}">{{ $section->grade_level }} - {{ $section->section_name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="grade_level">
                        </div>
                        @error('section_id') <span class="text-red-600 text-sm ml-0 md:ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <label class="w-full md:w-40 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">Username: <span class="text-red-600">*</span></label>
                            <input type="text" name="username" class="form-input-pill @error('username') border-red-600 @enderror" value="{{ old('username') }}" required>
                        </div>
                        @error('username') <span class="text-red-600 text-sm ml-0 md:ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col space-y-5">
                        <div class="flex flex-col">
                            <div class="flex flex-col md:flex-row md:items-center">
                                <label class="w-full md:w-40 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">Password: <span class="text-red-600">*</span></label>
                                <div class="w-full relative" x-data="{ show: false }">
                                    <input :type="show ? 'text' : 'password'" name="password" x-model="pw" class="form-input-pill pr-10" required>
                                    <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-black">
                                        <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                            </div>
                            
                            {{-- LIVE STRENGTH INDICATOR --}}
                            <div x-show="pw !== ''" x-transition class="mt-2 md:ml-40 flex items-center gap-2 text-[10px] font-black uppercase tracking-widest bg-gray-50 p-2 rounded-lg border-2 border-black">
                                <span class="text-gray-600">Strength:</span>
                                <div class="flex-1 flex h-2 gap-1">
                                    <div class="flex-1 rounded-full transition-colors duration-300" :class="strength() === 'Weak' ? 'bg-red-500' : (strength() === 'Mid' ? 'bg-yellow-400' : 'bg-green-500')"></div>
                                    <div class="flex-1 rounded-full transition-colors duration-300" :class="(strength() === 'Mid' || strength() === 'Strong') ? (strength() === 'Mid' ? 'bg-yellow-400' : 'bg-green-500') : 'bg-gray-200'"></div>
                                    <div class="flex-1 rounded-full transition-colors duration-300" :class="strength() === 'Strong' ? 'bg-green-500' : 'bg-gray-200'"></div>
                                </div>
                                <span :class="{'text-red-600': strength() === 'Weak', 'text-yellow-600': strength() === 'Mid', 'text-green-600': strength() === 'Strong'}" x-text="strength()"></span>
                            </div>
                        </div>
                        
                        <div class="flex flex-col">
                            <div class="flex flex-col md:flex-row md:items-center">
                                <label class="w-full md:w-40 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">Confirm: <span class="text-red-600">*</span></label>
                                <div class="w-full relative" x-data="{ show: false }">
                                    <input :type="show ? 'text' : 'password'" name="password_confirmation" x-model="pw_confirm" class="form-input-pill pr-10" required>
                                    <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-black">
                                        <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                            </div>
                            <template x-if="pw_confirm !== '' && pw !== pw_confirm">
                                <span class="text-red-600 text-sm ml-0 md:ml-40 mt-1 font-bold italic"><i class="fa-solid fa-circle-exclamation"></i> Passwords do not match!</span>
                            </template>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row justify-end gap-4 md:gap-6 pt-10">
                        <a href="{{ route('account.management') }}" class="w-full md:w-auto justify-center bg-[#FF3B30] text-white px-10 py-3 md:py-2 rounded-xl font-bold text-xl shadow-[4px_4px_0px_rgba(0,0,0,1)] border-2 border-black hover:brightness-90 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all flex items-center">
                            Cancel
                        </a>
                        <button type="submit" 
                                :disabled="pw === '' || pw !== pw_confirm || strength() !== 'Strong'"
                                :class="(pw === '' || pw !== pw_confirm || strength() !== 'Strong') ? 'opacity-50 cursor-not-allowed' : 'hover:brightness-90 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none'"
                                class="w-full md:w-auto justify-center bg-[#34C759] text-white px-10 py-3 md:py-2 rounded-xl font-bold text-xl shadow-[4px_4px_0px_rgba(0,0,0,1)] border-2 border-black transition-all">
                            Create
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>
@endsection