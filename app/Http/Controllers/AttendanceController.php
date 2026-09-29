<?php

namespace App\Http\Controllers;

use App\Models\Attendance; 
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Section;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'parent') {
            $student = \App\Models\Student::where('user_id', $user->user_id)->first();
            if ($student && $student->section_id) {
                return redirect()->route('attendance.show', $student->section_id);
            }
            return "No child record found for this account.";
        }

        if ($user->role === 'teacher') {
            $sections = Section::where('teacher_id', $user->user_id)
                ->orderByRaw("
                    CASE 
                        WHEN grade_level IN ('Nursery', 'NURSERY') THEN 1
                        WHEN grade_level IN ('Kindergarten', 'Kinder', 'KINDER') THEN 2
                        WHEN grade_level IN ('Preparatory', 'Prep', 'PREPARATORY') THEN 3
                        ELSE 4 
                    END ASC
                ")
                ->orderByRaw("CAST(grade_level AS UNSIGNED) ASC")
                ->orderBy('grade_level', 'asc')
                ->orderBy('section_name', 'asc')
                ->get();

            if ($sections->count() > 1) {
                return view('teacher.select_section', compact('sections'));
            } 
            
            if ($sections->count() === 1) {
                return redirect()->route('attendance.show', $sections->first()->section_id);
            }
        }

        $sections = Section::orderByRaw("
            CASE 
                WHEN grade_level IN ('Nursery', 'NURSERY') THEN 1
                WHEN grade_level IN ('Kindergarten', 'Kinder', 'KINDER') THEN 2
                WHEN grade_level IN ('Preparatory', 'Prep', 'PREPARATORY') THEN 3
                ELSE 4 
            END ASC
        ")
        ->orderByRaw("CAST(grade_level AS UNSIGNED) ASC")
        ->orderBy('grade_level', 'asc')
        ->orderBy('section_name', 'asc')
        ->get();

        return view('attendance', compact('sections')); 
    }

    public function show($idOrSlug)
    {
        $user = Auth::user();
        $section = Section::find($idOrSlug);

        if (!$section) {
            $gradeMap = [
                'nursery'      => 'Nursery',
                'kinder'       => 'Kindergarten', 
                'kindergarten' => 'Kindergarten',
                'preparatory'  => 'Preparatory',
                'grade-1'      => '1',
                'grade-2'      => '2',
                'grade-3'      => '3',
                'grade-4'      => '4',
                'grade-5'      => '5',
                'grade-6'      => '6',
                '5'            => '5',
            ];

            $dbGradeLevel = $gradeMap[$idOrSlug] ?? $idOrSlug;
            $query = Section::where('grade_level', 'like', "%$dbGradeLevel%");

            if ($user->role === 'teacher') {
                $section = $query->where('teacher_id', $user->user_id)->first();
            } elseif ($user->role === 'parent') {
                $student = Student::where('user_id', $user->user_id)->first();
                $section = Section::find($student->section_id);
            } else {
                $section = $query->first();
            }
        }

        if (!$section) {
            return "Error: Section not found or you are not assigned to this grade.";
        }

        $students = Student::where('section_id', $section->section_id)->get();

        // 1. Get ALL unique attendance dates, sorted newest to oldest
        $allDates = Attendance::whereIn('student_id', $students->pluck('student_id'))
            ->select('attendance_date')
            ->distinct()
            ->orderBy('attendance_date', 'desc')
            ->pluck('attendance_date');

        // 2. Setup 10-day Pagination
        $perPage = 5;
        $currentPage = (int) request()->input('page', 1);
        $totalPages = max(1, (int) ceil($allDates->count() / $perPage));
        
        // 3. Get the dates for the current page, then reverse them so they display left-to-right chronologically
        $pagedDatesDesc = $allDates->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $existingDates = $pagedDatesDesc->reverse()->values()->toArray();

        // 4. Fetch only the attendance records for these 10 specific dates
        $attendances = Attendance::whereIn('student_id', $students->pluck('student_id'))
            ->whereIn('attendance_date', $existingDates)
            ->get();

        $statusMap = ['present' => 1, 'absent' => 2, 'late' => 3, 'excused' => 4];
        $attendanceMap = [];
        
        foreach($attendances as $att) {
            $attendanceMap[$att->student_id][$att->attendance_date] = $statusMap[strtolower($att->status)] ?? 0;
        }

        $grade = in_array(strtoupper($section->grade_level), ['NURSERY', 'KINDER', 'KINDERGARTEN', 'PREP', 'PREPARATORY']) 
                    ? strtolower($section->grade_level) 
                    : 'grade-' . $section->grade_level;

        return view('section-attendance', [
            'grade'         => $grade,
            'displayName'   => strtoupper($section->grade_level . ' - ' . $section->section_name),
            'students'      => $students,
            'existingDates' => $existingDates,
            'attendanceMap' => $attendanceMap,
            'canManage'     => $user->role === 'teacher' && $section->teacher_id == $user->user_id,
            'currentPage'   => $currentPage,
            'totalPages'    => $totalPages
        ]);
    }

   public function store(Request $request)
    {
        $request->validate([
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required',
            'attendance.*.date' => 'required|date',
            'attendance.*.status' => 'required', 
        ]);

        $records = $request->input('attendance', []);
        if (empty($records)) {
            return response()->json(['message' => 'No records to save.'], 400);
        }

        $firstRecord = $records[0];
        $sampleStudent = Student::find($firstRecord['student_id']);
        $sectionName = $sampleStudent->section->section_name ?? 'Unknown Section';
        $gradeLevel = $sampleStudent->grade_level ?? '';
        $attendanceDate = $firstRecord['date'];
        
        foreach ($records as $record) {
            
            // 1. Map status safely
            $rawStatus = strtolower(trim((string)$record['status']));
            $textStatus = match($rawStatus) {
                '1', 'present' => 'Present',
                '2', 'absent'  => 'Absent',
                '3', 'late'    => 'Late',
                '4', 'excused' => 'Excused',
                default        => 'Present' 
            };

            // 2. Format date perfectly for MySQL
            $dbDate = \Carbon\Carbon::parse($record['date'])->format('Y-m-d');

            // 3. STRICT MANUAL DATABASE CHECK (Replaces updateOrCreate)
            $existingAttendance = \App\Models\Attendance::where('student_id', $record['student_id'])
                                                        ->whereDate('attendance_date', $dbDate)
                                                        ->first();

            $statusChanged = false;
            $isNew = false;

            if ($existingAttendance) {
                // Strictly compare the old text to the new text
                if (strtolower($existingAttendance->status) !== strtolower($textStatus)) {
                    $existingAttendance->update(['status' => $textStatus]);
                    $statusChanged = true;
                }
            } else {
                // Create brand new record
                \App\Models\Attendance::create([
                    'student_id'      => $record['student_id'],
                    'attendance_date' => $dbDate,
                    'status'          => $textStatus
                ]);
                $isNew = true;
            }

            // 4. THE MAGIC FIX: BULLETPROOF NOTIFICATION LOGIC
            // Only trigger if the status GENUINELY changed or is NEW, AND is not 'Present'
            if (($isNew || $statusChanged) && $textStatus !== 'Present') {
                
                $student = Student::find($record['student_id']);
                
                if ($student && $student->user_id) {
                    $parent = User::find($student->user_id);
                    
                    if ($parent) {
                        $typeLabel = strtoupper($textStatus);
                        $formattedDate = \Carbon\Carbon::parse($dbDate)->timezone('Asia/Manila')->format('F j');
                        
                        $parent->notifyUser(
                            'Attendance Alert', 
                            "Notice: {$student->first_name} was marked {$typeLabel} for {$formattedDate}.", 
                            'attendance'
                        );
                    }
                }
            }
        }

        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Attendance Submitted',
            'description' => Auth::user()->username . " submitted attendance for {$gradeLevel} - {$sectionName} on {$attendanceDate}."
        ]);

        return response()->json(['message' => 'Saved Successfully!']);
    }
}