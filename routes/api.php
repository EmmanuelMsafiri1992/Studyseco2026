<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileDataController;
use App\Http\Controllers\Api\MobileEnrollmentController;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

// Mobile App Authentication Routes
Route::post('/login', [MobileAuthController::class, 'login']);
Route::post('/register', [MobileAuthController::class, 'register']);
Route::post('/verify-email', [MobileAuthController::class, 'verifyEmail']);
Route::post('/resend-otp', [MobileAuthController::class, 'resendOtp']);
Route::post('/forgot-password', [MobileAuthController::class, 'forgotPassword']);
Route::post('/reset-password', [MobileAuthController::class, 'resetPassword']);

// Authenticated Mobile App Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/user', [MobileAuthController::class, 'user']);
    Route::put('/user/profile', [MobileAuthController::class, 'updateProfile']);
    Route::post('/user/change-password', [MobileAuthController::class, 'changePassword']);
    Route::post('/logout', [MobileAuthController::class, 'logout']);

    // Dashboard
    Route::get('/student/dashboard', [MobileDataController::class, 'dashboard']);
    Route::get('/student/subjects', [MobileDataController::class, 'studentSubjects']);

    // Subjects
    Route::get('/subjects', [MobileDataController::class, 'subjects']);
    Route::get('/subjects/{id}', [MobileDataController::class, 'subjectDetail']);
    Route::get('/departments', [MobileDataController::class, 'departments']);

    // Notifications
    Route::get('/notifications', [MobileDataController::class, 'notifications']);
    Route::post('/notifications/{id}/read', [MobileDataController::class, 'markNotificationRead']);
    Route::post('/notifications/read-all', [MobileDataController::class, 'markAllNotificationsRead']);
    Route::delete('/notifications/{id}', [MobileDataController::class, 'deleteNotification']);

    // Enrollment & Payments
    Route::get('/payment-methods', [MobileEnrollmentController::class, 'paymentMethods']);
    Route::get('/access-durations', [MobileEnrollmentController::class, 'accessDurations']);
    Route::get('/enrollment/subjects', [MobileEnrollmentController::class, 'availableSubjects']);
    Route::post('/enrollment/pricing', [MobileEnrollmentController::class, 'calculatePricing']);
    Route::post('/enrollment/submit', [MobileEnrollmentController::class, 'submitEnrollment']);
    Route::post('/enrollment/trial', [MobileEnrollmentController::class, 'startTrial']);
    Route::post('/enrollment/extend', [MobileEnrollmentController::class, 'extendEnrollment']);
    Route::get('/enrollment/status', [MobileEnrollmentController::class, 'enrollmentStatus']);
    Route::get('/payments', [MobileEnrollmentController::class, 'paymentHistory']);
});

// Chatbot API routes for landing page integration
Route::prefix('chatbot')->name('api.chatbot.')->group(function () {
    Route::post('/start', [ChatbotController::class, 'startChat'])->name('start');
    Route::post('/chat/{sessionId}/message', [ChatbotController::class, 'sendMessage'])->name('message');
    Route::get('/chat/{sessionId}/messages', [ChatbotController::class, 'getMessages'])->name('messages');
    Route::get('/chat/{sessionId}/status', [ChatbotController::class, 'getStatus'])->name('status');
    Route::post('/chat/{sessionId}/end', [ChatbotController::class, 'endChat'])->name('end');
});

// Chunked upload routes
Route::middleware('auth')->prefix('upload')->name('api.upload.')->group(function () {
    Route::post('/initiate', [App\Http\Controllers\ChunkedUploadController::class, 'initiate'])->name('initiate');
    Route::post('/{uploadId}/chunk', [App\Http\Controllers\ChunkedUploadController::class, 'uploadChunk'])->name('chunk');
    Route::post('/{uploadId}/finalize', [App\Http\Controllers\ChunkedUploadController::class, 'finalize'])->name('finalize');
    Route::get('/{uploadId}/status', [App\Http\Controllers\ChunkedUploadController::class, 'status'])->name('status');
    Route::delete('/{uploadId}/cancel', [App\Http\Controllers\ChunkedUploadController::class, 'cancel'])->name('cancel');
});

// User API routes for chat functionality
Route::middleware('auth')->group(function () {
    Route::get('/users', function () {
        return User::select('id', 'name', 'email')->get();
    });
});