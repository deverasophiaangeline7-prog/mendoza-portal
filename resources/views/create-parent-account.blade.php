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

<main class="flex-1 p-12 bg-white">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-10">
            <h2 class="text-5xl font-black text-black">Create an account</h2>
            <p class="text-3xl font-bold text-black mt-2">Parent</p>
        </div>

        <form action="{{ route('account.parent.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-2 gap-x-16 gap-y-6" x-data="{ pw: '', pw_confirm: '' }">
                
                <div class="space-y-5">
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <label class="w-40 flex-shrink-0 font-bold text-xl">LRN: <span class="text-red-600">*</span></label>
                            
                            <input type="text" 
                                   name="lrn" 
                                   class="form-input-pill @error('lrn') border-red-600 @enderror" 
                                   value="{{ old('lrn') }}" 
                                   required
                                   maxlength="12"
                                   inputmode="numeric"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12)">
                        </div>
                        @error('lrn') <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <label class="w-40 flex-shrink-0 font-bold text-xl">Last name: <span class="text-red-600">*</span></label>
                            <input type="text" 
                                   name="last_name" 
                                   class="form-input-pill @error('last_name') border-red-600 @enderror" 
                                   value="{{ old('last_name') }}" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s'-]/g, '').toLowerCase().replace(/\b\w/g, char => char.toUpperCase())"
                                   required>
                        </div>
                        @error('last_name') <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <label class="w-40 flex-shrink-0 font-bold text-xl">First name: <span class="text-red-600">*</span></label>
                            <input type="text" 
                                   name="first_name" 
                                   class="form-input-pill @error('first_name') border-red-600 @enderror" 
                                   value="{{ old('first_name') }}" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s'-]/g, '').toLowerCase().replace(/\b\w/g, char => char.toUpperCase())"
                                   required>
                        </div>
                        @error('first_name') <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <div class="flex items-center">
                            <label class="w-40 flex-shrink-0 font-bold text-xl">Middle name:</label>
                            <input type="text" 
                                   name="middle_name" 
                                   class="form-input-pill"
                                   value="{{ old('middle_name') }}"
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s'-]/g, '').toLowerCase().replace(/\b\w/g, char => char.toUpperCase())">
                        </div>
                    </div>

                    <div class="flex items-center">
                        <label class="w-40 flex-shrink-0 font-bold text-xl">Ext. name:</label>
                        <input type="text" 
                               name="ext_name" 
                               class="form-input-pill"
                               value="{{ old('ext_name') }}"
                               oninput="this.value = this.value.replace(/[^a-zA-Z\s'-]/g, '').toLowerCase().replace(/\b\w/g, char => char.toUpperCase())">
                    </div>

                    <div class="flex flex-col" x-data="{ fileError: false }">
                        <div class="flex items-center">
                            <label class="w-40 flex-shrink-0 font-bold text-xl">Profile Photo:</label>
                            <div class="flex flex-col w-full">
                                <input type="file" 
                                    name="profile_photo" 
                                    id="profile_photo"
                                    accept=".png, .jpg, .jpeg" 
                                    class="form-input-pill bg-white py-1 transition-colors"
                                    :class="fileError ? 'border-red-600 ring-1 ring-red-600' : 'border-black'"
                                    @change="
                                        const file = $event.target.files[0];
                                        if (file) {
                                            const type = file.type;
                                            const validTypes = ['image/png', 'image/jpg', 'image/jpeg'];
                                            fileError = !validTypes.includes(type);
                                            
                                            if(fileError) {
                                                $event.target.value = ''; 
                                            }
                                        }
                                    ">
                                
                                <p class="text-[10px] text-gray-500 font-bold mt-1 uppercase tracking-wider">
                                    Max size: 2MB (.png, .jpg, .jpeg only)
                                </p>

                                <template x-if="fileError">
                                    <span class="text-red-600 text-sm font-bold italic mt-1">
                                        The profile photo field must be an image.
                                    </span>
                                </template>
                            </div>
                        </div>
                        
                        @error('profile_photo') 
                            <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>

                <div class="space-y-5">
                    <div class="flex items-center">
                        <label class="w-40 flex-shrink-0 font-bold text-xl">Sex: <span class="text-red-600">*</span></label>
                        <select name="gender" class="form-input-pill bg-white cursor-pointer focus:outline-none">
                            <option value="" disabled selected>Select Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <label class="w-40 flex-shrink-0 font-bold text-xl">Birthdate: <span class="text-red-600">*</span></label>
                            
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

                            <input type="date" 
                                   name="birthdate" 
                                   class="form-input-pill @error('birthdate') border-red-600 @enderror" 
                                   value="{{ old('birthdate') }}"
                                   min="{{ $minDate }}" 
                                   max="{{ $maxDate }}" 
                                   required>
                        </div>

                        @error('birthdate')
                            <span class="text-red-600 text-[10px] font-black uppercase italic mt-1 ml-40 flex items-center">
                                <i class="fa-solid fa-circle-exclamation mr-1 text-xs"></i>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <label class="w-40 flex-shrink-0 font-bold text-xl leading-tight">Grade &<br>Section: <span class="text-red-600">*</span></label>
                            <select name="section_id" 
                                    class="border-2 border-black rounded-xl p-2 w-full font-bold"
                                    @change="$el.form.grade_level.value = $el.options[$el.selectedIndex].getAttribute('data-grade')">
                                <option value="">Select Grade & Section</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->section_id }}" data-grade="{{ $section->grade_level }}">
                                        {{ $section->grade_level }} - {{ $section->section_name }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="grade_level">
                        </div>
                        @error('section_id') <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <label class="w-40 flex-shrink-0 font-bold text-xl">Username: <span class="text-red-600">*</span></label>
                            <input type="text" name="username" class="form-input-pill @error('username') border-red-600 @enderror" value="{{ old('username') }}" required>
                        </div>
                        @error('username') <span class="text-red-600 text-sm ml-40 mt-1 font-bold italic">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col space-y-5">
                        <div class="flex items-center">
                            <label class="w-40 flex-shrink-0 font-bold text-xl">Password: <span class="text-red-600">*</span></label>
                            <input type="password" name="password" x-model="pw" class="form-input-pill" required>
                        </div>
                        <div class="flex items-center">
                            <label class="w-40 flex-shrink-0 font-bold text-xl">Confirm: <span class="text-red-600">*</span></label>
                            <input type="password" name="password_confirmation" x-model="pw_confirm" class="form-input-pill" required>
                        </div>
                        <template x-if="pw_confirm !== '' && pw !== pw_confirm">
                            <span class="text-red-600 text-sm ml-40 mt-[-10px] font-bold italic">Passwords do not match!</span>
                        </template>
                    </div>

                    <div class="flex justify-end gap-6 pt-10">
                        <a href="{{ route('account.management') }}" class="bg-[#FF3B30] text-white px-10 py-2 rounded-xl font-bold text-xl shadow-md border border-black/10 hover:brightness-90 transition flex items-center">
                            Cancel
                        </a>
                        <button type="submit" class="bg-[#34C759] text-white px-10 py-2 rounded-xl font-bold text-xl shadow-md border border-black/10 hover:brightness-90 transition">
                            Create
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>
@endsection