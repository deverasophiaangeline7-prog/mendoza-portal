@extends('layouts.navigation')

@section('title', 'Grade Sheet')

@section('content')
<style>
    .form-input-pill { border: 1px solid #ccc; height: 100%; width: 100%; text-align: center; font-weight: 900; font-size: 1.1rem; outline: none; background: transparent; }
    .form-select-pill { border: 1px solid #ccc; height: 100%; width: 100%; text-align: center; font-weight: bold; font-size: 0.9rem; outline: none; background: transparent; cursor: pointer; }
    .form-input-pill:focus, .form-select-pill:focus { background: white; border-color: #3b82f6; }
    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }

    .deped-table th, .deped-table td { border: 1px solid black; padding: 0.25rem 0.5rem; }
    .deped-table th { text-align: center; font-weight: bold; }
    .deped-table td.input-cell { padding: 0; height: 35px; }

    .print-only { display: none; }
    
    @media screen {
        .print-sheet, .print-card { width: 100%; display: block; }
    }

    @media print {
        body { background-color: white; margin: 0; padding: 0; }
        body * { visibility: hidden; }
        #printable-area, #printable-area * { visibility: visible; }
        
        #printable-area { position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 0; }
        .print-only { display: block !important; }
        .no-print { display: none !important; }
        
        /* 1. Strict PORTRAIT */
        @page { size: letter portrait; margin: 0.25in !important; }
        
        /* 2. Container matches 11" paper */
        .print-sheet {
            width: 100% !important;
            height: 10.5in !important;
            display: flex !important;
            flex-direction: column !important;
            page-break-after: always !important;
            overflow: hidden !important;
            background: white;
            box-sizing: border-box;
        }
        
        /* 3. The card takes exactly the top half of the portrait paper */
        .print-card {
            width: 100% !important;
            height: 50% !important;
            padding: 0.2in 0.4in !important;
            box-sizing: border-box !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between;
        }

        /* 4. Compress tables for print */
        .deped-table { margin-bottom: 4px !important; }
        .deped-table th, .deped-table td { padding: 2px 4px !important; font-size: 9px !important; height: auto !important; }
        .input-cell { padding: 2px !important; height: auto !important; }
        .print-compress { margin-bottom: 2px !important; }
    }
</style>

<main class="flex-1 p-6 bg-white min-h-screen" x-data="gradeData()">
    <div class="max-w-5xl mx-auto">
        
        <!-- ON-SCREEN HEADER & BUTTONS (Hidden during print) -->
        <div class="no-print flex justify-between items-start mb-8 border-b-4 border-black pb-4">
            <div>
                <h2 class="text-4xl font-black uppercase text-black">{{ $studentName ?? 'Student Name' }}</h2>
                <h3 class="text-2xl font-bold text-amber-700 uppercase">{{ $sectionName ?? 'Section Name' }}</h3>
                <p class="text-sm font-bold text-blue-600 uppercase mt-1">Active Window: Term <span x-text="activeTerm"></span></p>
            </div>
            
            <div class="flex items-start gap-4">
                @if($canManage ?? true)
                <div class="flex flex-col space-y-2">
                    <button @click="isManaging = !isManaging" class="font-black px-4 py-2 border-[3px] border-black rounded-lg shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all" :class="isManaging ? 'bg-green-400' : 'bg-gray-200'">
                        <i class="fa-solid" :class="isManaging ? 'fa-unlock' : 'fa-lock'"></i> <span x-text="isManaging ? ' EDITING' : ' VIEWING'"></span>
                    </button>
                    <button x-show="isManaging" x-cloak @click="saveGrades()" class="bg-[#b26905] text-black px-4 py-2 rounded-lg border-[3px] border-black font-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> SAVE
                    </button>
                </div>
                @endif

                <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg border-[3px] border-black font-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-blue-700 transition-all">
                    <i class="fa-solid fa-print mr-1"></i> PRINT
                </button>

                @if(auth()->user()->role !== 'parent')
                <a href="javascript:history.back()" class="text-red-600 text-5xl hover:scale-110 transition leading-none ml-2">
                    <i class="fa-solid fa-circle-left"></i>
                </a>
                @endif
            </div>
        </div>

        @php
            $studentObj = \App\Models\Student::with('section.teacher')->find($student_id);
            $teacherObj = $studentObj->section->teacher ?? null;
            $resolvedTeacherName = $teacherObj ? strtoupper($teacherObj->first_name . ' ' . $teacherObj->last_name) : '_________________';
        @endphp

        <!-- START OF PRINTABLE AREA -->
        <div id="printable-area" class="w-full">
            <div class="print-sheet">
                <div class="print-card">
                    
                    <!-- 1. HIDDEN PRINT HEADER -->
                    <div class="print-only text-black print-compress mb-4">
                        <div class="flex items-center justify-center gap-3 mb-2">
                            <img src="{{ asset('images/MAILogo.png') }}" alt="Logo" class="w-10 h-10 object-contain">
                            <div class="text-center leading-none">
                                <h1 class="text-[13px] font-bold tracking-wide">MENDOZA ACADEMY, INC.</h1>
                                <h2 class="text-[10px] font-semibold mt-1">Temporary Learner’s Performance Report</h2>
                                <p class="text-[8px] font-bold mt-1">School Year {{ $activeYear->school_year ?? '2026-2027' }}</p>
                            </div>
                        </div>
                        
                        <div class="text-[9px] font-semibold space-y-1 mt-2">
                            <div class="flex items-end">
                                <span class="w-10">Name:</span>
                                <span class="flex-1 border-b border-black font-normal px-1 leading-none pb-0.5">{{ $studentName }}</span>
                            </div>
                            <div class="flex items-end gap-2">
                                <div class="flex items-end w-1/4">
                                    <span class="mr-1">Grade:</span>
                                    @php $cleanGrade = str_replace('GRADE', '', strtoupper($sectionName ?? '')); @endphp
                                    <span class="flex-1 border-b border-black text-center font-normal px-1 leading-none pb-0.5">{{ explode("\n", trim($cleanGrade))[0] }}</span>
                                </div>
                                <div class="flex items-end w-1/3">
                                    <span class="mr-1">Section:</span>
                                    <span class="flex-1 border-b border-black text-center font-normal px-1 leading-none pb-0.5">{{ explode("\n", $sectionName ?? '')[1] ?? '' }}</span>
                                </div>
                                <div class="flex items-end flex-1">
                                    <span class="mr-1">LRN:</span>
                                    <span class="flex-1 border-b border-black text-center font-normal px-1 leading-none pb-0.5">{{ $studentObj->lrn ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="flex items-end">
                                <span class="w-12">Teacher:</span>
                                <span class="flex-1 border-b border-black font-normal px-1 leading-none pb-0.5">{{ $resolvedTeacherName }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- VISIBLE TABLES (Displays properly on screen and in print) -->
                    <div class="flex flex-col space-y-10 print:space-y-0 w-full mt-4 print:mt-2">
                        
                        <!-- GRADES TABLE -->
                        <div class="w-full print-compress">
                            <h4 class="text-center font-bold text-lg mb-2 uppercase print:text-[8px] print:mb-0.5">Report on Learning Progress and Achievement</h4>
                            <table class="w-full text-sm deped-table bg-white">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="w-1/3">Learning Areas</th>
                                        <th colspan="3">Term</th>
                                        <th rowspan="2" class="w-16 print:w-8">Final<br>Grade</th>
                                        <th rowspan="2" class="w-20">Remarks</th>
                                    </tr>
                                    <tr>
                                        <th class="w-12 print:w-6">1</th>
                                        <th class="w-12 print:w-6">2</th>
                                        <th class="w-12 print:w-6">3</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center font-bold">
                                    @foreach($subjects as $subject)
                                    <tr>
                                        <td class="text-left font-bold">{{ $subject }}</td>
                                        @for($t = 1; $t <= 3; $t++)
                                        <td class="input-cell text-center">
                                            <input x-show="isManaging && activeTerm == '{{ $t }}' && isTermUnlocked('{{ $t }}') && canGradeSubject('{{ $subject }}')" type="number" min="0" max="100" step="0.01" 
                                                   oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0;" 
                                                   x-model="grades['{{ $subject }}'].term{{ $t }}" @input="calculateGrades()" 
                                                   class="form-input-pill">
                                            <span x-show="!isManaging || activeTerm != '{{ $t }}' || !isTermUnlocked('{{ $t }}') || !canGradeSubject('{{ $subject }}')" 
                                            x-text="gradeLevelNum === 1 ? getDescriptor(grades['{{ $subject }}'].term{{ $t }}) : grades['{{ $subject }}'].term{{ $t }}"></span>
                                        </td>
                                        @endfor
                                        <td class="text-center font-bold" 
                                        x-text="gradeLevelNum === 1 ? getDescriptor(grades['{{ $subject }}'].final_grade) : grades['{{ $subject }}'].final_grade"></td>
                                        <td class="text-center" x-text="grades['{{ $subject }}'].remarks"></td>
                                    </tr>
                                    @endforeach

                                    <tr>
                                        <td colspan="4" class="text-right font-bold pr-4 print:pr-2">General Average</td>
                                        <td class="text-center font-bold text-lg print:text-[9px]" 
                                        x-text="gradeLevelNum === 1 ? getDescriptor(generalAverage) : generalAverage"></td>
                                        <td class="text-center font-bold" x-text="finalStatus"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- LEGEND (Grade 1 Only) -->
                        <div x-show="gradeLevelNum === 1" x-cloak class="w-full print-compress mt-4 print:mt-1">
                            <h4 class="text-center font-bold text-lg print:text-[8px] mb-2 print:mb-0.5">Performance Descriptors</h4>
                            <div class="max-w-3xl mx-auto print:max-w-full">
                                <table class="w-full text-sm deped-table bg-white font-semibold">
                                    <thead>
                                        <tr><th class="w-1/3">Scale</th><th class="w-1/3">Descriptors</th><th class="w-1/3">Remarks</th></tr>
                                    </thead>
                                    <tbody class="text-center">
                                        <tr><td>90 - 100</td><td>Advancing</td><td>Passed</td></tr>
                                        <tr><td>80 - 89</td><td>Benchmarking</td><td>Passed</td></tr>
                                        <tr><td>75 - 79</td><td>Connecting</td><td>Passed</td></tr>
                                        <tr><td>65 - 74</td><td>Developing</td><td>Passed</td></tr>
                                        <tr><td>0 - 64</td><td>Emerging</td><td>Failed</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TEACHER REMARKS -->
                        <div class="w-full mt-4 print:mt-auto">
                            <h4 class="text-center font-bold text-lg uppercase print:text-[8px] mb-2 print:mb-0.5">Teacher's Comments/Remarks</h4>
                            <table class="w-full text-sm deped-table bg-white">
                                <tbody>
                                    @for($t = 1; $t <= 3; $t++)
                                    <tr>
                                        <td class="w-32 font-bold align-middle px-3 py-2 text-center print:w-12 print:p-0">Term {{ $t }}</td>
                                        <td class="p-0 align-top relative">
                                            <textarea x-show="isManaging && activeTerm == '{{ $t }}' && isTermUnlocked('{{ $t }}')" 
                                                      x-model="teacherRemarks.term{{ $t }}" 
                                                      @input="teacherRemarks.term{{ $t }} = $event.target.value.replace(/[^a-zA-Z\s.,\-']/g, '')"
                                                      rows="2"
                                                      class="w-full min-h-[3rem] px-3 py-2 outline-none resize-y focus:bg-white focus:ring-2 focus:ring-blue-500 bg-transparent border-none"
                                                      placeholder="Enter comments/remarks..."></textarea>
                                                      
                                            <div x-show="!isManaging || activeTerm != '{{ $t }}' || !isTermUnlocked('{{ $t }}')" 
                                                 class="w-full min-h-[3rem] px-3 py-2 whitespace-pre-wrap text-left print:min-h-0 print:py-0 print:px-1" 
                                                 x-text="teacherRemarks.term{{ $t }}"></div>
                                        </td>
                                    </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>

                        <!-- 2. HIDDEN PRINT FOOTER -->
                        <div class="print-only mt-4">
                            <h4 class="font-bold text-center uppercase mb-3 text-sm print:text-[9px]">PARENT'S/GUARDIAN'S SIGNATURE</h4>
                            <div class="space-y-4 text-sm font-semibold max-w-[280px] mx-auto print:text-[9px] print:space-y-1.5 print:max-w-[200px]">
                                <div class="flex items-end gap-2"><span class="w-16 print:w-10">Term 1</span><div class="flex-1 border-b border-black"></div></div>
                                <div class="flex items-end gap-2"><span class="w-16 print:w-10">Term 2</span><div class="flex-1 border-b border-black"></div></div>
                                <div class="flex items-end gap-2"><span class="w-16 print:w-10">Term 3</span><div class="flex-1 border-b border-black"></div></div>
                            </div>
                        </div>

                    </div> <!-- End of inner flex column -->
                </div>
            </div>
        </div> <!-- End of Printable Area -->

        <!-- MODALS (Keep outside of printable area) -->
        <!-- STRICT LOCK MODAL -->
        <div x-show="showStrictLockModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-transition.opacity>
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showStrictLockModal = false"></div>
            <div class="relative bg-white border-[4px] border-black rounded-3xl shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] p-8 max-w-lg w-full flex flex-col items-center text-center space-y-4">
                <div class="bg-red-600 rounded-full w-20 h-20 flex items-center justify-center border-[4px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] -mt-16">
                    <i class="fa-solid fa-lock text-white text-4xl font-black"></i>
                </div>
                <h3 class="text-3xl font-black uppercase text-red-600 tracking-tight leading-tight mt-2">Strict Lock Active</h3>
                <p class="font-bold text-lg text-gray-800">
                    You cannot save grades for <span class="text-black font-black uppercase underline">Term <span x-text="strictLockTerm"></span></span> because <span class="text-red-600 font-black uppercase underline">Term <span x-text="strictLockPastTerm"></span></span> is incomplete.
                </p>
                <div class="bg-gray-100 border-[3px] border-black rounded-xl p-4 w-full text-left shadow-[4px_4px_0px_0px_rgba(0,0,0,0.1)]">
                    <p class="font-black text-xs uppercase text-gray-500 mb-2 tracking-wider">Missing Grades For:</p>
                    <ul class="font-bold text-md list-disc list-inside text-black">
                        <template x-for="sub in strictLockMissing" :key="sub">
                            <li x-text="sub"></li>
                        </template>
                    </ul>
                </div>
                <p class="font-bold text-sm text-gray-600 italic">Please ask the Admin to extend the Term <span x-text="strictLockPastTerm"></span> deadline.</p>
                <button @click="showStrictLockModal = false" class="mt-4 bg-red-600 text-white font-black text-xl px-8 py-3 border-[3px] border-black rounded-xl shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all w-full uppercase tracking-widest">
                    Understood
                </button>
            </div>
        </div>

        <!-- SOFT CONFIRMATION MODAL -->
        <div x-show="showErrorModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-transition.opacity>
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showErrorModal = false"></div>
            <div class="relative bg-white border-[4px] border-black rounded-3xl shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] p-8 max-w-sm w-full flex flex-col items-center text-center space-y-4">
                <div class="bg-amber-400 rounded-full w-20 h-20 flex items-center justify-center border-[4px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] -mt-16">
                    <i class="fa-solid fa-triangle-exclamation text-black text-4xl font-black"></i>
                </div>
                <h3 class="text-2xl font-black uppercase text-amber-500 tracking-tight leading-tight mt-2">Missing Grades</h3>
                <p class="font-bold text-sm text-gray-800">
                    You have empty fields for Term <span x-text="activeTerm"></span>. Other teachers can fill these in later, but do you want to save your current progress?
                </p>
                <div class="bg-gray-100 border-[3px] border-black rounded-xl p-3 w-full text-left shadow-[4px_4px_0px_0px_rgba(0,0,0,0.1)]">
                    <ul class="font-bold text-sm list-disc list-inside text-black">
                        <template x-for="sub in missingSubjects" :key="sub">
                            <li x-text="sub"></li>
                        </template>
                    </ul>
                </div>
                <div class="flex w-full gap-3 mt-4">
                    <button @click="showErrorModal = false" class="flex-1 bg-gray-200 text-black font-black text-sm px-4 py-3 border-[3px] border-black rounded-xl shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all uppercase tracking-wider">
                        Cancel
                    </button>
                    <button @click="saveGrades(true)" class="flex-1 bg-green-500 text-black font-black text-sm px-4 py-3 border-[3px] border-black rounded-xl shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all uppercase tracking-wider">
                        Proceed
                    </button>
                </div>
            </div>
        </div>

    </div>
</main>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('gradeData', () => ({
            isManaging: false, showErrorModal: false, missingSubjects: [],
            showStrictLockModal: false, strictLockMissing: [], strictLockTerm: '', strictLockPastTerm: '',
            studentId: '{{ $student_id ?? 1 }}', 
            activeTerm: '1', 
            subjects: @json($subjects), 
            grades: {}, 
            generalAverage: '', finalStatus: '', 
            behaviors: {},
            teacherRemarks: { term1: '', term2: '', term3: '' },
            
            assignedString: '{{ \App\Models\Teacher::where("user_id", auth()->id())->value("assigned_subject") ?? "NONE" }}',
            
            gradeLevelNum: {{ $gradeNum ?? 0 }}, 
            
            getDescriptor(score) {
                if (score === '' || score === null || score === undefined) return '';
                
                let s = parseFloat(score);
                if (isNaN(s)) return score; 
                
                if (s >= 90 && s <= 100) return 'A';
                if (s >= 80 && s <= 89) return 'B';
                if (s >= 75 && s <= 79) return 'C';
                if (s >= 65 && s <= 74) return 'D';
                if (s >= 0 && s <= 64) return 'E';
                
                return '';
            },

            canGradeSubject(subj) {
                let assigned = this.assignedString.toUpperCase();
                return assigned.includes('ALL') || assigned.includes(subj.toUpperCase());
            },

            termDates: {
                term1: { start: '{{ $activeYear ? $activeYear->term1_start : "" }}', end: '{{ $activeYear ? $activeYear->term1_end : "" }}' },
                term2: { start: '{{ $activeYear ? $activeYear->term2_start : "" }}', end: '{{ $activeYear ? $activeYear->term2_end : "" }}' },
                term3: { start: '{{ $activeYear ? $activeYear->term3_start : "" }}', end: '{{ $activeYear ? $activeYear->term3_end : "" }}' }
            },

            init() {
                let rawGrades = @json((object)($savedGrades ?? []));
                this.grades = (Array.isArray(rawGrades) || !rawGrades) ? {} : rawGrades;

                let rawBehaviors = @json((object)($savedBehaviors ?? []));
                this.behaviors = (Array.isArray(rawBehaviors) || !rawBehaviors) ? {} : rawBehaviors;

                let rawRemarks = @json((object)($savedRemarks ?? []));
                this.teacherRemarks = Object.assign({ term1: '', term2: '', term3: '' }, Array.isArray(rawRemarks) || !rawRemarks ? {} : rawRemarks);

                this.subjects.forEach(sub => { 
                    if (!this.grades[sub]) {
                        this.grades[sub] = { term1: '', term2: '', term3: '', final_grade: '', remarks: '' }; 
                    }
                });

                const behaviorKeys = [
                    'Expresses ones spiritual beliefs',
                    'Shows adherence to ethical principles',
                    'Is sensitive to individual differences',
                    'Demonstrates contributions toward solidarity',
                    'Cares for the environment',
                    'Demonstrates pride in being a Filipino',
                    'Demonstrates appropriate behavior'
                ];
                
                behaviorKeys.forEach(b => {
                    if (!this.behaviors[b]) {
                        this.behaviors[b] = { term1: '', term2: '', term3: '' };
                    }
                });

                this.activeTerm = this.determineActiveTerm();
                this.calculateGrades();

                @if(!str_contains($sectionName ?? '', 'ARCHIVED'))
                setInterval(() => {
                    if (!this.isManaging) {
                        fetch(`/fetch-grades/${this.studentId}?t=` + Date.now())
                            .then(res => res.json())
                            .then(data => {
                                let freshGrades = data.grades || {};
                                let freshBehaviors = data.behaviors || {};
                                let freshRemarks = data.remarks || {};

                                this.subjects.forEach(sub => { 
                                    this.grades[sub] = freshGrades[sub] || { term1: '', term2: '', term3: '', final_grade: '', remarks: '' };
                                });

                                behaviorKeys.forEach(b => {
                                    this.behaviors[b] = freshBehaviors[b] || { term1: '', term2: '', term3: '' };
                                });

                                this.teacherRemarks = Object.assign(this.teacherRemarks, freshRemarks);
                                this.calculateGrades(); 
                            })
                            .catch(err => console.error('Error syncing grades:', err));
                    }
                }, 1000);
                @endif
            },

            determineActiveTerm() {
                const tzOffset = (new Date()).getTimezoneOffset() * 60000;
                const today = (new Date(Date.now() - tzOffset)).toISOString().split("T")[0];

                for (let t = 1; t <= 3; t++) {
                    const term = this.termDates['term' + t];
                    if (term && term.start && term.end) {
                        const startDate = term.start.split(' ')[0];
                        const endDate = term.end.split(' ')[0];
                        if (today >= startDate && today <= endDate) return String(t);
                    }
                }

                const isComplete = (t) => this.subjects.every(s => String(this.grades[s]['term'+t] || '').trim() !== '');
                if (isComplete(1) && isComplete(2)) return '3';
                if (isComplete(1)) return '2';
                
                return '1';
            },

            isTermUnlocked(termNumber) {
                const term = this.termDates['term' + termNumber];
                if (!term.start || !term.end) return false; 
                
                const tzOffset = (new Date()).getTimezoneOffset() * 60000;
                const today = (new Date(Date.now() - tzOffset)).toISOString().split("T")[0];
                const startDate = term.start.split(' ')[0];
                const endDate = term.end.split(' ')[0];
                
                return today >= startDate && today <= endDate;
            },

            calculateGrades() {
                let totalScore = 0, count = 0;
                this.subjects.forEach(s => {
                    let g = this.grades[s];
                    let vals = [parseFloat(g.term1), parseFloat(g.term2), parseFloat(g.term3)];
                    
                    if (vals.every(v => !isNaN(v))) {
                        let avg = vals.reduce((a,b) => a+b, 0) / 3; 
                        g.final_grade = Math.round(avg);
                        g.remarks = g.final_grade >= 65 ? 'Passed' : 'Failed';
                        totalScore += avg; count++;
                    } else { g.final_grade = ''; g.remarks = ''; }
                });
                
                if (count > 0) {
                    this.generalAverage = Math.round(totalScore / count);
                    this.finalStatus = this.generalAverage >= 65 ? 'Promoted' : 'Retained';
                }
            },

            async saveGrades(forceSave = false) {
                let currentTermNum = parseInt(this.activeTerm);
                
                if (currentTermNum > 1) {
                    for (let pastTerm = 1; pastTerm < currentTermNum; pastTerm++) {
                        let pastMissing = this.subjects.filter(s => {
                            let canGrade = this.assignedString.toUpperCase().includes('ALL') || this.assignedString.toUpperCase().includes(s.toUpperCase());
                            return canGrade && String(this.grades[s]['term' + pastTerm] || '').trim() === '';
                        });

                        if (pastMissing.length > 0) {
                            this.strictLockTerm = currentTermNum;
                            this.strictLockPastTerm = pastTerm;
                            this.strictLockMissing = pastMissing;
                            this.showStrictLockModal = true;
                            return; 
                        }
                    }
                }

                if (!forceSave) {
                    let tKey = 'term' + this.activeTerm;
                    this.missingSubjects = this.subjects.filter(s => {
                        let canGrade = this.assignedString.toUpperCase().includes('ALL') || this.assignedString.toUpperCase().includes(s.toUpperCase());
                        let isEmpty = String(this.grades[s][tKey] || '').trim() === '';
                        return canGrade && isEmpty; 
                    });

                    if (this.missingSubjects.length > 0) {
                        this.showErrorModal = true;
                        return;
                    }
                }

                this.showErrorModal = false;

                try {
                    const response = await fetch('{{ route('reportcard.store') }}', {
                        method: 'POST', 
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ 
                            student_id: this.studentId, 
                            grades: this.grades, 
                            behaviors: this.behaviors,
                            remarks: this.teacherRemarks
                        })
                    });

                    const resData = await response.json();

                    if (response.ok) {
                        location.reload();
                    } else {
                        alert('Server Validation Error: ' + (resData.message || JSON.stringify(resData)));
                    }
                } catch (err) {
                    alert('Save Failed: ' + err.message);
                }
            }
        }));
    });
</script>
@endsection