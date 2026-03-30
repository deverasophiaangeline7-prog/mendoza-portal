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

});

// Admin only routes - role:admin middleware restricts access 👇
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
});

require __DIR__.'/auth.php';