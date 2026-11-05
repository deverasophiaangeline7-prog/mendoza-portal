@extends('layouts.navigation')

@section('title', 'Batch Print')

@section('content')
<style>
    .deped-table th, .deped-table td { border: 1px solid black; padding: 0.1rem 0.3rem; font-size: 0.65rem; }
    .deped-table th { text-align: center; font-weight: bold; background-color: #f9fafb !important; }
    
    @media print {
        body { background-color: white; margin: 0; padding: 0; }
        body * { visibility: hidden; }
        #printable-area, #printable-area * { visibility: visible; }
        
        #printable-area { position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 0; }
        .no-print { display: none !important; }
        
        /* 1. Strict PORTRAIT with tight margins */
        @page { size: letter portrait; margin: 0.25in !important; }
        
        /* 2. One physical sheet of paper (8in wide x 10.5in tall printable area) */
        .print-sheet {
            width: 100% !important;
            height: 10.5in !important;
            display: flex !important;
            flex-direction: column !important; /* Stack vertically */
            page-break-after: always !important;
            overflow: hidden !important;
            background: white;
            box-sizing: border-box;
        }
        
        /* 3. Exactly half the page per student */
        .print-card {
            width: 100% !important;
            height: 50% !important;
            padding: 0.2in 0.4in !important;
            box-sizing: border-box !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between;
            border-bottom: 1px dashed #999;
        }
        .print-card:last-child { border-bottom: none; }
        
        /* 4. Tightly pack tables so they fit in the 5-inch height limit */
        .deped-table { margin-bottom: 4px !important; }
        .deped-table th, .deped-table td { padding: 2px 4px !important; font-size: 9px !important; }
    }
</style>

<main class="flex-1 p-6 bg-gray-100 min-h-screen">
    @php
        if (!function_exists('getDescriptor')) {
            function getDescriptor($score) {
                $s = floatval($score);
                if(!$s) return $score;
                if($s>=90) return 'A'; if($s>=80) return 'B'; if($s>=75) return 'C'; if($s>=65) return 'D'; return 'E';
            }
        }
    @endphp

    <div class="fixed top-6 right-6 no-print z-50 flex gap-4">
        <button onclick="window.close()" class="bg-gray-200 text-black font-black px-6 py-3 rounded-xl border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-gray-300 transition-all uppercase tracking-wider text-sm">
            Close Tab
        </button>
        <button onclick="window.print()" class="bg-blue-600 text-white font-black px-6 py-3 rounded-xl border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-blue-700 transition-all uppercase tracking-wider text-sm">
            <i class="fa-solid fa-print mr-2"></i> Print Now
        </button>
    </div>

    <div id="printable-area" class="w-full text-black">
        @foreach($students->chunk(2) as $chunk)
            <!-- A single Portrait Sheet -->
            <div class="print-sheet mb-10 print:mb-0 w-full max-w-3xl mx-auto flex flex-col bg-white shadow-xl print:shadow-none border-[3px] border-black print:border-0">
                
                @foreach($chunk as $student)
                    @php
                        $studentGrades = isset($grades[$student->student_id]) ? $grades[$student->student_id]->keyBy('subject_name') : collect();
                        $studentRemark = $remarks[$student->student_id] ?? null;
                        $totalScore = 0; $count = 0;
                        $cleanGrade = str_replace('GRADE', '', strtoupper($section->grade_level));
                    @endphp
                    
                    <!-- Exactly Half the Page (Top or Bottom) -->
                    <div class="print-card w-full p-6 border-b-[3px] border-dashed border-gray-400 print:border-b last:border-b-0">
                        
                        <!-- HEADER -->
                        <div>
                            <div class="flex items-center justify-center gap-3 mb-2">
                                <img src="{{ asset('images/MAILogo.png') }}" alt="Logo" class="w-10 h-10 print:w-8 print:h-8 object-contain">
                                <div class="text-center leading-tight">
                                    <h1 class="text-[14px] font-bold tracking-wide">MENDOZA ACADEMY, INC.</h1>
                                    <h2 class="text-[11px] font-semibold">Temporary Learner’s Performance Report</h2>
                                    <p class="text-[9px] font-bold">School Year {{ $activeYear->school_year ?? '2026-2027' }}</p>
                                </div>
                            </div>
                            
                            <div class="text-[10px] font-semibold space-y-1 mb-2">
                                <div class="flex items-end">
                                    <span class="w-12">Name:</span>
                                    <span class="flex-1 border-b border-black font-normal px-1 leading-none pb-0.5">{{ strtoupper($student->last_name . ', ' . $student->first_name) }}</span>
                                </div>
                                <div class="flex items-end gap-4">
                                    <div class="flex items-end w-1/4">
                                        <span class="mr-1">Grade:</span>
                                        <span class="flex-1 border-b border-black text-center font-normal leading-none pb-0.5">{{ trim($cleanGrade) }}</span>
                                    </div>
                                    <div class="flex items-end w-1/3">
                                        <span class="mr-1">Section:</span>
                                        <span class="flex-1 border-b border-black text-center font-normal leading-none pb-0.5">{{ strtoupper($section->section_name) }}</span>
                                    </div>
                                    <div class="flex items-end flex-1">
                                        <span class="mr-1">LRN:</span>
                                        <span class="flex-1 border-b border-black text-center font-normal leading-none pb-0.5">{{ $student->lrn ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="flex items-end">
                                    <span class="w-12">Teacher:</span>
                                    <span class="flex-1 border-b border-black font-normal px-1 leading-none pb-0.5">{{ $teacherName }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- GRADES TABLE -->
                        <div>
                            <h4 class="text-center font-bold text-[9px] mb-1 uppercase">Report on Learning Progress and Achievement</h4>
                            <table class="w-full deped-table mb-2">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="w-1/3">Learning Areas</th>
                                        <th colspan="3">Term</th>
                                        <th rowspan="2" class="w-12">Final Grade</th>
                                        <th rowspan="2" class="w-20">Remarks</th>
                                    </tr>
                                    <tr><th class="w-8">1</th><th class="w-8">2</th><th class="w-8">3</th></tr>
                                </thead>
                                <tbody class="text-center font-bold">
                                    @foreach($subjects as $subj)
                                        @php
                                            $g = $studentGrades[$subj] ?? null;
                                            $t1 = $g->term1 ?? ''; $t2 = $g->term2 ?? ''; $t3 = $g->term3 ?? '';
                                            $final = $g->final_grade ?? ''; $rem = $g->remarks ?? '';
                                            if(is_numeric($final)) { $totalScore += $final; $count++; }
                                        @endphp
                                        <tr>
                                            <td class="text-left">{{ $subj }}</td>
                                            <td>{{ $gradeNum == 1 ? getDescriptor($t1) : $t1 }}</td>
                                            <td>{{ $gradeNum == 1 ? getDescriptor($t2) : $t2 }}</td>
                                            <td>{{ $gradeNum == 1 ? getDescriptor($t3) : $t3 }}</td>
                                            <td>{{ $gradeNum == 1 ? getDescriptor($final) : $final }}</td>
                                            <td>{{ $rem }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td colspan="4" class="text-right pr-2 uppercase">General Average</td>
                                        @php $genAve = $count > 0 ? round($totalScore/$count) : ''; @endphp
                                        <td>{{ $gradeNum == 1 ? getDescriptor($genAve) : $genAve }}</td>
                                        <td>{{ $genAve ? ($genAve >= 65 ? 'Promoted' : 'Retained') : '' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- LEGEND -->
                        @if($gradeNum == 1)
                        <div class="mb-2">
                            <h4 class="text-center font-bold text-[8px] mb-0.5">Performance Descriptors</h4>
                            <div class="max-w-xs mx-auto">
                                <table class="w-full deped-table text-center font-semibold text-[7px]">
                                    <thead><tr><th>Scale</th><th>Descriptors</th><th>Remarks</th></tr></thead>
                                    <tbody>
                                        <tr><td>90-100</td><td>Advancing</td><td>Passed</td></tr>
                                        <tr><td>80-89</td><td>Benchmarking</td><td>Passed</td></tr>
                                        <tr><td>75-79</td><td>Connecting</td><td>Passed</td></tr>
                                        <tr><td>65-74</td><td>Developing</td><td>Passed</td></tr>
                                        <tr><td>0-64</td><td>Emerging</td><td>Failed</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- REMARKS -->
                        <div>
                            <table class="w-full deped-table text-[8px] mb-2">
                                <thead><tr><th colspan="2">Teacher's Comments/Remarks</th></tr></thead>
                                <tbody>
                                    <tr><td class="w-16 font-bold text-center py-1">Term 1</td><td class="text-left h-4 px-1">{{ $studentRemark->term1 ?? '' }}</td></tr>
                                    <tr><td class="font-bold text-center py-1">Term 2</td><td class="text-left h-4 px-1">{{ $studentRemark->term2 ?? '' }}</td></tr>
                                    <tr><td class="font-bold text-center py-1">Term 3</td><td class="text-left h-4 px-1">{{ $studentRemark->term3 ?? '' }}</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- SIGNATURE -->
                        <div class="mt-auto pt-2 border-t border-black">
                            <h4 class="font-bold text-center uppercase mb-2 text-[9px]">PARENT'S/GUARDIAN'S SIGNATURE</h4>
                            <div class="space-y-1.5 text-[9px] font-semibold max-w-[200px] mx-auto">
                                <div class="flex items-end gap-2"><span class="w-12">Term 1</span><div class="flex-1 border-b border-black"></div></div>
                                <div class="flex items-end gap-2"><span class="w-12">Term 2</span><div class="flex-1 border-b border-black"></div></div>
                                <div class="flex items-end gap-2"><span class="w-12">Term 3</span><div class="flex-1 border-b border-black"></div></div>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</main>
<script>
    window.onload = function() {
        setTimeout(() => window.print(), 500);
    };
</script>
@endsection