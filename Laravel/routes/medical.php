<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\TreatmentController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->withoutMiddleware(ValidateCsrfToken::class)->group(function () {
    Route::get('/patients', [PatientController::class, 'getPatients']);
    Route::get('/patients/{id}', [PatientController::class, 'getPatientById']);
    Route::post('/patients', [PatientController::class, 'createPatient']);
    Route::patch('/patients/{id}', [PatientController::class, 'updatePatientById']);
    Route::delete('/patients/{id}', [PatientController::class, 'deletePatientById']);

    Route::get('/doctors', [DoctorController::class, 'getDoctors']);
    Route::get('/doctors/{id}', [DoctorController::class, 'getDoctorById']);
    Route::post('/doctors', [DoctorController::class, 'createDoctor']);
    Route::patch('/doctors/{id}', [DoctorController::class, 'updateDoctorById']);
    Route::delete('/doctors/{id}', [DoctorController::class, 'deleteDoctorById']);

    Route::get('/appointments', [AppointmentController::class, 'getAppointments']);
    Route::get('/appointments/{id}', [AppointmentController::class, 'getAppointmentById']);
    Route::post('/appointments', [AppointmentController::class, 'createAppointment']);
    Route::patch('/appointments/{id}', [AppointmentController::class, 'updateAppointmentById']);
    Route::delete('/appointments/{id}', [AppointmentController::class, 'deleteAppointmentById']);

    Route::get('/diagnoses', [DiagnosisController::class, 'getDiagnoses']);
    Route::get('/diagnoses/{id}', [DiagnosisController::class, 'getDiagnosisById']);
    Route::post('/diagnoses', [DiagnosisController::class, 'createDiagnosis']);
    Route::patch('/diagnoses/{id}', [DiagnosisController::class, 'updateDiagnosisById']);
    Route::delete('/diagnoses/{id}', [DiagnosisController::class, 'deleteDiagnosisById']);

    Route::get('/treatments', [TreatmentController::class, 'getTreatments']);
    Route::get('/treatments/{id}', [TreatmentController::class, 'getTreatmentById']);
    Route::post('/treatments', [TreatmentController::class, 'createTreatment']);
    Route::patch('/treatments/{id}', [TreatmentController::class, 'updateTreatmentById']);
    Route::delete('/treatments/{id}', [TreatmentController::class, 'deleteTreatmentById']);
});