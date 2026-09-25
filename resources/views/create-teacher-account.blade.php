@extends('layouts.navigation')

@section('title', 'Create Teacher Account')

@section('content')
<style>
    .form-input-pill {
        border: 2px solid black;
        border-radius: 0.75rem;
        height: 2.5rem;
        padding: 0 0.75rem;
        width: 100%;
    }
    [x-cloak] { display: none !important; }
</style>

@php
    // Filter out individual NKP sections so they don't clutter the dropdowns
    $filteredSections = $sections->reject(function($sec) {
        return in_array(strtoupper(trim($sec->grade_level)), [
            'NURSERY', 'KINDER', 'KINDERGARTEN', 'PREPARATORY', 'PREP', 'NKP'
        ]);
    });

    // Map the filtered sections (Grades 1-6) for the Alpine.js Dynamic Builder
    $alpineSections = $filteredSections->map(function($sec) {
        $gradeNum = '1'; // Default fallback
        if (preg_match('/\d+/', $sec->grade_level, $matches)) {
            $gradeNum = (string)$matches[0];
        }
        return [
            'id' => $sec->section_id,
            'name' => strtoupper($sec->grade_level . ' - ' . $sec->section_name),
            'grade' => $gradeNum
        ];
    })->values();
@endphp

<main class="flex-1 p-4 md:p-12 bg-white" x-data="teacherForm()">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-8 md:mb-10">
            <h2 class="text-3xl md:text-5xl font-black text-black leading-tight">Create an account</h2>
            <p class="text-xl md:text-3xl font-bold text-black mt-1 md:mt-2">Teacher</p>
        </div>

        <form action="{{ route('account.teacher.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-16 gap-y-6">

                {{-- LEFT COLUMN --}}
                <div class="space-y-5">
                    <div class="flex flex-col">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <label class="w-full md:w-32 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">Last name: <span class="text-red-600">*</span></label>
                            <input type="text" name="last_name" class="form-input-pill @error('last_name') border-red-600 @enderror" value="{{ old('last_name') }}" oninput="this.value = this.value.replace(/[^a-zA-Z\s'-]/g, '').toLowerCase().replace(/\b\w/g, char => char.toUpperCase())" required>
                        </div>
                        @error('last_name') <span class="text-red-600 text-sm ml-0 md:ml-32 mt-1 font-bold italic">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <label class="w-full md:w-32 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">First name: <span class="text-red-600">*</span></label>
                            <input type="text" name="first_name" class="form-input-pill @error('first_name') border-red-600 @enderror" value="{{ old('first_name') }}" oninput="this.value = this.value.replace(/[^a-zA-Z\s'-]/g, '').toLowerCase().replace(/\b\w/g, char => char.toUpperCase())" required>
                        </div>
                        @error('first_name') <span class="text-red-600 text-sm ml-0 md:ml-32 mt-1 font-bold italic">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <label class="w-full md:w-32 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">Middle name:</label>
                            <input type="text" name="middle_name" class="form-input-pill" value="{{ old('middle_name') }}" oninput="this.value = this.value.replace(/[^a-zA-Z\s'-]/g, '').toLowerCase().replace(/\b\w/g, char => char.toUpperCase())">
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center">
                        <label class="w-full md:w-32 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">Ext. name:</label>
                        <input type="text" name="ext_name" class="form-input-pill" value="{{ old('ext_name') }}" oninput="this.value = this.value.replace(/[^a-zA-Z\s'-]/g, '').toLowerCase().replace(/\b\w/g, char => char.toUpperCase())">
                    </div>

                    <div class="flex flex-col" x-data="{ fileError: false }">
                        <div class="flex flex-col md:flex-row md:items-start">
                            <label class="w-full md:w-32 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0 md:mt-1">Profile Photo:</label>
                            <div class="flex flex-col w-full">
                                <input type="file" name="profile_photo" accept=".png, .jpg, .jpeg" class="form-input-pill bg-white py-1 transition-colors @error('profile_photo') border-red-600 ring-1 ring-red-600 @enderror" :class="fileError ? 'border-red-600 ring-1 ring-red-600' : ''" @change="
                                        const file = $event.target.files[0];
                                        if (file) {
                                            const type = file.type;
                                            fileError = !['image/png', 'image/jpg', 'image/jpeg'].includes(type);
                                            if(fileError) $event.target.value = ''; 
                                        }
                                    ">
                                <p class="text-[10px] text-gray-500 font-bold mt-1 uppercase tracking-wider">Max size: 2MB (.png, .jpg, .jpeg only)</p>
                                <template x-if="fileError"><span class="text-red-600 text-sm font-bold italic mt-1">The profile photo field must be an image.</span></template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN --}}
                <div class="space-y-5">
                    <div class="flex flex-col md:flex-row md:items-center">
                        <label class="w-full md:w-32 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">Sex: <span class="text-red-600">*</span></label>
                        <select name="gender" class="form-input-pill bg-white cursor-pointer focus:outline-none" required>
                            <option value="" disabled selected>Select Sex</option>
                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center">
                        <label class="w-full md:w-32 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">Birthdate: <span class="text-red-600">*</span></label>
                        <input type="date" name="birthdate" class="form-input-pill" value="{{ old('birthdate') }}" max="{{ \Carbon\Carbon::now()->subYears(18)->format('Y-m-d') }}" required>
                    </div>

                    <div class="flex flex-col">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <label class="w-full md:w-32 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">Homeroom: <span class="text-red-600">*</span></label>
                            <select name="advisory" class="form-input-pill bg-white cursor-pointer focus:outline-none" required>
                                <option value="" disabled selected>Select Advisory Section</option>
                                <option value="NKP">NKP (Nursery, Kinder, Prep)</option>
                                @foreach($filteredSections as $section)
                                    <option value="{{ $section->section_id }}">
                                        {{ strtoupper($section->grade_level . ' - ' . $section->section_name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p class="text-[10px] text-gray-500 font-bold mt-1 ml-0 md:ml-32 uppercase tracking-wider">The class where they act as Adviser.</p>
                    </div>

                    <div class="flex flex-col">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <label class="w-full md:w-32 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">Username: <span class="text-red-600">*</span></label>
                            <input type="text" name="username" class="form-input-pill @error('username') border-red-600 @enderror" value="{{ old('username') }}" required>
                        </div>
                        @error('username') <span class="text-red-600 text-sm ml-0 md:ml-32 mt-1 font-bold italic">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-5" x-data="{ pw: '', pw_confirm: '' }">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <label class="w-full md:w-32 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">Password: <span class="text-red-600">*</span></label>
                            <input type="password" name="password" x-model="pw" class="form-input-pill" required>
                        </div>

                        <div class="flex flex-col">
                            <div class="flex flex-col md:flex-row md:items-center">
                                <label class="w-full md:w-32 flex-shrink-0 font-bold text-base md:text-xl mb-1 md:mb-0">Confirm: <span class="text-red-600">*</span></label>
                                <input type="password" name="password_confirmation" x-model="pw_confirm" class="form-input-pill" required>
                            </div>
                            <template x-if="pw_confirm !== '' && pw !== pw_confirm">
                                <span class="text-red-600 text-sm ml-0 md:ml-32 mt-1 font-bold italic">Passwords do not match!</span>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- FULL WIDTH DYNAMIC SUBJECT BUILDER --}}
                <div class="col-span-1 md:col-span-2 mt-4 bg-gray-50 border-2 border-black rounded-xl p-6">
                    <div class="mb-4">
                        <h3 class="font-black text-xl text-black">CLASS ASSIGNMENTS</h3>
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Assign specific subjects to specific classes below. (Leave empty if they are strictly a Class Adviser without subject load).</p>
                    </div>

                    <template x-for="(assignment, index) in assignments" :key="index">
                        <div class="flex flex-col md:flex-row gap-4 mb-4 items-center bg-white p-4 rounded-lg border-2 border-gray-200 shadow-sm">
                            
                            {{-- Target Section Dropdown (NKP is now included) --}}
                            <div class="w-full md:w-1/2">
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Target Section</label>
                                <select :name="`assignments[${index}][section_id]`" x-model="assignment.section_id" @change="updateSubjects(index)" class="form-input-pill bg-white cursor-pointer" required>
                                    <option value="" disabled selected>Select a Section...</option>
                                    <option value="NKP">NKP (Nursery, Kinder, Prep)</option>
                                    <template x-for="sec in sectionsList" :key="sec.id">
                                        <option :value="sec.id" x-text="sec.name"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Subject Taught Dropdown (MODIFIED: Hides when NKP is selected) --}}
                            <div class="w-full md:w-1/2" x-show="assignment.section_id !== 'NKP'" x-cloak>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Subject Taught</label>
                                <!-- Notice the :required here! It prevents form submission errors when hidden -->
                                <select :name="`assignments[${index}][subject]`" x-model="assignment.subject" class="form-input-pill bg-white cursor-pointer" :required="assignment.section_id !== 'NKP'">
                                    <option value="" disabled selected>Select a Subject...</option>
                                    <template x-for="subj in assignment.available_subjects" :key="subj">
                                        <option :value="subj" x-text="subj"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Red Square Delete Button --}}
                            <button type="button" @click="removeAssignment(index)" class="mt-4 md:mt-5 bg-[#FF3B30] text-white font-black text-xl h-10 w-10 flex-shrink-0 rounded-lg hover:brightness-90 transition border-2 border-black shadow-[2px_2px_0px_rgba(0,0,0,1)] active:scale-95 flex items-center justify-center leading-none">
                                &times;
                            </button>
                        </div>
                    </template>

                    {{-- Add Assignment Button --}}
                    <button type="button" @click="addAssignment()" class="bg-[#e68a2d] text-black px-5 py-2 mt-2 rounded-lg font-black text-sm uppercase tracking-wider border-2 border-black shadow-[2px_2px_0px_rgba(0,0,0,1)] hover:-translate-y-1 hover:shadow-[4px_4px_0px_rgba(0,0,0,1)] transition-all active:scale-95 flex items-center gap-2">
                        + Add Class Assignment
                    </button>
                </div>

                {{-- SUBMIT BUTTONS --}}
                <div class="col-span-1 md:col-span-2 flex flex-col md:flex-row justify-end gap-4 md:gap-6 pt-6">
                    <a href="{{ route('account.management') }}" class="w-full md:w-auto justify-center bg-[#FF3B30] text-white px-10 py-3 rounded-xl font-bold text-xl shadow-md border border-black/10 hover:brightness-90 transition flex items-center">Cancel</a>
                    <button type="submit" class="w-full md:w-auto justify-center bg-[#34C759] text-white px-10 py-3 rounded-xl font-bold text-xl shadow-md border border-black/10 hover:brightness-90 transition flex items-center">Create Account</button>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('teacherForm', () => ({
        sectionsList: @json($alpineSections),
        assignments: [],
        
        subjectMap: {
            'NKP': ['ALL (Class Adviser)', 'Language', 'Reading and Literacy', 'Mathematics', 'Makabansa', 'GMRC'],
            '1': ['ALL (Class Adviser)', 'GMRC', 'Language', 'Makabansa', 'Mathematics', 'Reading and Literacy'],
            '2': ['ALL (Class Adviser)', 'English', 'Filipino', 'GMRC', 'Makabansa', 'Mathematics'],
            '3': ['ALL (Class Adviser)', 'English', 'Filipino', 'GMRC', 'Makabansa', 'Mathematics'],
            '4': ['ALL (Class Adviser)', 'Araling Panlipunan (AP)', 'English', 'Filipino', 'GMRC', 'MAPEH', 'Mathematics', 'Science', 'TLE'],
            '5': ['ALL (Class Adviser)', 'Araling Panlipunan (AP)', 'English', 'Filipino', 'GMRC', 'MAPEH', 'Mathematics', 'Science', 'TLE'],
            '6': ['ALL (Class Adviser)', 'Araling Panlipunan (AP)', 'English', 'Filipino', 'GMRC', 'MAPEH', 'Mathematics', 'Science', 'TLE']
        },

        addAssignment() {
            this.assignments.push({ 
                section_id: '', 
                subject: '', 
                available_subjects: [] 
            });
        },

        removeAssignment(index) {
            this.assignments.splice(index, 1);
        },

        updateSubjects(index) {
            let selectedSectionId = this.assignments[index].section_id;
            
            // Explicitly handle the grouped NKP option
            if (selectedSectionId === 'NKP') {
                this.assignments[index].available_subjects = this.subjectMap['NKP'];
                this.assignments[index].subject = '';
                return;
            }

            // Handle Grades 1 through 6
            let sectionData = this.sectionsList.find(s => s.id == selectedSectionId);
            
            if (sectionData && this.subjectMap[sectionData.grade]) {
                this.assignments[index].available_subjects = this.subjectMap[sectionData.grade];
                this.assignments[index].subject = '';
            } else {
                this.assignments[index].available_subjects = [];
                this.assignments[index].subject = '';
            }
        }
    }));
});
</script>
@endsection