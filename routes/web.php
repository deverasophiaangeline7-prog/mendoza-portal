<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AnnouncementImageController;
use App\Http\Controllers\SchoolCalendarController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeacherAccountController;
use App\Http\Controllers\ParentAccountController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::get('/', function () {
    return view('welcome');
});

Route::get('/about', function () {
    return view('about'); 
})->name('about');

Route::get('/tuitionfee', function () {
    return view('tuitionfee');
})->name('tuitionfee');

Route::get('/faqs', function () {
    return view('faqs');
})->name('faqs');

// Logged-in User Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [UserController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/calendar', [SchoolCalendarController::class, 'index'])->name('calendar.index');
});

// Admin Only Routes
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {

    // Account Management Overview
    Route::get('/account-management', function () {
        return view('accountmanagement'); 
    })->name('account.management');

    // Teacher Management
    Route::get('/create-teacher-account', function () {
        return view('create-teacher-account'); 
    })->name('teacher.create');
    Route::post('/account/teacher/store', [TeacherAccountController::class, 'store'])->name('account.teacher.store'); 
    Route::get('/teacher-list', [TeacherAccountController::class, 'index'])->name('teacher.list');
    Route::delete('/teacher/{id}', [TeacherAccountController::class, 'destroy'])->name('account.teacher.destroy');

    // Parent Management
    Route::get('/create-parent-account', function () {
        return view('create-parent-account'); 
    })->name('parent.create'); 
    Route::post('/account/parent/store', [ParentAccountController::class, 'store'])->name('account.parent.store');  

    // General User CRUD
    Route::post('/finalize-year', [UserController::class, 'finalize'])->name('finalize.year');
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');

    // Announcement Management
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

    // Calendar Management
    Route::get('/calendar/create', [SchoolCalendarController::class, 'create'])->name('calendar.create');
    Route::post('/calendar', [SchoolCalendarController::class, 'store'])->name('calendar.store');
    Route::get('/calendar/{schoolCalendar}/edit', [SchoolCalendarController::class, 'edit'])->name('calendar.edit');
    Route::put('/calendar/{schoolCalendar}', [SchoolCalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/delete/{id}', [SchoolCalendarController::class, 'destroy'])->name('calendar.delete');

});

require __DIR__.'/auth.php';