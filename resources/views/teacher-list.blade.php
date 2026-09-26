@extends('layouts.navigation')

@section('title', 'Teacher Accounts')

@section('content')
<!-- Top-level div holds the Alpine state for the Edit & Archive Modals -->
<div class="flex-1 flex flex-col bg-white min-h-screen relative" x-data="{ 
    archiveModal: false, archiveUrl: '', 
    editModal: false, editId: '', editFirstName: '', editLastName: '', editAdvisory: '',
    
    /* DYNAMIC ARRAY LOGIC FOR MULTIPLE SUBJECTS & GRADES */
    editAssignments: [],
    subjectMap: {
        'NKP': [],
        '1': ['ALL (Class Adviser)', 'GMRC', 'Language', 'Makabansa', 'Mathematics', 'Reading and Literacy'],
        '2': ['ALL (Class Adviser)', 'English', 'Filipino', 'GMRC', 'Makabansa', 'Mathematics'],
        '3': ['ALL (Class Adviser)', 'English', 'Filipino', 'GMRC', 'Makabansa', 'Mathematics'],
        '4': ['ALL (Class Adviser)', 'Araling Panlipunan (AP)', 'English', 'Filipino', 'GMRC', 'MAPEH', 'Mathematics', 'Science', 'TLE'],
        '5': ['ALL (Class Adviser)', 'Araling Panlipunan (AP)', 'English', 'Filipino', 'GMRC', 'MAPEH', 'Mathematics', 'Science', 'TLE'],
        '6': ['ALL (Class Adviser)', 'Araling Panlipunan (AP)', 'English', 'Filipino', 'GMRC', 'MAPEH', 'Mathematics', 'Science', 'TLE']
    },
    
    openEditModal(id, fname, lname, advisory, assignmentsJson) {
        this.editId = id;
        this.editFirstName = fname;
        this.editLastName = lname;
        this.editAdvisory = advisory;
        
        let parsed = JSON.parse(assignmentsJson);
        
        // Map existing backend data into the dynamic array
        this.editAssignments = parsed.map(a => {
            let grade = (['Nursery', 'Kinder', 'Prep', 'NKP', '1,2,3'].includes(a.grade)) ? 'NKP' : a.grade;
            let sectionId = (grade === 'NKP') ? 'NKP' : a.section_id;
            return {
                section_id: sectionId,
                grade: grade,
                subject: a.subject || '',
                availableSubjects: this.subjectMap[grade] || []
            };
        });

        // Ensure there's always at least one empty row if no data exists
        if(this.editAssignments.length === 0) {
            this.addAssignmentRow();
        }
        
        this.editModal = true;
    },
    
    addAssignmentRow() {
        this.editAssignments.push({ section_id: '', grade: '', subject: '', availableSubjects: [] });
    },
    
    removeAssignmentRow(index) {
        this.editAssignments.splice(index, 1);
    },
    
    updateRowSubjects(index, selectEl) {
        let selectedOption = selectEl.options[selectEl.selectedIndex];
        let grade = selectedOption.getAttribute('data-grade');
        
        this.editAssignments[index].grade = grade;
        this.editAssignments[index].subject = ''; 
        this.editAssignments[index].availableSubjects = this.subjectMap[grade] || [];
    }
}">
    
    <main class="flex-1 p-8">
        <div class="max-w-6xl mx-auto">
            
            <!-- RESPONSIVE HEADER SECTION -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8 w-full">
                <div>
                    <h2 class="text-3xl md:text-4xl font-black text-black uppercase tracking-tight leading-none">List of Accounts</h2>
                    <h3 class="text-xl md:text-2xl font-bold text-amber-700 mt-1 italic">Teachers</h3>
                </div>
                
                <div class="flex flex-wrap gap-3 w-full md:w-auto">
                    <a href="{{ route('teacher.archived') }}" class="flex-1 md:flex-none justify-center bg-gray-200 hover:bg-gray-300 text-black px-4 md:px-6 py-2 rounded-lg font-bold transition flex items-center gap-2 border-2 border-black text-sm md:text-base whitespace-nowrap">
                        <i class="fa-solid fa-box-archive"></i> View Archives
                    </a>
                    <a href="{{ route('account.management') }}" class="flex-1 md:flex-none justify-center bg-gray-800 hover:bg-black text-white px-4 md:px-6 py-2 rounded-lg font-bold transition flex items-center gap-2 border-2 border-black text-sm md:text-base whitespace-nowrap">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <!-- SCROLLABLE TABLE CONTAINER -->
            <div class="overflow-x-auto border-2 border-black rounded-lg shadow-sm w-full">
                <table class="w-full min-w-[600px] text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-200 border-b-2 border-black">
                            <th class="px-4 py-4 border-r-2 border-black text-center font-bold text-xl w-24">No.</th>
                            <th class="px-6 py-4 border-r-2 border-black font-bold text-xl">Name</th>
                            <th class="px-6 py-4 font-bold text-xl">Advisory Class & Subjects</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-black">
                        @forelse($teachers as $index => $teacherUser)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-4 border-r-2 border-black text-center font-bold text-lg text-gray-700">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 border-r-2 border-black font-bold text-lg uppercase">
                                {{ $teacherUser->teacher?->first_name ?? 'NO PROFILE' }} {{ $teacherUser->teacher?->last_name ?? '' }}
                            </td>
                            <td class="px-6 py-4 flex justify-between items-center gap-4">
                                
                                <div class="flex flex-col gap-2">
                                    
                                    <!-- Main Advisory Display -->
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-users text-amber-700"></i>
                                        <span class="font-black text-lg">
                                        @if(in_array($teacherUser->teacher?->advisory, ['1,2,3', 'NKP']) || in_array($teacherUser->teacher?->section?->grade_level, ['Nursery', 'Kinder', 'Prep', 'NKP', '1,2,3']))
                                            NKP
                                        @elseif($teacherUser->teacher?->section)
                                            {{ $teacherUser->teacher->section->grade_level }} - {{ $teacherUser->teacher->section->section_name }}
                                        @else
                                            No Advisory
                                        @endif
                                        </span>
                                    </div>

                                    @php
                                        $actualAssignments = \App\Models\SubjectAssignment::where('teacher_id', $teacherUser->user_id)->get();
                                        $tempAssignments = [];
                                        
                                        foreach($actualAssignments as $sa) {
                                            $grade = 'NKP'; 
                                            $section = \App\Models\Section::find($sa->section_id);
                                            
                                            if($section && !in_array(strtoupper($section->grade_level), ['NURSERY', 'KINDERGARTEN', 'KINDER', 'PREPARATORY', 'PREP', 'NKP', '1,2,3'])) {
                                                $grade = $section->grade_level;
                                            }

                                            $tempAssignments[] = [
                                                'section_id' => $sa->section_id,
                                                'grade' => $grade,
                                                'subject' => $sa->subject_name
                                            ];
                                        }
                                        
                                        $rawAdvisory = in_array($teacherUser->teacher?->advisory, ['1,2,3', 'NKP', 'Nursery', 'Kinder', 'Prep']) || in_array($teacherUser->teacher?->section?->grade_level, ['Nursery', 'Kinder', 'Prep', 'NKP', '1,2,3']) ? 'NKP' : $teacherUser->teacher?->advisory;
                                    @endphp

                                    <!-- Subjects Display -->
                                    @if($actualAssignments->count() > 0)
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($actualAssignments as $sa)
                                                @php
                                                    $sec = \App\Models\Section::find($sa->section_id);
                                                    $gradeDisplay = $sec && !in_array(strtoupper($sec->grade_level), ['NURSERY', 'KINDER', 'PREP', 'NKP']) 
                                                        ? 'G' . $sec->grade_level 
                                                        : 'NKP';
                                                @endphp
                                                <span class="text-xs text-blue-800 font-bold tracking-wider bg-blue-100 px-2 py-1 rounded border border-blue-600 flex items-center gap-1 shadow-sm">
                                                    <span class="bg-blue-600 text-white px-1.5 py-0.5 rounded text-[10px]">{{ $gradeDisplay }}</span>
                                                    {{ $sa->subject_name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @elseif($teacherUser->teacher?->assigned_subject)
                                        <!-- Fallback for older data -->
                                        <span class="text-sm text-blue-600 font-bold tracking-wider bg-blue-100 px-2 py-0.5 rounded border border-blue-600 w-max">
                                            <i class="fa-solid fa-book mr-1"></i> {{ $teacherUser->teacher->assigned_subject }}
                                        </span>
                                    @endif
                                </div>

                                <div class="flex gap-2 items-center shrink-0">
                                    <button type="button" 
                                            @click="openEditModal(
                                                '{{ $teacherUser->user_id }}', 
                                                '{{ addslashes($teacherUser->teacher?->first_name) }}', 
                                                '{{ addslashes($teacherUser->teacher?->last_name) }}',
                                                '{{ $rawAdvisory }}',
                                                '{{ json_encode($tempAssignments) }}'
                                            )"
                                        class="bg-[#34C759] hover:bg-green-600 transition-colors text-white px-4 py-1.5 rounded-full font-bold text-sm">
                                        Edit
                                    </button>
                                    
                                    <button type="button" @click="archiveModal = true; archiveUrl = '{{ route('account.teacher.archive', $teacherUser->user_id) }}'" title="Archive" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1.5 rounded-full font-bold text-sm transition-colors">
                                        <i class="fa-solid fa-box-archive"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-6 py-10 text-center text-gray-500 font-bold text-xl">No teachers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- TEACHER EDIT MODAL (Smaller box, pushed down on mobile) -->
    <div x-show="editModal" class="fixed inset-0 z-[9999] flex p-4 bg-black/60 backdrop-blur-sm overflow-y-auto" x-cloak>
        <div @click.away="editModal = false" class="bg-white border-4 border-black rounded-[2rem] p-8 max-w-2xl w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] mt-24 mx-auto mb-10 md:m-auto max-h-[70vh] flex flex-col flex-shrink-0">
            <div class="flex justify-between items-start mb-6 shrink-0">
                <h2 class="text-3xl font-black uppercase text-black">Edit Teacher</h2>
                <button type="button" @click="editModal = false" class="text-gray-400 hover:text-red-600 text-3xl"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form :action="'/account/teacher/' + editId + '/edit'" method="POST" class="flex flex-col overflow-hidden">
                @csrf
                @method('PUT')
                
                <div class="overflow-y-auto pr-2 pb-4 space-y-6">
                    <!-- Names -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">First Name</label>
                            <input type="text" name="first_name" x-model="editFirstName" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-green-400">
                        </div>
                        <div>
                            <label class="block font-bold uppercase text-gray-600 text-sm mb-2 tracking-widest">Last Name</label>
                            <input type="text" name="last_name" x-model="editLastName" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-green-400">
                        </div>
                    </div>

                    <!-- Required Main Advisory Field for the Controller -->
                    <div class="border-t-4 border-black pt-6">
                        <label class="block font-black uppercase text-black text-lg mb-2 tracking-widest">Main Advisory Class <span class="text-red-600">*</span></label>
                        <select name="advisory" x-model="editAdvisory" required class="w-full border-2 border-black rounded-xl px-4 py-3 font-bold focus:outline-none focus:ring-4 focus:ring-green-400 appearance-none bg-gray-50">
                            <option value="" disabled>Select Main Advisory</option>
                            <option value="NKP">NKP (Nursery, Kinder, Prep)</option>
                            @php
                                $dynamicSections = \App\Models\Section::orderByRaw("CAST(grade_level AS UNSIGNED) ASC")->get();
                            @endphp
                            @foreach($dynamicSections as $sec)
                                @if(!in_array(strtoupper($sec->grade_level), ['NURSERY', 'KINDER', 'KINDERGARTEN', 'PREP', 'PREPARATORY', 'NKP']))
                                    <option value="{{ $sec->section_id }}">Grade {{ $sec->grade_level }} - {{ $sec->section_name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 font-bold mt-2 uppercase tracking-wide">Sets the primary homeroom class this teacher manages.</p>
                    </div>

                    <!-- Dynamic Assignments Section -->
                    <div class="border-t-4 border-black pt-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-black uppercase tracking-widest text-lg">Subjects Taught</h3>
                            <button type="button" @click="addAssignmentRow()" class="bg-blue-600 text-white font-black uppercase text-sm tracking-wider px-4 py-2 rounded-xl border-2 border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:bg-blue-700 active:translate-y-1 active:translate-x-1 active:shadow-none transition-all">
                                + Add Subject
                            </button>
                        </div>

                        <!-- Rows Wrapper -->
                        <div class="space-y-4">
                            <template x-for="(assignment, index) in editAssignments" :key="index">
                                <div class="flex flex-col sm:flex-row gap-4 p-4 bg-gray-100 border-2 border-black rounded-xl relative">
                                    
                                    <button type="button" @click="removeAssignmentRow(index)" class="absolute -top-3 -right-3 bg-red-600 text-white w-8 h-8 rounded-full border-2 border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] flex items-center justify-center hover:bg-red-700 font-black active:translate-y-[2px] active:translate-x-[2px] active:shadow-none transition-all">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>

                                    <!-- Grade/Section -->
                                    <div class="flex-1">
                                        <label class="block font-bold uppercase text-gray-600 text-xs mb-1 tracking-widest">Target Grade <span class="text-red-600">*</span></label>
                                        <select :name="'assignments[' + index + '][section_id]'" x-model="assignment.section_id" @change="updateRowSubjects(index, $event.target)" required class="w-full border-2 border-black rounded-xl px-3 py-2 font-bold focus:outline-none focus:ring-4 focus:ring-green-400 appearance-none bg-white">
                                            <option value="" disabled selected>Select Grade</option>
                                            <option value="NKP" data-grade="NKP">NKP (Nursery, Kinder, Prep)</option>
                                            @foreach($dynamicSections as $sec)
                                                @if(!in_array(strtoupper($sec->grade_level), ['NURSERY', 'KINDER', 'KINDERGARTEN', 'PREP', 'PREPARATORY', 'NKP']))
                                                    <option value="{{ $sec->section_id }}" data-grade="{{ $sec->grade_level }}">Grade {{ $sec->grade_level }} - {{ $sec->section_name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Subject -->
                                    <div class="flex-1" x-show="assignment.grade !== 'NKP' && assignment.grade !== ''">
                                        <label class="block font-bold uppercase text-gray-600 text-xs mb-1 tracking-widest">Subject <span class="text-red-600">*</span></label>
                                        <select :name="'assignments[' + index + '][subject]'" x-model="assignment.subject" :required="assignment.grade !== 'NKP' && assignment.grade !== ''" class="w-full border-2 border-black rounded-xl px-3 py-2 font-bold focus:outline-none focus:ring-4 focus:ring-green-400 appearance-none bg-white">
                                            <option value="" disabled>Select Subject</option>
                                            <template x-for="subj in assignment.availableSubjects" :key="subj">
                                                <option :value="subj" x-text="subj" :selected="subj === assignment.subject"></option>
                                            </template>
                                        </select>
                                    </div>

                                </div>
                            </template>
                            
                            <div x-show="editAssignments.length === 0" class="text-center p-6 border-2 border-dashed border-gray-400 rounded-xl font-bold text-gray-500 italic">
                                No subjects assigned. Click "Add Subject" to begin.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex justify-end space-x-4 pt-6 mt-2 border-t-2 border-black shrink-0">
                    <button type="button" @click="editModal = false" class="font-bold text-gray-500 hover:text-black uppercase tracking-wider px-4">Cancel</button>
                    <button type="submit" class="bg-[#34C759] text-white font-black uppercase tracking-wider px-6 py-3 rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-green-600 active:translate-y-1 active:translate-x-1 active:shadow-none transition-all">
                        <i class="fa-solid fa-save mr-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Archive Confirmation Modal (Smaller box, pushed down on mobile) -->
    <div x-show="archiveModal" x-transition:opacity class="fixed inset-0 z-[9999] flex p-4 bg-black/60 backdrop-blur-sm overflow-y-auto" x-cloak>
        <div class="bg-white border-4 border-black rounded-[2rem] p-8 max-w-md w-full shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] mt-24 mx-auto mb-10 md:m-auto max-h-[70vh] flex-shrink-0" @click.away="archiveModal = false">
            <div class="text-center">
                <i class="fa-solid fa-box-archive text-6xl text-[#ffb72b] mb-6"></i>
                <h2 class="text-3xl font-black mb-4 uppercase">Archive Account?</h2>
                <p class="text-lg font-medium text-gray-600 mb-8 leading-tight">Are you sure you want to archive this teacher account? They will be hidden from the active list.</p>
                <div class="flex flex-col gap-4">
                    <form :action="archiveUrl" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full bg-[#ffb72b] text-black font-black py-4 rounded-full border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-yellow-500 active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all">YES, ARCHIVE</button>
                    </form>
                    <button @click="archiveModal = false" type="button" class="w-full bg-gray-100 text-gray-700 font-black py-4 rounded-full border-2 border-black hover:bg-gray-200 transition-all">CANCEL</button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- SUCCESS TOAST -->
@if(session('success'))
<div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-10"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-10"
     class="fixed bottom-10 right-10 z-[9999] px-8 py-4 rounded-2xl border-[3px] border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] flex items-center gap-4 bg-[#4ade80] text-black">
    <i class="fa-solid fa-circle-check text-3xl"></i>
    <span class="font-black text-xl tracking-wide">{{ session('success') }}</span>
</div>
@endif

<!-- ERROR TOAST -->
@if($errors->any())
<div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-10"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-10"
     class="fixed bottom-10 right-10 z-[9999] px-8 py-4 rounded-2xl border-[3px] border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] flex flex-col gap-2 bg-red-500 text-white">
    <div class="flex items-center gap-4 border-b-2 border-black/20 pb-2">
        <i class="fa-solid fa-circle-exclamation text-3xl"></i>
        <span class="font-black text-xl tracking-wide uppercase">Save Failed!</span>
    </div>
    <ul class="font-bold text-sm list-disc list-inside">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@endsection