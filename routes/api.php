<?php

use App\Http\Controllers\V1\Auth\AuthController;
use App\Http\Controllers\V1\Auth\LaborerController;
use App\Http\Controllers\V1\Auth\SubcontractorController;
use App\Http\Controllers\V1\Auth\ContractorController;
use App\Http\Controllers\V1\Auth\ApprenticeController;
use App\Http\Controllers\V1\JobPostController;
use App\Http\Controllers\V1\SpecializationController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('register',          'register');
    Route::post('login',             'login');
    Route::post('forgot-password',   'forgotPassword');
    Route::post('resend-forgot-otp', 'resendForgotOtp');
    Route::post('verify-forgot-otp', 'verifyForgotOtp');
    Route::post('reset-password',    'resetPassword');
});

// Protected routes (JWT)
Route::middleware('auth:api')->group(function () {

    // Laborer
    Route::post('/laborer_update_details', [LaborerController::class, 'updateDetails']);
    Route::get('/laborer/specializations', [LaborerController::class, 'listSpecializations']);

    // Subcontractor
    Route::post('/subcontractor_update_details', [SubcontractorController::class, 'updateDetails']);

    // Contractor
    Route::post('/contractor_update_details', [ContractorController::class, 'updateDetails']);
    Route::get('/contractor/job-requirements', [ContractorController::class, 'jobRequirements']);

    // Apprentice
    Route::post('/apprentice_update_details', [ApprenticeController::class, 'updateDetails']);
    Route::get('/apprentice/trade-interests', [ApprenticeController::class, 'tradeInterests']);
});

use App\Http\Controllers\V1\SkillController;

Route::middleware('auth:api')->group(function () {
    // Skill
    Route::get('/skills', [SkillController::class, 'getSkills']);

    // JobPost
    Route::controller(JobPostController::class)->group(function () {
        Route::post('/create_job', 'createJob');
        Route::get('/jobs', 'listJobs');
        Route::post('/edit_job', 'editJob');
        Route::post('/delete_job', 'deleteJob');
        Route::post('/jobs/update-status', 'updateJobStatus');
    });

    // Specialization
     Route::get('/specializations', [SpecializationController::class, 'index']);
});
