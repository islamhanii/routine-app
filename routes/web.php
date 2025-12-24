<?php

use App\Http\Controllers\GoogleController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware("guest")->group(function () {
    Route::get('/login', [GoogleController::class, 'index'])->name('login');
    Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
    Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
});
Route::middleware('auth')->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks', [TaskController::class, 'tasksByDate'])->name('tasks.byDate');
    Route::post('/', [TaskController::class, 'store'])->name('tasks.store');
    Route::post('/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
    Route::post('/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');
});

