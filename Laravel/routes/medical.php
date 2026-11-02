<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\TreatmentController;
use Illuminate\Support\Facades\Route;

Route::view('/medical', 'medical.home')->name('medical.home');
Route::resource('patients', PatientController::class)->except('show');
Route::resource('doctors', DoctorController::class)->except('show');
Route::resource('appointments', AppointmentController::class)->except('show');
Route::resource('diagnoses', DiagnosisController::class)
    ->parameters(['diagnoses' => 'diagnosis'])
    ->except('show');
Route::resource('treatments', TreatmentController::class)->except('show');