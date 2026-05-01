<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AnnouncementImageController;
use App\Http\Controllers\SchoolCalendarController;
use App\Http\Controllers\TeacherAccountController;
use App\Http\Controllers\ParentAccountController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ParentAttendanceController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\StudentCalendarController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\Admin\StudentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. PUBLIC ROUTES
// ==========================================
Route::get('/', function () { return view('welcome'); });
Route::get('/about', function () { return view('about'); })->name('about');
Route::get('/tuitionfee', function () { return view('tuitionfee'); })->name('tuitionfee');
Route::get('/faqs', function () { return view('faqs'); })->name('faqs');

Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');


// ==========================================
// 2. GENERAL LOGGED-IN USERS (View Only)
// ==========================================
    Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [SchoolCalendarController::class, 'index'])->name('dashboard');
    Route::get('/my-calendar', [StudentCalendarController::class, 'studentCalendar'])->name('student.calendar');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/section/{slug}', [StudentController::class, 'showSection'])->name('students.section');
    Route::post('/students/message', [StudentController::class, 'sendMessage'])->name('students.message');
    
    // General Views
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/calendar', [SchoolCalendarController::class, 'index'])->name('calendar.index');

    // Attendance Views
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{grade}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::get('/parent/attendance', [ParentAttendanceController::class, 'index'])->name('parent.attendance');

    // Report Card Views
    Route::get('/report-card', [ReportCardController::class, 'index'])->name('reportcard.index');
    Route::get('/report-card/list/{section_id}', [ReportCardController::class, 'show'])->name('reportcard.show');
    Route::get('/report-card/view/{student_id}', [ReportCardController::class, 'showStudent'])->name('reportcard.showStudent');
    Route::get('/my-child/report-card', [ReportCardController::class, 'showParentReportCard'])->name('parent.reportcard');
    
    });

// ==========================================
// 3. TEACHER ONLY ROUTES (Data Entry/Updates)
// ==========================================
    Route::middleware(['auth', 'teacher'])->group(function () {
    // Attendance Actions
    Route::post('/attendance/save', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/attendance/{grade}/publish', [AttendanceController::class, 'publish'])->name('attendance.publish');
    Route::post('/attendance/{grade}/update', [AttendanceController::class, 'update'])->name('attendance.update');
    
    // Report Card / Grading Actions
    Route::post('/report-card/save', [ReportCardController::class, 'store'])->name('reportcard.store');
    Route::get('/report-card/edit/{student_id}', [ReportCardController::class, 'edit'])->name('reportcard.edit');
    Route::post('/report-card/update/{student_id}', [ReportCardController::class, 'update'])->name('reportcard.update');

    // Student Participation in Events
    Route::get('/student-calendar', [StudentCalendarController::class, 'index'])->name('student.calendar.index');
    Route::post('/student-calendar/add-participant', [StudentCalendarController::class, 'addParticipant'])->name('calendar.addParticipant');
    Route::delete('/student-calendar/participant/{id}', [StudentCalendarController::class, 'destroyParticipant'])->name('calendar.deleteParticipant');

    Route::post('/teacher/students/add', [App\Http\Controllers\Teacher\StudentController::class, 'storeStudent'])->name('teacher.students.store');
    Route::delete('/teacher/students/delete/{id}', [App\Http\Controllers\Teacher\StudentController::class, 'destroyStudent'])->name('teacher.students.destroy');
    });


// ==========================================
// 4. ADMIN ONLY ROUTES (Management & CRUD)
// ==========================================
    Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    
    Route::get('/admin/student-participation', [StudentCalendarController::class, 'index'])->name('admin.student.participation');
    Route::get('/students/view/{id}', [App\Http\Controllers\Admin\StudentController::class, 'showStudent'])->name('students.showStudent');

    // Accounts Overview
    Route::get('/account-management', function () { return view('accountmanagement'); })->name('account.management');

    // Teacher Management
    Route::get('/create-teacher-account', [TeacherAccountController::class, 'create'])->name('teacher.create');
    Route::post('/account/teacher/store', [TeacherAccountController::class, 'store'])->name('account.teacher.store'); 
    Route::get('/teacher-list', [TeacherAccountController::class, 'index'])->name('teacher.list');
    Route::delete('/teacher/{id}', [TeacherAccountController::class, 'destroy'])->name('account.teacher.destroy');

    // Parent Management
    Route::get('/create-parent-account', [ParentAccountController::class, 'create'])->name('parent.create'); 
    Route::post('/account/parent/store', [ParentAccountController::class, 'store'])->name('account.parent.store');
    Route::get('/parent-list', [ParentAccountController::class, 'index'])->name('parent.list');
    Route::get('/account/parents/{grade}', [ParentAccountController::class, 'showGrade'])->name('grade.show');

    // User General CRUD
    Route::post('/finalize-year', [UserController::class, 'finalize'])->name('finalize.year');
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');

    // Announcements
    Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
    Route::patch('/announcements/{announcement}/archive', [AnnouncementController::class, 'archive'])->name('announcements.archive');

    // Announcement Images
    Route::post('/announcement-images', [AnnouncementImageController::class, 'store'])->name('announcement-images.store');
    Route::patch('/announcement-images/{announcementImage}/archive', [AnnouncementImageController::class, 'archive'])->name('announcement-images.archive');
    Route::get('/announcement-images/archived', [AnnouncementImageController::class, 'archivedIndex'])->name('announcement-images.archived');
    Route::patch('/announcement-images/{announcementImage}/restore', [AnnouncementImageController::class, 'restore'])->name('announcement-images.restore');

    // Calendar
    Route::get('/calendar/create', [SchoolCalendarController::class, 'create'])->name('calendar.create');
    Route::post('/calendar', [SchoolCalendarController::class, 'store'])->name('calendar.store');
    Route::get('/calendar/{schoolCalendar}/edit', [SchoolCalendarController::class, 'edit'])->name('calendar.edit');
    Route::put('/calendar/{schoolCalendar}', [SchoolCalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/delete/{id}', [SchoolCalendarController::class, 'destroy'])->name('calendar.delete');

    // The route for loading the table (change {slug} to {id})
    Route::get('/students/section/{id}', [App\Http\Controllers\Admin\StudentController::class, 'showSection'])->name('students.showSection');

    // The two new routes for adding and deleting
    Route::post('/sections/add', [App\Http\Controllers\Admin\StudentController::class, 'storeSection'])->name('sections.store');
    Route::delete('/sections/delete/{id}', [App\Http\Controllers\Admin\StudentController::class, 'destroySection'])->name('sections.destroy');

    Route::post('/admin/students/add', [App\Http\Controllers\Admin\StudentController::class, 'storeStudent'])->name('admin.students.store');
    Route::delete('/admin/students/delete/{id}', [App\Http\Controllers\Admin\StudentController::class, 'destroyStudent'])->name('admin.students.destroy');
    });

    // Both Admins and Teachers are allowed inside this group!
    Route::middleware(['auth', 'role:admin,teacher'])->group(function () {
    Route::post('/students/add', [App\Http\Controllers\Admin\StudentController::class, 'storeStudent'])->name('students.store');
    Route::delete('/students/delete/{id}', [App\Http\Controllers\Admin\StudentController::class, 'destroyStudent'])->name('students.destroy');
    
    });

    // Only users with the 'parent' role can access these routes
    Route::middleware(['auth', 'role:parent'])->group(function () {
    // The name here has a DOT, so the route() call must have a DOT
    Route::get('/student-view', [ParentAccountController::class, 'showStudentProfile'])->name('student.view');
    });

require __DIR__.'/auth.php';