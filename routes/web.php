<?php

use App\Http\Controllers\GoogleController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/change-language/{locale}', [LanguageController::class, 'changeLanguage'])->name('language.change');

Route::middleware('change-language')->group(function () {
    Route::middleware("guest")->group(function () {
        Route::get('/login', [GoogleController::class, 'index'])->name('login');
        Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
        Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
    });
    Route::middleware('auth')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks', [TaskController::class, 'tasksByDate'])->name('tasks.byDate');
        Route::post('/', [TaskController::class, 'store'])->name('tasks.store');
        Route::put('/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
        Route::put('/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');
        Route::delete('/delete', [TaskController::class, 'delete'])->name('tasks.delete');
    });
});

