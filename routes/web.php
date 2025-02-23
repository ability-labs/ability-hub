<?php

use App\Http\Controllers\LanguageSelectorController;
use App\Http\Controllers\LearnerController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Learners Section Routes
    Route::resource('learners', LearnerController::class);
});

Route::post('/locale', LanguageSelectorController::class)
    ->name('locale.update');


require __DIR__.'/auth.php';
