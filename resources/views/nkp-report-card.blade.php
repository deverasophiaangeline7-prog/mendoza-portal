@extends('layouts.navigation')

@section('title', 'NKP Report Card')

@section('content')
<style>
    .deped-table th, .deped-table td { border: 1px solid black; padding: 0.4rem 0.6rem; vertical-align: middle; }
    .deped-table th { background-color: #f3f4f6; font-weight: 800; text-transform: uppercase; text-align: center; }
    .category-header { background-color: #e5e7eb; font-weight: 900; font-size: 1.1rem; }
    .sub-category-header { background-color: #f9fafb; font-weight: 800; font-size: 0.95rem; color: #4b5563; }
</style>

<div class="flex-1 p-6 bg-white min-h-screen relative" x-data="nkpData()">
    <div class="max-w-5xl mx-auto">
        
        <div class="flex justify-between items-start mb-6 border-b-4 border-black pb-4">
            <div>
                <h2 class="text-4xl font-black uppercase text-black">{{ $studentName }}</h2>
                <h3 class="text-2xl font-bold text-blue-700 uppercase">{{ $sectionName }} (Kindergarten Progress Report)</h3>
                <p class="text-sm font-bold text-blue-600 uppercase mt-1">Active Window: Term <span x-text="activeTerm"></span></p>
            </div>
            
            <div class="flex flex-col items-end space-y-3">
                <button onclick="window.history.back()" class="text-red-600 text-5xl hover:scale-110 transition leading-none">
                    <i class="fa-solid fa-circle-left"></i>
                </button>
                @if($canManage ?? true)
                <div class="flex space-x-2 items-center">
                    
                    <!-- BULK FILL BUTTONS (Only visible when managing and term is unlocked) -->
                    <div x-show="isManaging && isTermUnlocked(activeTerm)" x-cloak class="flex items-center space-x-1 mr-2 bg-gray-100 p-1.5 rounded border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                        <span class="text-xs font-black uppercase mx-1">Fill Empty:</span>
                        <button @click="fillEmpty('CO')" class="px-2 py-1 bg-white border-2 border-black font-black hover:bg-gray-200 active:bg-gray-300 rounded text-sm transition-colors">CO</button>
                        <button @click="fillEmpty('DV')" class="px-2 py-1 bg-white border-2 border-black font-black hover:bg-gray-200 active:bg-gray-300 rounded text-sm transition-colors">DV</button>
                        <button @click="fillEmpty('BG')" class="px-2 py-1 bg-white border-2 border-black font-black hover:bg-gray-200 active:bg-gray-300 rounded text-sm transition-colors">BG</button>
                    </div>

                    <button @click="isManaging = !isManaging" class="font-black px-4 py-2 border-[3px] border-black rounded shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all" :class="isManaging ? 'bg-green-400' : 'bg-gray-200'">
                        <i class="fa-solid" :class="isManaging ? 'fa-unlock' : 'fa-lock'"></i> <span x-text="isManaging ? ' EDITING' : ' VIEWING'"></span>
                    </button>
                    <button x-show="isManaging" x-cloak @click="saveNKP()" class="bg-[#b26905] text-black px-4 py-2 rounded border-[3px] border-black font-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> SAVE ALL
                    </button>
                </div>
                @endif
            </div>
        </div>

        <div class="space-y-10 mb-12">
            @php
                $curriculum = [
                    'I. Sensory Perceptual and Motor Development' => [
                        'skills' => [
                            '1. Identifies external body parts and their functions',
                            '2. Identifies ways to care for and protects one\'s body',
                            '3. Demonstrates gross motor skills (locomotor, non-locomotor)',
                            '4. Moves body parts as directed',
                            '5. Demonstrates fine motor skills (tearing, cutting, rolling, molding with playdough)'
                        ]
                    ],
                    'II. Socio-emotional Development' => [
                        'skills' => [
                            '1. Identifies and expresses feelings in appropriate ways',
                            '2. Recognizes and respect feelings of others',
                            '3. Expresses needs and preferences',
                            '4. Behaves appropriately in different situations',
                            '5. Participates in classroom routines and activities',
                            '6. Follows classroom and school rules',
                            '7. Fulfills classroom responsibilities'
                        ]
                    ],
                    'III. Cognitive Development' => [
                        'skills' => [
                            '1. Identifies attributes of objects (color, shape, size)',
                            '2. Matches objects based on attributes',
                            '3. Describes objects based on attributes (shape, color, taste, texture)',
                            '4. Classifies objects by a single attribute (color, shape, size)',
                            '5. Reclassifies objects according to multiple attributes',
                            '6. Arranges objects according to specific attributes',
                            '7. Recognizes, extends and create patterns using concrete objects',
                            '8. Measures size, length, capacity and mass of objects using non-standard measuring tools',
                            '9. Identifies position of objects (in, on, over, under, top, bottom)',
                            '10. Compares quantities of objects (more/less)',
                            '11. Counts with one-to-one correspondence',
                            '12. Recognizes numerals',
                            '13. Matches numerals to objects',
                            '14. Adds and subtracts using concrete objects',
                            '15. Recognizes clock as measure of time (hours and minutes)',
                            '16. Shows awareness and care for the natural and physical environment',
                            '17. Talks about participation in cultural and religious activities',
                            '18. Shows awareness of the importance of caring for the natural and physical environment through simple practices (e.g., sorting trash, helping to clean up)',
                            '19. Predicts outcomes in familiar stories read aloud in class',
                            '20. Suggests solutions to problems in class activities and stories read aloud in class'
                        ]
                    ],
                    'IV. Language, Literacy, and Communication Development' => [
                        'sections' => [
                            'A. Listening and Viewing' => [
                                '1. Identifies familiar environmental sound',
                                '2. Recalls what happens first, middle and end in a story',
                                '3. Retells story in sequence',
                                '4. Follows 1-2 step instructions'
                            ],
                            'B. Sight Word Recognition' => [
                                '5. Recognizes non-decodable words in and out of context automatically',
                                '6. Recognizes sight words'
                            ],
                            'C. Speaking' => [
                                '7. Identifies first and last name',
                                '8. Identifies classmates, teachers, family member',
                                '9. Identifies familiar objects at home, in school and in the community',
                                '10. Uses polite greetings and courteous expressions in varied situations',
                                '11. Retells personal experiences to story events',
                                '12. Expresses ideas and feelings using phrases and simple sentences'
                            ],
                            'D. Reading' => [
                                '13. Orally segment sounds (a. syllable, b. onset and rime, c. phoneme by phoneme)',
                                '14. Identifies uppercase letters',
                                '15. Identifies lowercase letters',
                                '16. Matches upper and lowercase letters',
                                '17. Identifies letter sounds',
                                '18. Matches letters and their corresponding sounds'
                            ],
                            'E. Comprehension' => [
                                '19. Uses a variety of strategies to gain meaning of leveled texts',
                                '20. Uses print and illustrations to make meaning'
                            ],
                            'F. Concepts of Print' => [
                                '21. Demonstrates book handling skills',
                                '22. Distinguishes between letters, words, and sentences',
                                '23. Demonstrates awareness of print (left to right and top to bottom)'
                            ],
                            'G. Writing' => [
                                '24. Traces/draws/copies shapes, designs, pictures',
                                '25. Traces/copies/writes name, words',
                                '26. Writes uppercase and lowercase letters',
                                '27. Spells sight words',
                                '28. Spells simple words phonetically'
                            ]
                        ]
                    ]
                ];
            @endphp

            @foreach($curriculum as $catName => $content)
            <div class="border-black rounded-2xl bg-gray-50 mb-10 shadow-sm">                    
                <table class="w-full text-sm deped-table bg-white">
                    <thead>
                        <tr class="category-header">
                            <th class="text-left w-3/4">{{ $catName }}</th>
                            <th class="w-12">T1</th><th class="w-12">T2</th><th class="w-12">T3</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($content['skills']))
                            @foreach($content['skills'] as $skill)
                            <tr>
                                <td class="font-medium pl-4">{{ $skill }}</td>
                                @for($t=1; $t<=3; $t++)
                                <td class="p-0 text-center cursor-pointer select-none" 
                                    @click="if(isManaging && activeTerm == '{{ $t }}') cycleGrade('{{ addslashes($skill) }}', 'term{{ $t }}')">
                                    
                                    <div x-show="isManaging && activeTerm == '{{ $t }}'" 
                                         class="h-[32px] w-full flex items-center justify-center font-black text-blue-600 hover:bg-gray-200 transition-colors"
                                         :class="{'bg-red-100': !evaluations['{{ addslashes($skill) }}'].term{{ $t }}}"
                                         x-text="evaluations['{{ addslashes($skill) }}'].term{{ $t }} || '-'">
                                    </div>
                                    
                                    <span x-show="!isManaging || activeTerm != '{{ $t }}'" 
                                          class="block text-center font-black text-blue-600 text-[1rem]" 
                                          x-text="evaluations['{{ addslashes($skill) }}'].term{{ $t }}">
                                    </span>
                                </td>
                                @endfor
                            </tr>
                            @endforeach
                        @elseif(isset($content['sections']))
                            @foreach($content['sections'] as $subName => $subSkills)
                            <tr class="sub-category-header"><td colspan="4" class="italic pl-6">{{ $subName }}</td></tr>
                            @foreach($subSkills as $skill)
                            <tr>
                                <td class="font-medium pl-10">{{ $skill }}</td>
                                @for($t=1; $t<=3; $t++)
                                <td class="p-0 text-center cursor-pointer select-none" 
                                    @click="if(isManaging && activeTerm == '{{ $t }}') cycleGrade('{{ addslashes($skill) }}', 'term{{ $t }}')">
                                    
                                    <div x-show="isManaging && activeTerm == '{{ $t }}'" 
                                         class="h-[32px] w-full flex items-center justify-center font-black text-blue-600 hover:bg-gray-200 transition-colors"
                                         :class="{'bg-red-100': !evaluations['{{ addslashes($skill) }}'].term{{ $t }}}"
                                         x-text="evaluations['{{ addslashes($skill) }}'].term{{ $t }} || '-'">
                                    </div>
                                    
                                    <span x-show="!isManaging || activeTerm != '{{ $t }}'" 
                                          class="block text-center font-black text-blue-600 text-[1rem]" 
                                          x-text="evaluations['{{ addslashes($skill) }}'].term{{ $t }}">
                                    </span>
                                </td>
                                @endfor
                            </tr>
                            @endforeach
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            @endforeach
        </div>

        <div class="mt-8 p-6 border-2 border-black rounded-2xl bg-gray-50 shadow-sm">
            <h5 class="font-black text-center text-xl mb-4 uppercase">Rating Indicators</h5>
            <table class="w-full text-sm deped-table bg-white">
                <thead>
                    <tr class="bg-gray-200 text-black border-black">
                        <th class="w-1/4 font-black">RATING</th>
                        <th class="font-black">INDICATORS</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-black text-center">Consistent (CO)</td>
                        <td class="italic text-xs">
                            <ul class="list-disc ml-5">
                                <li>Always demonstrates the expected competency</li>
                                <li>Always participates in the different activities, works independently</li>
                                <li>Always performs tasks, advanced in some aspects</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-black text-center">Developing (DV)</td>
                        <td class="italic text-xs">
                            <ul class="list-disc ml-5">
                                <li>Sometimes demonstrates the competency</li>
                                <li>Sometimes participates, minimal supervision</li>
                                <li>Progresses continuously in doing assigned tasks</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-black text-center">Beginning (BG)</td>
                        <td class="italic text-xs">
                            <ul class="list-disc ml-5">
                                <li>Rarely demonstrates the expected competency</li>
                                <li>Rarely participates in class activities and/or initiates independent works</li>
                                <li>Shows interest in doing tasks but needs close supervision</li>
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>
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
                <h3 class="text-xl font-black uppercase leading-tight">Missing Values in Term <span x-text="activeTerm"></span></h3>
                <p class="font-bold text-sm mt-1">Please completely fill out all competencies for the active term before saving.</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('nkpData', () => ({
            isManaging: false, showErrorModal: false, activeTerm: '1',
            studentId: '{{ $student_id }}',
            evaluations: @json($savedEvaluations) || {},
            
            termDates: {
                term1: { start: '{{ $activeYear->term1_start ?? "" }}', end: '{{ $activeYear->term1_end ?? "" }}' },
                term2: { start: '{{ $activeYear->term2_start ?? "" }}', end: '{{ $activeYear->term2_end ?? "" }}' },
                term3: { start: '{{ $activeYear->term3_start ?? "" }}', end: '{{ $activeYear->term3_end ?? "" }}' }
            },

            init() {
                if (Array.isArray(this.evaluations)) this.evaluations = {};
                
                document.querySelectorAll('tbody tr:not(.sub-category-header)').forEach(row => {
                    let firstTd = row.querySelector('td:first-child');
                    if(firstTd) {
                        let skillText = firstTd.innerText.trim();
                        if(!['Consistent (CO)', 'Developing (DV)', 'Beginning (BG)'].includes(skillText)) {
                            if(!this.evaluations[skillText]) {
                                this.evaluations[skillText] = { category: this.findCategory(row), term1: '', term2: '', term3: '' };
                            }
                        }
                    }
                });

                this.activeTerm = this.determineActiveTerm();
            },

            findCategory(rowElement) {
                let current = rowElement.closest('div.border-black')?.querySelector('th.text-left');
                return current ? current.innerText.trim() : 'General';
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

            determineActiveTerm() {
                if (this.isTermUnlocked('3')) return '3';
                if (this.isTermUnlocked('2')) return '2';
                if (this.isTermUnlocked('1')) return '1';
                
                return '1'; 
            },
            
            cycleGrade(skill, termKey) {
                const termNumber = termKey.replace('term', '');
                if (!this.isTermUnlocked(termNumber)) return; 

                const currentVal = this.evaluations[skill][termKey] || '';
                const nextVal = {
                    '': 'CO',
                    'CO': 'DV',
                    'DV': 'BG',
                    'BG': ''
                };
                this.evaluations[skill][termKey] = nextVal[currentVal];
            },

            fillEmpty(grade) {
                let tKey = 'term' + this.activeTerm;
                Object.keys(this.evaluations).forEach(skill => {
                    if (!this.evaluations[skill][tKey]) {
                        this.evaluations[skill][tKey] = grade;
                    }
                });
            },

            async saveNKP() {
                let tKey = 'term' + this.activeTerm;
                
                let isIncomplete = Object.values(this.evaluations).some(s => String(s[tKey] || '').trim() === '');
                if (isIncomplete) {
                    this.showErrorModal = true;
                    setTimeout(() => { this.showErrorModal = false; }, 3000);
                    return;
                }

                try {
                    const response = await fetch('{{ route('reportcard.store') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ 
                            student_id: this.studentId, 
                            nkp_evaluations: this.evaluations 
                        })
                    });

                    const resData = await response.json();
                    if (response.ok) {
                        location.reload();
                    } else {
                        alert('Server Error: ' + (resData.message || JSON.stringify(resData)));
                    }
                } catch (err) {
                    alert('Save Failed: ' + err.message);
                }
            }
        }));
    });
</script>
@endsection