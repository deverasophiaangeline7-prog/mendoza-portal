<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController; 
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

    // Admin User Management Routes
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    
    // NEW: Edit and Update Routes for Archiving/Modifying accounts
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    
});

require __DIR__.'/auth.php';