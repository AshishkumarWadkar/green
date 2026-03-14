<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserManagement\UserController;
use App\Http\Controllers\UserManagement\RoleController;
use App\Http\Controllers\EnquiryController;

// Public routes (no authentication required)
Route::middleware('guest')->group(function () {
  // Authentication routes
  Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
  Route::post('/login', [AuthController::class, 'login'])->name('login.post');
  
  // Redirect old login URLs for backward compatibility
  Route::get('/auth/login-basic', function () {
    return redirect()->route('login');
  });
  Route::get('/auth/login', function () {
    return redirect()->route('login');
  });
});

// Authenticated routes (authentication required)
Route::middleware('auth')->group(function () {
  // Main Page Route (require view-dashboard permission)
  Route::get('/', [HomePage::class, 'index'])->name('pages-home')->middleware('can:view-dashboard');
  
  // Logout route
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
  Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');

  // Enquiry Routes
  Route::prefix('enquiries')->name('enquiries.')->group(function () {
    Route::get('/', [EnquiryController::class, 'index'])->name('index');
    Route::get('/data', [EnquiryController::class, 'getData'])->name('data');
    Route::get('/create', [EnquiryController::class, 'create'])->name('create');
    Route::post('/', [EnquiryController::class, 'store'])->name('store');
    Route::get('/{id}', [EnquiryController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [EnquiryController::class, 'edit'])->name('edit');
    Route::put('/{id}', [EnquiryController::class, 'update'])->name('update');
    Route::patch('/{id}/status', [EnquiryController::class, 'updateStatus'])->name('update-status');
    Route::delete('/{id}', [EnquiryController::class, 'destroy'])->name('destroy');
  });
  
  // User Management Routes
  Route::prefix('user-management')->name('user-management.')->group(function () {
    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/data', [UserController::class, 'getData'])->name('users.data');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    
    // Roles
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/data', [RoleController::class, 'getData'])->name('roles.data');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
  });
});

// Public routes (accessible by all)
// locale
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
