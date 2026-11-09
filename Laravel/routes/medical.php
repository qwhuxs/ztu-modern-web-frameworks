<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\JwtAuthenticate;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::middleware(JwtAuthenticate::class)->group(function () {
    Route::view('/medical', 'medical.home')->name('medical.home');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/api/login', [AuthController::class, 'apiLogin'])
        ->withoutMiddleware(ValidateCsrfToken::class)
        ->name('api.login');
    Route::get('/api/me', [AuthController::class, 'me'])
        ->middleware(JwtAuthenticate::class.':client')
        ->name('api.me');

    $resources = [
        'patients' => PatientController::class,
        'doctors' => DoctorController::class,
        'appointments' => AppointmentController::class,
        'diagnoses' => DiagnosisController::class,
        'treatments' => TreatmentController::class,
    ];

    $access = [
        'client' => ['index'],                               
        'manager' => ['create', 'store', 'edit', 'update'],  
        'admin' => ['destroy'],                              
    ];

    foreach ($access as $role => $actions) {
        Route::middleware(JwtAuthenticate::class.':'.$role)->group(function () use ($resources, $actions) {
            foreach ($resources as $name => $controller) {
                Route::resource($name, $controller)
                    ->parameters(['diagnoses' => 'diagnosis'])
                    ->only($actions);
            }
        });
    }

    Route::middleware(JwtAuthenticate::class.':admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    });
});