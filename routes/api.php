<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuthForgotPasswordController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\Enquiry\EnquiryController;
use App\Http\Controllers\Api\UserManagement\PasswordResetRequestController;
use App\Http\Controllers\Api\UserManagement\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthForgotPasswordController::class, 'lookup']);
        Route::post('/forgot-password/reset', [AuthForgotPasswordController::class, 'resetPassword']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        });

        Route::get('/dashboard/counts', [DashboardController::class, 'counts'])->middleware('can:view-dashboard');

        Route::prefix('user-management')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->middleware('can:view-users');
            Route::post('/users', [UserController::class, 'store'])->middleware('can:create-users');
            Route::get('/users/assignable-roles', [UserController::class, 'assignableRoles'])->middleware('can:view-users');
            Route::get('/users/{user}', [UserController::class, 'show'])->middleware('can:view-users');
            Route::put('/users/{user}', [UserController::class, 'update'])->middleware('can:edit-users');
            Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->middleware('can:edit-users');
            Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('can:delete-users');

            Route::get('/password-reset-requests', [PasswordResetRequestController::class, 'index'])->middleware('can:manage-password-reset-requests');
            Route::patch('/password-reset-requests/{passwordResetRequest}/approve', [PasswordResetRequestController::class, 'approve'])->middleware('can:manage-password-reset-requests');
            Route::patch('/password-reset-requests/{passwordResetRequest}/decline', [PasswordResetRequestController::class, 'decline'])->middleware('can:manage-password-reset-requests');
        });

        Route::prefix('enquiries')->group(function () {
            Route::get('/', [EnquiryController::class, 'index'])->middleware('can:view-enquiries');
            Route::post('/', [EnquiryController::class, 'store'])->middleware('can:create-enquiries');
            Route::get('/filters', [EnquiryController::class, 'filterOptions'])->middleware('can:view-enquiries');
            Route::get('/followups', [EnquiryController::class, 'followUps'])->middleware('can:view-enquiries');
            Route::get('/followups/completed', [EnquiryController::class, 'completedFollowUps'])->middleware('can:view-enquiries');
            Route::get('/followups/{followUp}', [EnquiryController::class, 'showFollowUp'])->middleware('can:view-enquiries');
            Route::get('/{enquiry}', [EnquiryController::class, 'show'])->middleware('can:view-enquiries');
            Route::put('/{enquiry}', [EnquiryController::class, 'update'])->middleware('can:edit-enquiries');
            Route::patch('/{enquiry}/status', [EnquiryController::class, 'updateStatus'])->middleware('can:edit-enquiries');
            Route::patch('/{enquiry}/followup-complete', [EnquiryController::class, 'completeFollowUp'])->middleware('can:edit-enquiries');
            Route::put('/followups/{followUp}', [EnquiryController::class, 'updateFollowUp'])->middleware('can:edit-enquiries');
            Route::delete('/{enquiry}', [EnquiryController::class, 'destroy'])->middleware('can:delete-enquiries');
        });
    });
});
