@extends('layouts.navigation')

@section('title', 'Grade Sheet')

@section('content')
<style>
    /* Input Styles */
    .form-input-pill { border: 1px solid #ccc; height: 100%; width: 100%; text-align: center; font-weight: 900; font-size: 1.1rem; outline: none; background: transparent; }
    .form-select-pill { border: 1px solid #ccc; height: 100%; width: 100%; text-align: center; font-weight: bold; font-size: 0.9rem; outline: none; background: transparent; cursor: pointer; }
    .form-input-pill:focus, .form-select-pill:focus { background: white; border-color: #3b82f6; }
    
    /* Hide Up/Down Arrows on Number Inputs */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }

    /* DepEd Table Styling */
    .deped-table th, .deped-table td { border: 1px solid black; padding: 0.25rem 0.5rem; }
    .deped-table th { text-align: center; font-weight: bold; }
    .deped-table td.input-cell { padding: 0; height: 35px; }
</style>

<main class="flex-1 p-6 bg-white min-h-screen" x-data="gradeData()">
    <div class="max-w-5xl mx-auto">
        
        <div class="flex justify-between items-start mb-6 border-b-4 border-black pb-4">
            <div>
                <h2 class="text-4xl font-black uppercase text-black">{{ $studentName ?? 'Student Name' }}</h2>
                <h3 class="text-2xl font-bold text-amber-700 uppercase">{{ $sectionName ?? 'Section Name' }}</h3>
                <p class="text-sm font-bold text-blue-600 uppercase mt-1">Active Window: Term <span x-text="activeTerm"></span></p>
            </div>
            
            <div class="flex flex-col items-end space-y-3">
                <button onclick="window.history.back()" class="text-red-600 text-5xl hover:scale-110 transition leading-none">
                    <i class="fa-solid fa-circle-left"></i>
                </button>
                @if($canManage ?? true)
                <div class="flex space-x-2">
                    <button @click="isManaging = !isManaging" class="font-black px-4 py-2 border-[3px] border-black rounded shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all" :class="isManaging ? 'bg-green-400' : 'bg-gray-200'">
                        <i class="fa-solid" :class="isManaging ? 'fa-unlock' : 'fa-lock'"></i> <span x-text="isManaging ? ' EDITING' : ' VIEWING'"></span>
                    </button>
                    <button x-show="isManaging" x-cloak @click="saveGrades()" class="bg-[#b26905] text-black px-4 py-2 rounded border-[3px] border-black font-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> SAVE
                    </button>
                </div>
                @endif
            </div>
        </div>

        <div class="flex flex-col space-y-12 mb-12">
            <!-- Learning Progress and Achievement Table (3 Terms) -->
            <div>
                <h4 class="text-center font-bold text-lg mb-2 uppercase">Report on Learning Progress and Achievement</h4>
                <table class="w-full text-sm deped-table bg-white">
                    <thead>
                        <tr>
                            <th rowspan="2" class="w-1/3">Learning Areas</th>
                            <th colspan="3">Term</th>
                            <th rowspan="2" class="w-16">Final<br>Grade</th>
                            <th rowspan="2" class="w-20">Remarks</th>
                        </tr>
                        <tr>
                            <th class="w-12">1</th>
                            <th class="w-12">2</th>
                            <th class="w-12">3</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $regularSubs = ['Filipino', 'English', 'Mathematics', 'Science', 'Araling Panlipunan (AP)', 'GMRC', 'EPP / TLE'];
                            $mapehSubs = ['Music and Arts', 'Physical Education and Health'];
                        @endphp

                        @foreach($regularSubs as $subject)
                        <tr>
                            <td class="font-bold">{{ $subject }}</td>
                            @for($t = 1; $t <= 3; $t++)
                            <td class="input-cell text-center">
                                <input x-show="isManaging && activeTerm == '{{ $t }}' && isTermUnlocked('{{ $t }}')" type="number" min="0" max="100" step="0.01" 
                                       oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0;" 
                                       x-model="grades['{{ $subject }}'].term{{ $t }}" @input="calculateGrades()" 
                                       class="form-input-pill">
                                <span x-show="!isManaging || activeTerm != '{{ $t }}' || !isTermUnlocked('{{ $t }}')" x-text="grades['{{ $subject }}'].term{{ $t }}"></span>
                            </td>
                            @endfor
                            <td class="text-center font-bold" x-text="grades['{{ $subject }}'].final_grade"></td>
                            <td class="text-center" x-text="grades['{{ $subject }}'].remarks"></td>
                        </tr>
                        @endforeach

                        <tr>
                            <td class="font-bold">MAPEH</td>
                            <td colspan="3" class="bg-gray-100"></td>
                            <td class="text-center font-bold bg-gray-100"></td>
                            <td class="text-center bg-gray-100"></td>
                        </tr>

                        @foreach($mapehSubs as $subject)
                        <tr>
                            <td class="pl-8">{{ $subject }}</td>
                            @for($t = 1; $t <= 3; $t++)
                            <td class="input-cell text-center">
                                <input x-show="isManaging && activeTerm == '{{ $t }}' && isTermUnlocked('{{ $t }}')" type="number" min="0" max="100" step="0.01" 
                                       oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0;" 
                                       x-model="grades['{{ $subject }}'].term{{ $t }}" @input="calculateGrades()" 
                                       class="form-input-pill">
                                <span x-show="!isManaging || activeTerm != '{{ $t }}' || !isTermUnlocked('{{ $t }}')" x-text="grades['{{ $subject }}'].term{{ $t }}"></span>
                            </td>
                            @endfor
                            <td class="text-center font-bold" x-text="grades['{{ $subject }}'].final_grade"></td>
                            <td class="text-center" x-text="grades['{{ $subject }}'].remarks"></td>
                        </tr>
                        @endforeach

                        <tr>
                            <td colspan="4" class="text-right font-bold pr-4">General Average</td>
                            <td class="text-center font-bold text-lg" x-text="generalAverage"></td>
                            <td class="text-center font-bold" x-text="finalStatus"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Observed Values Table (3 Terms) -->
            <div>
                <h4 class="text-center font-bold text-lg mb-2 uppercase">Report on Learner's Observed Values</h4>
                <table class="w-full text-xs deped-table bg-white">
                    <thead>
                        <tr class="bg-[#8faadc]">
                            <th rowspan="2" class="w-1/4">Core Values</th>
                            <th rowspan="2">Behavior Statements</th>
                            <th colspan="3">Term</th>
                        </tr>
                        <tr class="bg-[#8faadc]">
                            <th class="w-10">1</th>
                            <th class="w-10">2</th>
                            <th class="w-10">3</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $behaviorRows = [
                                ['Maka-Diyos', "Expresses one's spiritual beliefs while respecting the spiritual beliefs of others", 'Expresses ones spiritual beliefs'],
                                ['Maka-Diyos', 'Shows adherence to ethical principles by upholding truth', 'Shows adherence to ethical principles'],
                                ['Makatao', 'Is sensitive to individual, social, and cultural differences', 'Is sensitive to individual differences'],
                                ['Makatao', 'Demonstrates contributions toward solidarity', 'Demonstrates contributions toward solidarity'],
                                ['Maka-kalikasan', 'Cares for the environment and utilizes resources wisely, judiciously, and economically', 'Cares for the environment'],
                                ['Maka-bansa', 'Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen', 'Demonstrates pride in being a Filipino'],
                                ['Maka-bansa', 'Demonstrates appropriate behavior in carrying out activities in the school, community, and country', 'Demonstrates appropriate behavior'],
                            ];
                        @endphp

                        @foreach($behaviorRows as $index => $row)
                        <tr>
                            @if($index === 0) <td rowspan="2" class="font-bold align-top">1. Maka-Diyos</td> @endif
                            @if($index === 2) <td rowspan="2" class="font-bold align-top">2. Makatao</td> @endif
                            @if($index === 4) <td class="font-bold align-top">3. Maka-kalikasan</td> @endif
                            @if($index === 5) <td rowspan="2" class="font-bold align-top">4. Maka-bansa</td> @endif
                            
                            <td class="p-2">{{ $row[1] }}</td>

                            <template x-for="t in ['term1','term2','term3']">
                                <td class="input-cell text-center">
                                    <select x-show="isManaging && activeTerm == t.replace('term','')" x-model="behaviors['{{ $row[2] }}'][t]" class="form-select-pill">
                                        <option value=""></option><option value="AO">AO</option><option value="SO">SO</option><option value="RO">RO</option><option value="NO">NO</option>
                                    </select>
                                    <span x-show="!isManaging || activeTerm != t.replace('term','')" x-text="behaviors['{{ $row[2] }}'][t]"></span>
                                </td>
                            </template>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Centered Alert Modal -->
    <div x-show="showErrorModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-transition.opacity>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showErrorModal = false"></div>
        <div class="relative bg-red-500 border-[4px] border-black rounded-2xl shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] p-6 max-w-sm w-full flex items-center space-x-4">
            <div class="bg-white rounded-full w-12 h-12 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-exclamation text-red-500 text-2xl font-black"></i>
            </div>
            <div class="text-white">
                <h3 class="text-xl font-black uppercase leading-tight">Missing in Term <span x-text="activeTerm"></span>:</h3>
                <ul class="font-bold text-sm mt-1"><template x-for="sub in missingSubjects" :key="sub"><li x-text="sub"></li></template></ul>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('gradeData', () => ({
            isManaging: false, showErrorModal: false, missingSubjects: [],
            studentId: '{{ $student_id ?? 1 }}', 
            activeTerm: '1', 
            subjects: @json($subjects), 
            grades: {}, 
            generalAverage: '', finalStatus: '', 
            behaviors: {},

            // Bring backend centralized term dates into JavaScript
            termDates: {
                term1: { start: '{{ $activeYear->term1_start ?? "" }}', end: '{{ $activeYear->term1_end ?? "" }}' },
                term2: { start: '{{ $activeYear->term2_start ?? "" }}', end: '{{ $activeYear->term2_end ?? "" }}' },
                term3: { start: '{{ $activeYear->term3_start ?? "" }}', end: '{{ $activeYear->term3_end ?? "" }}' }
            },

            init() {
                let rawGrades = @json((object)($savedGrades ?? []));
                this.grades = (Array.isArray(rawGrades) || !rawGrades) ? {} : rawGrades;

                let rawBehaviors = @json((object)($savedBehaviors ?? []));
                this.behaviors = (Array.isArray(rawBehaviors) || !rawBehaviors) ? {} : rawBehaviors;

                // Initialize grades array for 3 terms
                this.subjects.forEach(sub => { 
                    if (!this.grades[sub]) {
                        this.grades[sub] = { term1: '', term2: '', term3: '', final_grade: '', remarks: '' }; 
                    }
                });

                // Initialize behaviors array for 3 terms
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
            },

            determineActiveTerm() {
                const isComplete = (t) => this.subjects.every(s => String(this.grades[s]['term'+t] || '').trim() !== '');
                if (isComplete(1) && isComplete(2)) return '3';
                if (isComplete(1)) return '2';
                return '1';
            },

            isTermUnlocked(termNumber) {
                const today = new Date().toISOString().split('T')[0]; // Format YYYY-MM-DD
                const term = this.termDates['term' + termNumber];
                
                // If admin hasn't set dates yet in central settings, keep locked safely
                if (!term.start || !term.end) return false; 
                
                return today >= term.start && today <= term.end;
            },

            calculateGrades() {
                let totalScore = 0, count = 0;
                this.subjects.forEach(s => {
                    let g = this.grades[s];
                    let vals = [parseFloat(g.term1), parseFloat(g.term2), parseFloat(g.term3)];
                    
                    if (vals.every(v => !isNaN(v))) {
                        let avg = vals.reduce((a,b) => a+b, 0) / 3; // Divided by 3 terms
                        g.final_grade = Math.round(avg);
                        g.remarks = g.final_grade >= 75 ? 'Passed' : 'Failed';
                        totalScore += avg; count++;
                    } else { g.final_grade = ''; g.remarks = ''; }
                });
                
                if (count > 0) {
                    this.generalAverage = Math.round(totalScore / count);
                    this.finalStatus = this.generalAverage >= 75 ? 'Promoted' : 'Retained';
                }
            },

            async saveGrades() {
                let tKey = 'term' + this.activeTerm;
                this.missingSubjects = this.subjects.filter(s => String(this.grades[s][tKey] || '').trim() === '');
                if (this.missingSubjects.length > 0) {
                    this.showErrorModal = true;
                    setTimeout(() => { this.showErrorModal = false; }, 3000);
                    return;
                }

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
                            behaviors: this.behaviors 
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