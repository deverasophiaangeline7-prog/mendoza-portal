<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AnnouncementImageController;
use App\Http\Controllers\SchoolCalendarController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// All routes inside here require the user to be logged in
Route::middleware(['auth', 'verified'])->group(function () {

    // The dashboard uses the index function in UserController
    Route::get('/dashboard', [UserController::class, 'index'])->name('dashboard');

    // Profile Routes (Default Laravel Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Announcement Routes (any logged-in user can view)
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');

    // Calendar Routes (any logged-in user can view)
    Route::get('/calendar', [SchoolCalendarController::class, 'index'])->name('calendar.index');

});

// Admin only routes - role:admin middleware restricts access
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {

    // User Management
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');

    // Announcement Management (admin only)
    Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
    Route::patch('/announcements/{announcement}/archive', [AnnouncementController::class, 'archive'])->name('announcements.archive');

    // Announcement Image Management (admin only)
    Route::post('/announcement-images', [AnnouncementImageController::class, 'store'])->name('announcement-images.store');
    Route::patch('/announcement-images/{announcementImage}/archive', [AnnouncementImageController::class, 'archive'])->name('announcement-images.archive');

    // School Calendar Management (admin only)
    Route::get('/calendar/create', [SchoolCalendarController::class, 'create'])->name('calendar.create');
    Route::post('/calendar', [SchoolCalendarController::class, 'store'])->name('calendar.store');
    Route::get('/calendar/{schoolCalendar}/edit', [SchoolCalendarController::class, 'edit'])->name('calendar.edit');
    Route::put('/calendar/{schoolCalendar}', [SchoolCalendarController::class, 'update'])->name('calendar.update');
    Route::patch('/calendar/{schoolCalendar}/archive', [SchoolCalendarController::class, 'archive'])->name('calendar.archive');

});

require __DIR__.'/auth.php';