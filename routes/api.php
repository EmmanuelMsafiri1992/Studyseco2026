<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatbotController;
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