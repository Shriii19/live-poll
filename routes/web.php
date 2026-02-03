<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redirect root to login
Route::get('/', function () {
    return redirect('/login');
});

// Protected routes (require authentication)
Route::middleware('auth')->group(function () {
    // Poll routes
    Route::get('/polls', [PollController::class, 'index'])->name('polls.index');
    Route::get('/api/polls', [PollController::class, 'getPolls']);
    Route::get('/api/polls/{id}', [PollController::class, 'getPoll']);
    Route::post('/api/vote', [PollController::class, 'vote']);
    Route::get('/api/polls/{id}/results', [PollController::class, 'getResults']);
    
    // Admin routes
    Route::middleware('admin')->group(function () {
        Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/api/admin/polls', [AdminController::class, 'getPolls']);
        Route::get('/api/admin/polls/{id}/voters', [AdminController::class, 'getPollVoters']);
        Route::get('/api/admin/polls/{id}/voters-history', [AdminController::class, 'getAllVotersWithHistory']);
        Route::get('/api/admin/polls/{pollId}/history/{ip}', [AdminController::class, 'getVoteHistory']);
        Route::post('/api/admin/release-ip', [AdminController::class, 'releaseIP']);
        Route::post('/api/polls', [PollController::class, 'store']);
        Route::post('/api/polls/{id}/toggle', [PollController::class, 'toggleStatus']);
    });
});
