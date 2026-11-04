@extends('layouts.navigation')

@section('title', 'Batch Print')

@section('content')
<style>
    /* Table specific sizing for 2-per-page layout */
    .deped-table th, .deped-table td { border: 1px solid black; padding: 0.2rem; font-size: 0.7rem; }
    .deped-table th { text-align: center; font-weight: bold; background-color: #f9fafb !important; }
    
    /* PRINT ONLY CSS STYLES (Identical to your student-report-card) */
    @media print {
        body { background-color: white; }
        
        /* Hide the main layout elements (sidebar, topbar, etc.) */
        body * { visibility: hidden; }
        
        /* Only show the batch print container */
        #batch-print-area, #batch-print-area * { visibility: visible; }
        
        /* Pin it to the top left of the physical paper */
        #batch-print-area { position: absolute; left: 0; top: 0; width: 100%; }
        
        .no-print { display: none !important; }
        
        /* Force Landscape and page breaks */
        .page-break { page-break-after: always; }
        @page { size: letter landscape; margin: 0.4in; }
    }
</style>

<main class="flex-1 p-6 bg-gray-100 min-h-screen">

    @php
        // Wrap in function_exists to prevent Laravel redeclare errors
        if (!function_exists('getDescriptor')) {
            function getDescriptor($score) {
                $s = floatval($score);
                if(!$s) return $score;
                if($s>=90) return 'A'; if($s>=80) return 'B'; if($s>=75) return 'C'; if($s>=65) return 'D'; return 'E';
            }
        }
    @endphp

    <!-- Floating Buttons (Hidden on paper) -->
    <div class="fixed top-6 right-6 no-print z-50 flex gap-4">
        <!-- Since this opens in a new tab, a 'Close Tab' button is more helpful than 'Back' -->
        <button onclick="window.close()" class="bg-gray-200 text-black font-black px-6 py-3 rounded-xl border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-gray-300 transition-all uppercase tracking-wider text-sm">
            Close Tab
        </button>
        <button onclick="window.print()" class="bg-blue-600 text-white font-black px-6 py-3 rounded-xl border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:bg-blue-700 transition-all uppercase tracking-wider text-sm">
            <i class="fa-solid fa-print mr-2"></i> Print Now
        </button>
    </div>

    <!-- MAIN BATCH PRINT CONTAINER -->
    <div id="batch-print-area" class="max-w-[11in] mx-auto text-black">
        
        @foreach($students->chunk(2) as $chunk)
            <!-- grid-cols-2 puts two students side-by-side on the landscape page -->
            <div class="grid grid-cols-2 gap-10 {{ !$loop->last ? 'page-break' : '' }} p-4 print:p-0 mb-10 print:mb-0">
                
                @foreach($chunk as $student)
                    @php
                        $studentGrades = isset($grades[$student->student_id]) ? $grades[$student->student_id]->keyBy('subject_name') : collect();
                        $studentRemark = $remarks[$student->student_id] ?? null;
                        $totalScore = 0; $count = 0;
                        $teacherName = $section->teacher ? strtoupper($section->teacher->first_name . ' ' . $section->teacher->last_name) : '_________________';
                        $cleanGrade = str_replace('GRADE', '', strtoupper($section->grade_level));
                    @endphp
                    
                    <div class="w-full bg-white p-4 border-2 border-black print:border-0 rounded-xl print:rounded-none">
                        
                        <!-- 1. EXACT PRINT HEADER -->
                        <div class="mb-4">
                            <div class="flex items-center justify-center gap-3 mb-4">
                                <img src="{{ asset('images/MAILogo.png') }}" alt="Logo" class="w-12 h-12 object-contain">
                                <div class="text-center leading-tight">
                                    <h1 class="text-lg font-bold tracking-wide">MENDOZA ACADEMY, INC.</h1>
                                    <h2 class="text-[13px] font-semibold">Temporary Learner’s Performance Report</h2>
                                    <p class="text-[11px] font-bold">School Year {{ $activeYear->school_year ?? '2026-2027' }}</p>
                                </div>
                            </div>
                            
                            <div class="text-[12px] font-semibold space-y-2">
                                <div class="flex items-end">
                                    <span class="w-12">Name:</span>
                                    <span class="flex-1 border-b border-black font-normal px-2 leading-none pb-1">{{ strtoupper($student->last_name . ', ' . $student->first_name) }}</span>
                                </div>
                                <div class="flex items-end gap-2">
                                    <div class="flex items-end w-1/4">
                                        <span class="mr-1">Grade:</span>
                                        <span class="flex-1 border-b border-black text-center font-normal leading-none pb-1">{{ trim($cleanGrade) }}</span>
                                    </div>
                                    <div class="flex items-end w-1/3">
                                        <span class="mr-1">Section:</span>
                                        <span class="flex-1 border-b border-black text-center font-normal leading-none pb-1">{{ strtoupper($section->section_name) }}</span>
                                    </div>
                                    <div class="flex items-end flex-1">
                                        <span class="mr-1">LRN:</span>
                                        <span class="flex-1 border-b border-black text-center font-normal leading-none pb-1">{{ $student->lrn ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="flex items-end">
                                    <span class="w-14">Teacher:</span>
                                    <span class="flex-1 border-b border-black font-normal px-2 leading-none pb-1">{{ $teacherName }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- GRADES TABLE -->
                        <h4 class="text-center font-bold text-[11px] mb-1 uppercase">Report on Learning Progress and Achievement</h4>
                        <table class="w-full deped-table mb-3">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="w-1/3">Learning Areas</th>
                                    <th colspan="3">Term</th>
                                    <th rowspan="2" class="w-12">Final<br>Grade</th>
                                    <th rowspan="2">Remarks</th>
                                </tr>
                                <tr><th class="w-6">1</th><th class="w-6">2</th><th class="w-6">3</th></tr>
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
                                    <td colspan="4" class="text-right pr-2">General Average</td>
                                    @php $genAve = $count > 0 ? round($totalScore/$count) : ''; @endphp
                                    <td>{{ $gradeNum == 1 ? getDescriptor($genAve) : $genAve }}</td>
                                    <td>{{ $genAve ? ($genAve >= 65 ? 'Promoted' : 'Retained') : '' }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- LEGEND (Only for Grade 1) -->
                        @if($gradeNum == 1)
                        <div class="mb-3 text-[9px]">
                            <h4 class="text-center font-bold mb-1">Performance Descriptors</h4>
                            <table class="w-full deped-table text-center font-semibold">
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
                        @endif

                        <!-- TEACHER REMARKS -->
                        <table class="w-full deped-table mb-4 text-[9px]">
                            <thead><tr><th colspan="2">Teacher's Comments/Remarks</th></tr></thead>
                            <tbody>
                                <tr><td class="w-12 font-bold text-center">Term 1</td><td class="text-left h-6">{{ $studentRemark->term1 ?? '' }}</td></tr>
                                <tr><td class="font-bold text-center">Term 2</td><td class="text-left h-6">{{ $studentRemark->term2 ?? '' }}</td></tr>
                                <tr><td class="font-bold text-center">Term 3</td><td class="text-left h-6">{{ $studentRemark->term3 ?? '' }}</td></tr>
                            </tbody>
                        </table>

                        <!-- 2. EXACT PRINT FOOTER -->
                        <div class="mt-4">
                            <h4 class="font-bold text-center uppercase mb-5 text-[11px]">PARENT'S/GUARDIAN'S SIGNATURE</h4>
                            <div class="space-y-3 text-[11px] font-semibold max-w-[220px] mx-auto">
                                <div class="flex items-end gap-2">
                                    <span class="w-12">Term 1</span>
                                    <div class="flex-1 border-b border-black"></div>
                                </div>
                                <div class="flex items-end gap-2">
                                    <span class="w-12">Term 2</span>
                                    <div class="flex-1 border-b border-black"></div>
                                </div>
                                <div class="flex items-end gap-2">
                                    <span class="w-12">Term 3</span>
                                    <div class="flex-1 border-b border-black"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</main>

<!-- Auto-trigger print dialog when the page loads -->
<script>
    window.onload = function() {
        setTimeout(() => window.print(), 500);
    };
</script>
@endsection