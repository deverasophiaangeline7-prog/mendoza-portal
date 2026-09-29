@extends('layouts.navigation')

@section('title', 'Student List')

@section('content')
<main class="flex-1 p-8 bg-white min-h-screen relative">
    
    {{-- BULLETPROOF VANILLA JS TOAST (MOVED TO BOTTOM RIGHT) --}}
    @if(request()->has('toast_status'))
        <div id="import-toast" class="fixed bottom-10 right-10 z-[999999] flex items-center justify-between gap-4 min-w-[320px] rounded-xl border-[3px] border-black {{ request('toast_status') === 'success' ? 'bg-green-100 text-green-900' : 'bg-red-100 text-red-900' }} px-5 py-4 font-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] transition-opacity duration-500">
            <div class="flex items-center gap-3">
                @if(request('toast_status') === 'success')
                    <i class="fa-solid fa-circle-check text-2xl"></i>
                @else
                    <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                @endif
                <span class="text-base">{{ request('toast_message') }}</span>
            </div>
            <button onclick="document.getElementById('import-toast').style.display='none'" class="text-2xl hover:scale-110 transition ml-2">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <script>
            setTimeout(function() {
                var toast = document.getElementById('import-toast');
                if (toast) {
                    toast.style.opacity = '0';
                    setTimeout(function() { toast.style.display = 'none'; }, 500);
                }
            }, 5000);
        </script>
    @endif

    <div class="max-w-6xl mx-auto">
        
        <div class="flex justify-between items-center mb-8 border-b-4 border-black pb-4">
            <div>
                <h2 class="text-4xl font-black text-black uppercase tracking-tight">Student List</h2>
                <h3 class="text-2xl font-bold text-amber-700 uppercase">{{ $sectionName }}</h3>
            </div>
            
            {{-- Conditionally show the back button based on section count --}}
            @if($showBackButton)
                <a href="{{ route('reportcard.index') }}" class="text-red-600 text-5xl hover:scale-110 transition leading-none">
                    <i class="fa-solid fa-circle-left"></i>
                </a>
            @endif
        </div>

        @if(auth()->user()->role === 'teacher')
            @php
                $teacherData = \App\Models\Teacher::where('user_id', auth()->id())->first();
                $assignedSubj = $teacherData ? $teacherData->assigned_subject : 'NONE';
            @endphp
            
            <div class="mb-4 inline-flex items-center gap-3 bg-blue-50 border-[3px] border-black px-4 py-2 rounded-xl shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                <i class="fa-solid fa-shield-halved text-blue-600 text-xl"></i>
                <span class="font-bold text-gray-800">
             You are assigned to grade <span class="text-red-600 font-black uppercase">{{ $assignedSubj }}</span>
                </span>
            </div>
        @endif

        {{-- NEW EXCEL SUBJECT IMPORT FORM --}}
        @if(auth()->user()->role === 'teacher')
        <form action="{{ route('batch.import', $section_id) }}" method="POST" enctype="multipart/form-data" class="mb-6 flex flex-wrap items-center justify-end gap-3">
            @csrf
            
          <select name="subject" required class="border-[3px] border-black rounded-xl px-3 py-2 font-black text-black bg-white shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
            <option value="" {{ stripos($assignedSubj, 'ALL') === false ? 'disabled' : '' }}>SELECT SUBJECT</option>
            
            @php
                preg_match('/\d+/', $sectionName, $matches);
                $gradeNum = isset($matches[0]) ? (int)$matches[0] : 0;

                if ($gradeNum == 1) {
                    $subjectList = ['Language', 'Reading and Literacy', 'Mathematics', 'Makabansa', 'GMRC'];
                } elseif ($gradeNum == 2 || $gradeNum == 3) {
                    $subjectList = ['English', 'Filipino', 'Mathematics', 'Makabansa', 'GMRC'];
                } elseif ($gradeNum >= 4 && $gradeNum <= 6) {
                    $subjectList = ['Filipino', 'English', 'Mathematics', 'Science', 'Araling Panlipunan', 'GMRC', 'TLE', 'MAPEH'];
                } else {
                    $subjectList = [];
                }
            @endphp

            @foreach($subjectList as $subj)
                @php
                    $hasAccess = stripos($assignedSubj, 'ALL') !== false || stripos($assignedSubj, $subj) !== false;
                @endphp
                
                <option value="{{ $subj }}" 
                    {{ !$hasAccess ? 'disabled' : '' }}
                    class="{{ !$hasAccess ? 'text-gray-300 bg-gray-100' : 'text-black font-bold' }}"
                    {{ stripos($assignedSubj, $subj) !== false && stripos($assignedSubj, 'ALL') === false ? 'selected' : '' }}>
                    {{ $subj }}
                </option>
            @endforeach
        </select>
            
            <div x-data="{ fileName: '' }">
                <label :class="fileName ? 'bg-red-500 text-white' : 'bg-white text-black'" 
                    class="cursor-pointer border-[3px] border-black rounded-xl px-3 py-2 font-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] transition-colors inline-flex items-center">
                    
                    <i class="fa-solid fa-file-excel mr-2" :class="fileName ? 'text-white' : 'text-black'"></i>
                    
                    <span class="mr-2" x-text="fileName ? fileName : 'Choose XLSX'"></span>
                    
                    <input type="file" name="excel_file" accept=".xlsx, .xls" required class="hidden" 
                        @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''">
                </label>
            </div>

            <button type="submit" class="bg-[#b26905] text-black font-black px-4 py-2 border-[3px] border-black rounded shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all">
                IMPORT GRADES
            </button>
        </form>
        @endif
       
        {{-- SEARCH BAR --}}
        <div class="mb-6 flex justify-end">
            <div class="relative w-full md:w-1/3">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-gray-500"></i>
                </div>
                <input type="text" id="studentSearch" placeholder="Search by LRN or Name..." 
                    class="w-full pl-10 pr-4 py-2 border-[3px] border-black rounded-xl font-bold text-black bg-white shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] focus:outline-none transition-all placeholder-gray-500">
            </div>
        </div>

        <!-- Changed overflow-hidden to overflow-x-auto to enable mobile scrolling -->
        <div class="border-[3px] border-black rounded-xl overflow-hidden shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] bg-white">
            <table class="w-full min-w-[600px] text-left border-collapse">
                <thead>
                    <tr class="bg-[#e5e7eb] border-b-[3px] border-black text-black">
                        <th class="py-3 px-4 border-r-[3px] border-black w-16 text-center font-bold text-sm">No.</th>
                        <th class="py-3 px-4 border-r-[3px] border-black w-32 text-center font-bold text-sm uppercase">LRN</th>
                        <th class="py-3 px-4 font-bold text-sm uppercase">Learner's Name</th>
                        <th class="py-3 px-4 border-l-[3px] border-black w-32 text-center font-bold text-sm"></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- MALE SECTION -->
                    <tr class="bg-[#e5e7eb] border-b-[3px] border-black text-black">
                        <td class="py-2 px-4 font-bold text-sm italic uppercase" colspan="4">MALE</td>
                    </tr>
                    
                    @php
                        $males = isset($students) ? $students->where('gender', 'Male')->sortBy(['last_name', 'first_name']) : $maleStudents;
                        $count = 1;
                    @endphp

                    @if($males->isEmpty())
                        <tr class="border-b-[3px] border-black bg-white text-black">
                            <td colspan="4" class="py-4 text-center font-bold text-sm text-[#5a6270]">No male students found.</td>
                        </tr>
                    @else
                        @foreach($males as $student)
                        <tr class="border-b-[3px] border-black hover:bg-yellow-50 transition-colors text-black bg-white">
                            <td class="py-3 px-4 text-center font-bold text-sm border-r-[3px] border-black text-black">{{ $count++ }}</td>
                            <td class="py-3 px-4 text-center font-bold text-sm border-r-[3px] border-black">{{ $student->lrn }}</td>
                            <td class="py-3 px-4 font-bold text-sm uppercase">{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}</td>
                            <td class="py-2 px-4 text-center border-l-[3px] border-black bg-white">
                                <a href="{{ route('reportcard.showStudent', $student->student_id) }}" 
                                   class="bg-[#b47214] hover:bg-[#965c0d] text-black px-6 py-1 rounded-lg font-bold text-sm border-[2px] border-black active:translate-x-[1px] active:translate-y-[1px] transition-all inline-block uppercase">
                                    VIEW
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    @endif

                    <!-- FEMALE SECTION -->
                    <tr class="bg-[#e5e7eb] border-b-[3px] border-black text-black">
                        <td class="py-2 px-4 font-bold text-sm italic uppercase" colspan="4">FEMALE</td>
                    </tr>
                    
                    @php
                        $females = isset($students) ? $students->where('gender', 'Female')->sortBy(['last_name', 'first_name']) : $femaleStudents;
                        $count = 1;
                    @endphp

                    @if($females->isEmpty())
                        <tr class="bg-white text-black">
                            <td colspan="4" class="py-4 text-center font-bold text-sm text-[#5a6270]">No female students found.</td>
                        </tr>
                    @else
                        @foreach($females as $student)
                        <tr class="border-b-[3px] border-black last:border-b-0 hover:bg-yellow-50 transition-colors text-black bg-white">
                            <td class="py-3 px-4 text-center font-bold text-sm border-r-[3px] border-black text-black">{{ $count++ }}</td>
                            <td class="py-3 px-4 text-center font-bold text-sm border-r-[3px] border-black">{{ $student->lrn }}</td>
                            <td class="py-3 px-4 font-bold text-sm uppercase">{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}</td>
                            <td class="py-2 px-4 text-center border-l-[3px] border-black bg-white">
                                <a href="{{ route('reportcard.showStudent', $student->student_id) }}" 
                                   class="bg-[#b47214] hover:bg-[#965c0d] text-black px-6 py-1 rounded-lg font-bold text-sm border-[2px] border-black active:translate-x-[1px] active:translate-y-[1px] transition-all inline-block uppercase">
                                    VIEW
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

    </div>
</main>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('studentSearch');
        const tableBody = document.querySelector('tbody');
        const rows = tableBody.querySelectorAll('tr');

        searchInput.addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();

            rows.forEach(row => {
                const firstCell = row.querySelector('td');
                if (firstCell && firstCell.hasAttribute('colspan')) {
                    return; 
                }

                const lrnCell = row.cells[1];
                const nameCell = row.cells[2];

                if (lrnCell && nameCell) {
                    const lrn = lrnCell.textContent.toLowerCase();
                    const name = nameCell.textContent.toLowerCase();

                    if (lrn.includes(searchTerm) || name.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        });
    });
</script>
@endsection