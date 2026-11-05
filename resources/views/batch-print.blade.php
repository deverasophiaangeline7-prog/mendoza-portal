@extends('layouts.navigation')

@section('title', 'Batch Print')

@section('content')
<style>
    /* Standard Table Styling */
    .deped-table th, .deped-table td { border: 1px solid black; padding: 0.15rem 0.25rem; font-size: 0.65rem; }
    .deped-table th { text-align: center; font-weight: bold; background-color: #f9fafb !important; }
    
    @media print {
        /* 1. Kill the Sidebar and Navigation completely so they don't take up ghost space */
        nav, aside, header, footer { display: none !important; }
        
        /* 2. Rip the main content out of the dashboard layout and pull it to the top-left edge */
        main { 
            position: absolute !important; 
            top: 0 !important; 
            left: 0 !important; 
            width: 100% !important; 
            max-width: 100% !important;
            margin: 0 !important; 
            padding: 0 !important; 
            background-color: white !important;
        }

        .no-print { display: none !important; }
        
        /* 3. Set standard Landscape with clean margins */
        @page { size: landscape; margin: 0.25in; }
        
        /* 4. Force Row layout for 2 students per page */
        .print-row {
            display: flex !important;
            flex-direction: row !important;
            justify-content: space-between !important;
            width: 100% !important;
            page-break-after: always !important;
        }
        
        /* 5. Give each student exactly 48.5% of the paper */
        .print-card { 
            width: 48.5% !important; 
            page-break-inside: avoid !important; 
        }

        /* 6. Tighten margins to prevent vertical spilling to a blank second page */
        .deped-table { margin-bottom: 2px !important; }
        .deped-table th, .deped-table td { padding: 1px 2px !important; font-size: 8px !important; }
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

    <!-- MAIN PRINT CONTAINER -->
    <div id="batch-print-area" class="w-full text-black">
        
        @foreach($students->chunk(2) as $chunk)
            <!-- The Row containing exactly 2 students -->
            <div class="print-row flex flex-col md:flex-row justify-between w-full {{ !$loop->last ? 'mb-10 print:mb-0' : '' }}">
                
                @foreach($chunk as $student)
                    @php
                        $studentGrades = isset($grades[$student->student_id]) ? $grades[$student->student_id]->keyBy('subject_name') : collect();
                        $studentRemark = $remarks[$student->student_id] ?? null;
                        $totalScore = 0; $count = 0;
                        $cleanGrade = str_replace('GRADE', '', strtoupper($section->grade_level));
                    @endphp
                    
                    <!-- The 48.5% Card -->
                    <div class="print-card w-full md:w-[48.5%] bg-white p-4 print:p-0 border-2 border-black print:border-0 mb-6 md:mb-0 flex flex-col">
                        
                        <!-- HEADER -->
                        <div>
                            <div class="flex items-center justify-center gap-2 mb-2">
                                <img src="{{ asset('images/MAILogo.png') }}" alt="Logo" class="w-10 h-10 print:w-8 print:h-8 object-contain">
                                <div class="text-center leading-tight">
                                    <h1 class="text-[12px] font-bold tracking-wide">MENDOZA ACADEMY, INC.</h1>
                                    <h2 class="text-[10px] font-semibold">Temporary Learner’s Performance Report</h2>
                                    <p class="text-[8px] font-bold mt-0.5">School Year {{ $activeYear->school_year ?? '2026-2027' }}</p>
                                </div>
                            </div>
                            
                            <div class="text-[10px] font-semibold space-y-1 mb-2 border-b border-black pb-1">
                                <div class="flex items-end">
                                    <span class="w-10">Name:</span>
                                    <span class="flex-1 border-b border-black font-normal px-1 leading-none pb-0.5">{{ strtoupper($student->last_name . ', ' . $student->first_name) }}</span>
                                </div>
                                <div class="flex items-end gap-2">
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
                                        <th rowspan="2" class="w-8">Final<br>Grade</th>
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
                        <div class="mb-2 text-[8px]">
                            <h4 class="text-center font-bold mb-0.5">Performance Descriptors</h4>
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

                        <!-- REMARKS -->
                        <div>
                            <table class="w-full deped-table mb-2 text-[8px]">
                                <thead><tr><th colspan="2">Teacher's Comments/Remarks</th></tr></thead>
                                <tbody>
                                    <tr><td class="w-12 font-bold text-center py-1">Term 1</td><td class="text-left h-4 px-1">{{ $studentRemark->term1 ?? '' }}</td></tr>
                                    <tr><td class="font-bold text-center py-1">Term 2</td><td class="text-left h-4 px-1">{{ $studentRemark->term2 ?? '' }}</td></tr>
                                    <tr><td class="font-bold text-center py-1">Term 3</td><td class="text-left h-4 px-1">{{ $studentRemark->term3 ?? '' }}</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- SIGNATURE -->
                        <div class="mt-2 border-t border-black pt-2">
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