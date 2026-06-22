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
                <h2 class="text-4xl font-black uppercase text-black">{{ $studentName }}</h2>
                <h3 class="text-2xl font-bold text-orange-600 uppercase">{{ $sectionName }}</h3>
            </div>
            
            <div class="flex flex-col items-end space-y-3">
                <button onclick="window.history.back()" class="text-red-600 text-5xl hover:scale-110 transition leading-none">
                    <i class="fa-solid fa-circle-left"></i>
                </button>
                @if($canManage)
                <div class="flex space-x-2">
                    <button @click="isManaging = !isManaging" class="font-black px-4 py-2 border-[3px] border-black rounded shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all" :class="isManaging ? 'bg-green-400' : 'bg-gray-200'">
                        <i class="fa-solid" :class="isManaging ? 'fa-unlock' : 'fa-lock'"></i> <span x-text="isManaging ? ' EDITING' : ' VIEWING'"></span>
                    </button>
                    <button x-show="isManaging" x-cloak @click="saveGrades()" class="bg-[#ffaf2e] text-black px-4 py-2 rounded border-[3px] border-black font-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> SAVE
                    </button>
                </div>
                @endif
            </div>
        </div>

        <div class="flex flex-col space-y-12 mb-12">
            
            <div>
                <h4 class="text-center font-bold text-lg mb-2 uppercase">Report on Learning Progress and Achievement</h4>
                <table class="w-full text-sm deped-table bg-white">
                    <thead>
                        <tr>
                            <th rowspan="2" class="w-1/3">Learning Areas</th>
                            <th colspan="4">Quarter</th>
                            <th rowspan="2" class="w-16">Final<br>Grade</th>
                            <th rowspan="2" class="w-20">Remarks</th>
                        </tr>
                        <tr>
                            <th class="w-10">1</th>
                            <th class="w-10">2</th>
                            <th class="w-10">3</th>
                            <th class="w-10">4</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $regularSubs = ['Language', 'English', 'Mathematics', 'Makabansa', 'GMRC'];
                            $mapehSubs = ['Music', 'Art', 'PE', 'Health'];
                        @endphp

                        @foreach($regularSubs as $subject)
                        <tr>
                            <td class="font-bold">{{ $subject }}</td>
                            <td class="input-cell text-center"><input x-show="isManaging" type="number" min="0" max="100" step="0.01" oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0;" x-model="grades['{{ $subject }}'].q1" @input="calculateGrades()" class="form-input-pill"><span x-show="!isManaging" x-text="grades['{{ $subject }}'].q1"></span></td>
                            <td class="input-cell text-center"><input x-show="isManaging" type="number" min="0" max="100" step="0.01" oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0;" x-model="grades['{{ $subject }}'].q2" @input="calculateGrades()" class="form-input-pill"><span x-show="!isManaging" x-text="grades['{{ $subject }}'].q2"></span></td>
                            <td class="input-cell text-center"><input x-show="isManaging" type="number" min="0" max="100" step="0.01" oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0;" x-model="grades['{{ $subject }}'].q3" @input="calculateGrades()" class="form-input-pill"><span x-show="!isManaging" x-text="grades['{{ $subject }}'].q3"></span></td>
                            <td class="input-cell text-center"><input x-show="isManaging" type="number" min="0" max="100" step="0.01" oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0;" x-model="grades['{{ $subject }}'].q4" @input="calculateGrades()" class="form-input-pill"><span x-show="!isManaging" x-text="grades['{{ $subject }}'].q4"></span></td>
                            <td class="text-center font-bold" x-text="grades['{{ $subject }}'].final_grade"></td>
                            <td class="text-center" x-text="grades['{{ $subject }}'].remarks"></td>
                        </tr>
                        @endforeach

                        <tr>
                            <td class="font-bold">MAPEH</td>
                            <td colspan="4" class="bg-gray-100"></td>
                            <td class="text-center font-bold bg-gray-100" x-text="mapehFinal"></td>
                            <td class="text-center bg-gray-100" x-text="mapehRemarks"></td>
                        </tr>

                        @foreach($mapehSubs as $subject)
                        <tr>
                            <td class="pl-8">{{ $subject }}</td>
                            <td class="input-cell text-center"><input x-show="isManaging" type="number" min="0" max="100" step="0.01" oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0;" x-model="grades['{{ $subject }}'].q1" @input="calculateGrades()" class="form-input-pill"><span x-show="!isManaging" x-text="grades['{{ $subject }}'].q1"></span></td>
                            <td class="input-cell text-center"><input x-show="isManaging" type="number" min="0" max="100" step="0.01" oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0;" x-model="grades['{{ $subject }}'].q2" @input="calculateGrades()" class="form-input-pill"><span x-show="!isManaging" x-text="grades['{{ $subject }}'].q2"></span></td>
                            <td class="input-cell text-center"><input x-show="isManaging" type="number" min="0" max="100" step="0.01" oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0;" x-model="grades['{{ $subject }}'].q3" @input="calculateGrades()" class="form-input-pill"><span x-show="!isManaging" x-text="grades['{{ $subject }}'].q3"></span></td>
                            <td class="input-cell text-center"><input x-show="isManaging" type="number" min="0" max="100" step="0.01" oninput="if(this.value > 100) this.value = 100; if(this.value < 0) this.value = 0;" x-model="grades['{{ $subject }}'].q4" @input="calculateGrades()" class="form-input-pill"><span x-show="!isManaging" x-text="grades['{{ $subject }}'].q4"></span></td>
                            <td class="text-center font-bold" x-text="grades['{{ $subject }}'].final_grade"></td>
                            <td class="text-center" x-text="grades['{{ $subject }}'].remarks"></td>
                        </tr>
                        @endforeach

                        <tr>
                            <td colspan="5" class="text-right font-bold pr-4">General Average</td>
                            <td class="text-center font-bold text-lg" x-text="generalAverage"></td>
                            <td class="text-center font-bold" x-text="finalStatus"></td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-4 flex justify-around text-xs leading-relaxed max-w-3xl mx-auto">
                    <div>
                        <p class="font-bold mb-2">Descriptors</p>
                        <p>Outstanding</p>
                        <p>Very Satisfactory</p>
                        <p>Satisfactory</p>
                        <p>Fairly Satisfactory</p>
                        <p>Did Not Meet Expectations</p>
                    </div>
                    <div class="text-center">
                        <p class="font-bold mb-2">Grading Scale</p>
                        <p>90-100</p>
                        <p>85-89</p>
                        <p>80-84</p>
                        <p>75-79</p>
                        <p>Below 75</p>
                    </div>
                    <div>
                        <p class="font-bold mb-2">Remarks</p>
                        <p>Passed</p>
                        <p>Passed</p>
                        <p>Passed</p>
                        <p>Passed</p>
                        <p>Failed</p>
                    </div>
                </div>
            </div>

            <div>
                <h4 class="text-center font-bold text-lg mb-2 uppercase">Report on Learner's Observed Values</h4>
                <table class="w-full text-xs deped-table bg-white">
                    <thead>
                        <tr class="bg-[#8faadc]">
                            <th rowspan="2" class="w-1/4">Core Values</th>
                            <th rowspan="2">Behavior Statements</th>
                            <th colspan="4">Quarter</th>
                        </tr>
                        <tr class="bg-[#8faadc]">
                            <th class="w-8">1</th>
                            <th class="w-8">2</th>
                            <th class="w-8">3</th>
                            <th class="w-8">4</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td rowspan="2" class="font-bold align-top">1. Maka-Diyos</td>
                            <td class="p-2">Expresses one's spiritual beliefs while respecting the spiritual beliefs of others</td>
                            <template x-for="q in ['q1','q2','q3','q4']">
                                <td class="input-cell text-center">
                                    <select x-show="isManaging" x-model="behaviors['Expresses ones spiritual beliefs'][q]" class="form-select-pill">
                                        <option value=""></option><option value="AO">AO</option><option value="SO">SO</option><option value="RO">RO</option><option value="NO">NO</option>
                                    </select>
                                    <span x-show="!isManaging" x-text="behaviors['Expresses ones spiritual beliefs'][q]"></span>
                                </td>
                            </template>
                        </tr>
                        <tr>
                            <td class="p-2">Shows adherence to ethical principles by upholding truth</td>
                            <template x-for="q in ['q1','q2','q3','q4']">
                                <td class="input-cell text-center">
                                    <select x-show="isManaging" x-model="behaviors['Shows adherence to ethical principles'][q]" class="form-select-pill">
                                        <option value=""></option><option value="AO">AO</option><option value="SO">SO</option><option value="RO">RO</option><option value="NO">NO</option>
                                    </select>
                                    <span x-show="!isManaging" x-text="behaviors['Shows adherence to ethical principles'][q]"></span>
                                </td>
                            </template>
                        </tr>

                        <tr>
                            <td rowspan="2" class="font-bold align-top">2. Makatao</td>
                            <td class="p-2">Is sensitive to individual, social, and cultural differences</td>
                            <template x-for="q in ['q1','q2','q3','q4']">
                                <td class="input-cell text-center">
                                    <select x-show="isManaging" x-model="behaviors['Is sensitive to individual differences'][q]" class="form-select-pill">
                                        <option value=""></option><option value="AO">AO</option><option value="SO">SO</option><option value="RO">RO</option><option value="NO">NO</option>
                                    </select>
                                    <span x-show="!isManaging" x-text="behaviors['Is sensitive to individual differences'][q]"></span>
                                </td>
                            </template>
                        </tr>
                        <tr>
                            <td class="p-2">Demonstrates contributions toward solidarity</td>
                            <template x-for="q in ['q1','q2','q3','q4']">
                                <td class="input-cell text-center">
                                    <select x-show="isManaging" x-model="behaviors['Demonstrates contributions toward solidarity'][q]" class="form-select-pill">
                                        <option value=""></option><option value="AO">AO</option><option value="SO">SO</option><option value="RO">RO</option><option value="NO">NO</option>
                                    </select>
                                    <span x-show="!isManaging" x-text="behaviors['Demonstrates contributions toward solidarity'][q]"></span>
                                </td>
                            </template>
                        </tr>

                        <tr>
                            <td class="font-bold align-top">3. Maka-kalikasan</td>
                            <td class="p-2">Cares for the environment and utilizes resources wisely, judiciously, and economically</td>
                            <template x-for="q in ['q1','q2','q3','q4']">
                                <td class="input-cell text-center">
                                    <select x-show="isManaging" x-model="behaviors['Cares for the environment'][q]" class="form-select-pill">
                                        <option value=""></option><option value="AO">AO</option><option value="SO">SO</option><option value="RO">RO</option><option value="NO">NO</option>
                                    </select>
                                    <span x-show="!isManaging" x-text="behaviors['Cares for the environment'][q]"></span>
                                </td>
                            </template>
                        </tr>

                        <tr>
                            <td rowspan="2" class="font-bold align-top">4. Maka-bansa</td>
                            <td class="p-2">Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen</td>
                            <template x-for="q in ['q1','q2','q3','q4']">
                                <td class="input-cell text-center">
                                    <select x-show="isManaging" x-model="behaviors['Demonstrates pride in being a Filipino'][q]" class="form-select-pill">
                                        <option value=""></option><option value="AO">AO</option><option value="SO">SO</option><option value="RO">RO</option><option value="NO">NO</option>
                                    </select>
                                    <span x-show="!isManaging" x-text="behaviors['Demonstrates pride in being a Filipino'][q]"></span>
                                </td>
                            </template>
                        </tr>
                        <tr>
                            <td class="p-2">Demonstrates appropriate behavior in carrying out activities in the school, community, and country</td>
                            <template x-for="q in ['q1','q2','q3','q4']">
                                <td class="input-cell text-center">
                                    <select x-show="isManaging" x-model="behaviors['Demonstrates appropriate behavior'][q]" class="form-select-pill">
                                        <option value=""></option><option value="AO">AO</option><option value="SO">SO</option><option value="RO">RO</option><option value="NO">NO</option>
                                    </select>
                                    <span x-show="!isManaging" x-text="behaviors['Demonstrates appropriate behavior'][q]"></span>
                                </td>
                            </template>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-4 flex justify-center space-x-12 text-sm">
                    <div>
                        <p class="font-bold mb-2">Marking</p>
                        <p>AO</p>
                        <p>SO</p>
                        <p>RO</p>
                        <p>NO</p>
                    </div>
                    <div>
                        <p class="font-bold mb-2">Non-Numerical Rating</p>
                        <p>Always Observed</p>
                        <p>Sometimes Observed</p>
                        <p>Rarely Observed</p>
                        <p>Not Observed</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div x-show="showToast" x-cloak class="fixed bottom-10 right-10 z-50 px-8 py-4 rounded shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] border-[3px] border-black bg-green-400 text-black font-black uppercase text-xl">
        <i class="fa-solid fa-circle-check mr-2"></i> <span x-text="toastMessage"></span>
    </div>
</main>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('gradeData', () => ({
            isManaging: false, 
            showToast: false, 
            toastMessage: '',
            studentId: '{{ $student_id }}', 
            
            subjects: ['Language', 'English', 'Mathematics', 'Makabansa', 'GMRC', 'Music', 'Art', 'PE', 'Health'], 
            grades: @json($savedGrades) || {}, 
            
            generalAverage: '',
            finalStatus: '',
            mapehFinal: '',
            mapehRemarks: '',

            behaviorKeys: [
                'Expresses ones spiritual beliefs',
                'Shows adherence to ethical principles',
                'Is sensitive to individual differences',
                'Demonstrates contributions toward solidarity',
                'Cares for the environment',
                'Demonstrates pride in being a Filipino',
                'Demonstrates appropriate behavior'
            ],
            behaviors: @json($savedBehaviors) || {},

            init() {
                if (Array.isArray(this.grades)) this.grades = {};
                this.subjects.forEach(sub => { 
                    if(!this.grades[sub]) {
                        this.grades[sub] = { q1: '', q2: '', q3: '', q4: '', final_grade: '', remarks: '' }; 
                    }
                });

                if (Array.isArray(this.behaviors)) this.behaviors = {};
                this.behaviorKeys.forEach(key => { 
                    if(!this.behaviors[key]) {
                        this.behaviors[key] = { q1: '', q2: '', q3: '', q4: '' }; 
                    }
                });

                this.calculateGrades();
            },

            calculateGrades() {
                let totalMain = 0; let mainCount = 0;
                let mapehTotal = 0; let mapehCount = 0;

                const regularSubs = ['Language', 'English', 'Mathematics', 'Makabansa', 'GMRC'];
                const mapehSubs = ['Music', 'Art', 'PE', 'Health'];

                regularSubs.forEach(sub => {
                    let g = this.grades[sub];
                    if (g.q1 && g.q2 && g.q3 && g.q4) {
                        let avg = (parseFloat(g.q1) + parseFloat(g.q2) + parseFloat(g.q3) + parseFloat(g.q4)) / 4;
                        g.final_grade = Math.round(avg); 
                        g.remarks = g.final_grade >= 75 ? 'Passed' : 'Failed';
                        totalMain += avg; mainCount++;
                    } else { g.final_grade = ''; g.remarks = ''; }
                });

                mapehSubs.forEach(sub => {
                    let g = this.grades[sub];
                    if (g.q1 && g.q2 && g.q3 && g.q4) {
                        let avg = (parseFloat(g.q1) + parseFloat(g.q2) + parseFloat(g.q3) + parseFloat(g.q4)) / 4;
                        g.final_grade = Math.round(avg); 
                        g.remarks = g.final_grade >= 75 ? 'Passed' : 'Failed';
                        mapehTotal += avg; mapehCount++;
                    } else { g.final_grade = ''; g.remarks = ''; }
                });

                if (mapehCount === 4) {
                    let mFinal = Math.round(mapehTotal / 4);
                    this.mapehFinal = mFinal;
                    this.mapehRemarks = mFinal >= 75 ? 'Passed' : 'Failed';
                    totalMain += (mapehTotal / 4);
                    mainCount++;
                } else {
                    this.mapehFinal = ''; this.mapehRemarks = '';
                }

                if (mainCount === 6) { 
                    let genAvg = Math.round(totalMain / 6);
                    this.generalAverage = genAvg;
                    this.finalStatus = genAvg >= 75 ? 'Promoted' : 'Retained';
                } else {
                    this.generalAverage = ''; this.finalStatus = '';
                }
            },

            saveGrades() {
                fetch('{{ route('reportcard.store') }}', {
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ 
                        student_id: this.studentId, 
                        grades: this.grades,
                        behaviors: this.behaviors 
                    })
                })
                .then(res => res.json())
                .then(data => { 
                    this.toastMessage = 'Saved Successfully!'; 
                    this.showToast = true; 
                    setTimeout(() => this.showToast = false, 3000); 
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Error saving. Check console.');
                });
            }
        }));
    });
</script>
@endsection