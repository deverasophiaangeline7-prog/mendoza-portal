@extends('layouts.navigation')

@section('title', 'Batch Print')

@section('content')
<style>
    .deped-table th, .deped-table td { border: 1px solid black; padding: 0.1rem 0.2rem; font-size: 0.6rem; }
    .deped-table th { text-align: center; font-weight: bold; background-color: #f9fafb !important; }
    
    @media print {
        body { background-color: white; margin: 0; padding: 0; }
        body * { visibility: hidden; }
        #batch-print-area, #batch-print-area * { visibility: visible; }
        
        #printable-area { position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 0; }
        .no-print { display: none !important; }
        
        /* 1. Strict Landscape with minimal margins to guarantee 1-page fit */
        @page { size: letter landscape; margin: 0.15in !important; }
        
        .print-sheet {
            width: 11in !important;
            height: 8.2in !important;
            display: flex !important;
            flex-direction: row !important;
            page-break-after: always !important;
            overflow: hidden !important;
            background: white;
            box-sizing: border-box;
        }
        
        .print-card {
            width: 5.4in !important;
            height: 8.2in !important;
            padding: 0.15in 0.25in !important;
            box-sizing: border-box !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between;
            border-right: 1px dashed #999;
        }
        .print-card:last-child { border-right: none; }
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
            <div class="print-sheet mb-10 print:mb-0 w-full flex bg-white shadow-xl print:shadow-none mx-auto">
                @foreach($chunk as $student)
                    @php
                        $studentGrades = isset($grades[$student->student_id]) ? $grades[$student->student_id]->keyBy('subject_name') : collect();
                        $studentRemark = $remarks[$student->student_id] ?? null;
                        $totalScore = 0; $count = 0;
                        $cleanGrade = str_replace('GRADE', '', strtoupper($section->grade_level));
                    @endphp
                    
                    <div class="print-card w-1/2 p-4">
                        
                        <!-- HEADER -->
                        <div>
                            <div class="flex items-center justify-center gap-2 mb-1">
                                <img src="{{ asset('images/MAILogo.png') }}" alt="Logo" class="w-8 h-8 object-contain">
                                <div class="text-center leading-tight">
                                    <h1 class="text-[11px] font-bold tracking-wide">MENDOZA ACADEMY, INC.</h1>
                                    <h2 class="text-[9px] font-semibold">Temporary Learner’s Performance Report</h2>
                                    <p class="text-[7px] font-bold">School Year {{ $activeYear->school_year ?? '2026-2027' }}</p>
                                </div>
                            </div>
                            
                            <div class="text-[9px] font-semibold space-y-0.5 border-b border-black pb-1 mb-1">
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
                            <h4 class="text-center font-bold text-[8px] mb-0.5 uppercase">Report on Learning Progress and Achievement</h4>
                            <table class="w-full deped-table mb-1">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="w-1/3">Learning Areas</th>
                                        <th colspan="3">Term</th>
                                        <th rowspan="2" class="w-6">Final<br>Grade</th>
                                        <th rowspan="2" class="w-10">Remarks</th>
                                    </tr>
                                    <tr><th class="w-5">1</th><th class="w-5">2</th><th class="w-5">3</th></tr>
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
                        </div>

                        <!-- LEGEND -->
                        @if($gradeNum == 1)
                        <div class="text-[7px] my-0.5">
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
                            <table class="w-full deped-table text-[7px] mb-1">
                                <thead><tr><th colspan="2">Teacher's Comments/Remarks</th></tr></thead>
                                <tbody>
                                    <tr><td class="w-10 font-bold text-center py-0.5">Term 1</td><td class="text-left h-3 px-1">{{ $studentRemark->term1 ?? '' }}</td></tr>
                                    <tr><td class="font-bold text-center py-0.5">Term 2</td><td class="text-left h-3 px-1">{{ $studentRemark->term2 ?? '' }}</td></tr>
                                    <tr><td class="font-bold text-center py-0.5">Term 3</td><td class="text-left h-3 px-1">{{ $studentRemark->term3 ?? '' }}</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- SIGNATURE -->
                        <div class="border-t border-black pt-1">
                            <h4 class="font-bold text-center uppercase mb-1 text-[8px]">PARENT'S/GUARDIAN'S SIGNATURE</h4>
                            <div class="space-y-1 text-[8px] font-semibold max-w-[180px] mx-auto">
                                <div class="flex items-end gap-1"><span class="w-8">Term 1</span><div class="flex-1 border-b border-black"></div></div>
                                <div class="flex items-end gap-1"><span class="w-8">Term 2</span><div class="flex-1 border-b border-black"></div></div>
                                <div class="flex items-end gap-1"><span class="w-8">Term 3</span><div class="flex-1 border-b border-black"></div></div>
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