<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendoza Academy - Grade Sheet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-gradient { background: linear-gradient(to right, #d32f2f, #8b0000); }
        [x-cloak] { display: none !important; }
        .form-input-pill { border: 2px solid black; border-radius: 0.5rem; height: 2.5rem; width: 100%; text-align: center; font-weight: 900; font-size: 1.25rem; box-shadow: 2px 2px 0px 0px rgba(0,0,0,1); }
    </style>
</head>
<body class="bg-gray-100">
    <header class="hero-gradient text-white py-4 px-6 shadow-lg flex justify-between items-center relative z-50">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('images/MAILogo.png') }}" class="h-10 w-10 bg-white p-1 rounded shadow">
            <h1 class="text-2xl font-bold uppercase tracking-tight">Mendoza Academy, Inc.</h1>
        </div>
    </header>

    <div class="flex min-h-screen">
        <nav class="w-64 bg-[#b91c1c] text-white pt-4">
            <ul class="space-y-1">
                <li><a href="{{ route('dashboard') }}" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-chart-line w-6"></i><span>Dashboard</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-user-graduate w-6"></i><span>List of Students</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-calendar-days w-6"></i><span>Student Calendar</span></a></li>
                <li class="bg-orange-400 mx-2 rounded-lg">
                    <a href="{{ route('reportcard.index') }}" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-star w-6"></i><span>Report Card</span></a></li>
                <li><a href="#" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-wallet w-6"></i><span>Tuition Fee</span></a></li>
                <li><a href="{{ route('attendance.index') }}" class="flex items-center p-3 space-x-3 hover:bg-red-800 transition"><i class="fa-solid fa-calendar-check w-6"></i><span>Attendance</span></a></li>
        </nav>

        <main class="flex-1 p-6 bg-white" x-data="gradeData()">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-start mb-6">
                    <div class="flex flex-col">
                        <h2 class="text-4xl font-black uppercase text-black">{{ $studentName }}</h2>
                        <h3 class="text-2xl font-bold text-orange-500 uppercase">{{ $sectionName }}</h3>
                    </div>
                    <button onclick="window.history.back()" class="text-red-600 text-5xl hover:scale-110 transition leading-none">
                        <i class="fa-solid fa-circle-left"></i>
                    </button>
                </div>

                @if($canManage)
                <div class="mb-6 p-4 border-[3px] border-black rounded-[20px] bg-gray-50 flex items-center justify-between shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                    <button @click="isManaging = !isManaging" class="font-black px-6 py-2 border-[3px] border-black rounded-xl shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all" :class="isManaging ? 'bg-green-400 text-black' : 'bg-gray-200 text-gray-500'">
                        <i class="fa-solid" :class="isManaging ? 'fa-unlock' : 'fa-lock'"></i> <span x-text="isManaging ? ' EDITING MODE' : ' VIEW MODE'"></span>
                    </button>
                    <button x-show="isManaging" x-cloak @click="saveGrades()" class="bg-[#ffaf2e] text-black px-8 py-2 rounded-xl border-[3px] border-black font-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px]">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> SAVE GRADES
                    </button>
                </div>
                @endif

                <div class="overflow-hidden border-[3px] border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] rounded-xl">
                    <table class="w-full text-center border-collapse bg-white">
                        <thead>
                            <tr class="border-b-[3px] border-black bg-gray-100">
                                <th rowspan="2" class="p-4 border-r-[3px] border-black text-2xl font-black">Learning Areas</th>
                                <th colspan="4" class="p-2 border-r-[3px] border-black text-2xl font-black uppercase">Quarter</th>
                                <th rowspan="2" class="p-2 border-r-[3px] border-black text-xl font-black">Final Grade</th>
                                <th rowspan="2" class="p-2 text-xl font-black">Remarks</th>
                            </tr>
                            <tr class="border-b-[3px] border-black">
                                <th class="p-2 border-r-[3px] border-black font-black text-2xl">1</th>
                                <th class="p-2 border-r-[3px] border-black font-black text-2xl">2</th>
                                <th class="p-2 border-r-[3px] border-black font-black text-2xl">3</th>
                                <th class="p-2 border-r-[3px] border-black font-black text-2xl">4</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjects as $subject)
                            <tr class="border-b-[2px] border-black h-16 hover:bg-yellow-50/50">
                                <td class="p-2 px-6 text-left font-black text-xl border-r-[3px] border-black uppercase">{{ $subject }}</td>
                                <td class="border-r-[3px] border-black px-2">
                                    <input x-show="isManaging" type="number" x-model="grades['{{ $subject }}'].q1" @input="calculateGrades()" class="form-input-pill">
                                    <span x-show="!isManaging" x-text="grades['{{ $subject }}'].q1" class="font-black text-2xl text-blue-600"></span>
                                </td>
                                <td class="border-r-[3px] border-black px-2">
                                    <input x-show="isManaging" type="number" x-model="grades['{{ $subject }}'].q2" @input="calculateGrades()" class="form-input-pill">
                                    <span x-show="!isManaging" x-text="grades['{{ $subject }}'].q2" class="font-black text-2xl text-blue-600"></span>
                                </td>
                                <td class="border-r-[3px] border-black px-2">
                                    <input x-show="isManaging" type="number" x-model="grades['{{ $subject }}'].q3" @input="calculateGrades()" class="form-input-pill">
                                    <span x-show="!isManaging" x-text="grades['{{ $subject }}'].q3" class="font-black text-2xl text-blue-600"></span>
                                </td>
                                <td class="border-r-[3px] border-black px-2">
                                    <input x-show="isManaging" type="number" x-model="grades['{{ $subject }}'].q4" @input="calculateGrades()" class="form-input-pill">
                                    <span x-show="!isManaging" x-text="grades['{{ $subject }}'].q4" class="font-black text-2xl text-blue-600"></span>
                                </td>
                                <td class="border-r-[3px] border-black px-2 bg-gray-50">
                                    <span x-text="grades['{{ $subject }}'].final_grade" class="font-black text-3xl text-green-600"></span>
                                </td>
                                <td class="px-2 bg-gray-50">
                                    <span x-text="grades['{{ $subject }}'].remarks" class="font-black text-lg" :class="grades['{{ $subject }}'].remarks === 'FAILED' ? 'text-red-600' : 'text-black'"></span>
                                </td>
                            </tr>
                            @endforeach
                            <tr class="h-16 bg-orange-50 border-t-[3px] border-black">
                                <td class="p-2 font-black text-2xl border-r-[3px] border-black text-right pr-6 uppercase" colspan="5">General Average</td>
                                <td class="border-r-[3px] border-black bg-white text-center">
                                    <span class="font-black text-4xl text-green-600" x-text="generalAverage"></span>
                                </td>
                                <td class="bg-white text-center">
                                    <span class="font-black text-xl uppercase" 
                                        :class="finalStatus === 'RETAINED' ? 'text-red-600' : 'text-blue-600'" 
                                        x-text="finalStatus"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="showToast" x-cloak class="fixed bottom-10 right-10 z-50 px-8 py-4 rounded-2xl border-[3px] border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] bg-[#4ade80] text-black">
                <i class="fa-solid fa-circle-check text-2xl mr-2"></i> <span class="font-black text-xl uppercase" x-text="toastMessage"></span>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('gradeData', () => ({
                isManaging: false, 
                showToast: false, 
                toastMessage: '',
                studentId: '{{ $student_id }}', 
                subjects: @json($subjects), 
                grades: @json($savedGrades) || {}, 
                generalAverage: '',
                finalStatus: '',

                init() {
                    // Safety: ensure grades is an object
                    if (Array.isArray(this.grades)) {
                        this.grades = {};
                    }

                    // Pre-fill missing subjects
                    this.subjects.forEach(sub => { 
                        if(!this.grades[sub]) {
                            this.grades[sub] = { q1: '', q2: '', q3: '', q4: '', final_grade: '', remarks: '' }; 
                        }
                    });
                    this.calculateGrades();
                },

                calculateGrades() {
                    let totalFinal = 0, count = 0;
                    this.subjects.forEach(sub => {
                        let g = this.grades[sub];
                        if (g.q1 && g.q2 && g.q3 && g.q4) {
                            let avg = Math.round((parseFloat(g.q1) + parseFloat(g.q2) + parseFloat(g.q3) + parseFloat(g.q4)) / 4);
                            g.final_grade = avg; 
                            g.remarks = avg >= 75 ? 'PASSED' : 'FAILED';
                            totalFinal += avg; 
                            count++;
                        } else { 
                            g.final_grade = ''; 
                            g.remarks = ''; 
                        }
                    });

                    // Calculate General Average
                    if (count > 0 && count === this.subjects.length) {
                        this.generalAverage = Math.round(totalFinal / count);
                        // NEW: Determine final status
                        this.finalStatus = this.generalAverage >= 75 ? 'PROMOTED' : 'RETAINED';
                    } else {
                        this.generalAverage = '';
                        this.finalStatus = ''; // Clear if not all grades are in
                    }
                },

                saveGrades() {
                    fetch('{{ route('reportcard.store') }}', {
                        method: 'POST', 
                        headers: { 
                            'Content-Type': 'application/json', 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                        },
                        body: JSON.stringify({ student_id: this.studentId, grades: this.grades })
                    })
                    .then(res => res.json())
                    .then(data => { 
                        this.toastMessage = 'GRADES SAVED!'; 
                        this.showToast = true; 
                        setTimeout(() => this.showToast = false, 3000); 
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        alert('Error saving grades. Check console.');
                    });
                }
            }));
        });
    </script>
</body>
</html>