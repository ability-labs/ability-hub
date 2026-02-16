<?php

use App\Http\Controllers\Api\Appointments\ListAppointmentsController;
use App\Http\Controllers\Api\Appointments\DuplicateWeekController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/appointments', ListAppointmentsController::class)
    ->name('api.appointments.index')
    ->middleware('auth:sanctum');


Route::post('/appointments/week/duplicate', DuplicateWeekController::class)
    ->name('api.appointments.week.duplicate')
    ->middleware('auth:sanctum');;
