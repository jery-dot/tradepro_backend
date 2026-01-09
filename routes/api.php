<?php

use App\Http\Controllers\V1\ApprenticeProfileController;
use App\Http\Controllers\V1\Auth\ApprenticeController;
use App\Http\Controllers\V1\Auth\AuthController;
use App\Http\Controllers\V1\Auth\ContractorController;
use App\Http\Controllers\V1\Auth\LaborerController;
use App\Http\Controllers\V1\Auth\SubcontractorController;
use App\Http\Controllers\V1\JobPostController;
use App\Http\Controllers\V1\ListingController;
use App\Http\Controllers\V1\ListingMetaController;
use App\Http\Controllers\V1\OpportunityController;
use App\Http\Controllers\V1\ReviewController;
use App\Http\Controllers\V1\SpecializationController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('forgot-password', 'forgotPassword');
    Route::post('resend-forgot-otp', 'resendForgotOtp');
    Route::post('verify-forgot-otp', 'verifyForgotOtp');
    Route::post('reset-password', 'resetPassword');
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
        Route::post('/jobs/search', 'searchJobs');
    });

    // Specialization
    Route::get('/specializations', [SpecializationController::class, 'index']);

    //  Review
    Route::controller(ReviewController::class)->group(function () {
        Route::post('/reviews_submit', 'submitReview');
        Route::get('/reviews', 'listReviews');
    });

    // Listing API
    Route::controller(ListingController::class)->group(function () {
        Route::post('/add_listings', 'createListing');
        Route::post('/edit_listings', 'editListing');
        Route::post('/delete_listing', 'deleteListing');
        Route::get('/listings',         'index');
        Route::get('/my_listings',      'myListings');
        Route::get('/listing_details',  'show');
    });

    // Listing Meta
    Route::controller(ListingMetaController::class)->group(function () {
        Route::get('/category_list', 'categoryList');
        Route::get('/condition_list', 'conditionList');
    });

    // Opportunity APIs
    Route::controller(OpportunityController::class)->group(function(){
        Route::post('/post_opportunity',   'postOpportunity');
        Route::get('/get_opportunities',   'getOpportunities');
        Route::post('/edit_opportunity',   'editOpportunity');
        Route::post('/delete_opportunity', 'deleteOpportunity');
    });

    // Apprentice Profile
    Route::controller(ApprenticeProfileController::class)->group(function(){
        
        Route::middleware('worker')->group(function(){
            Route::post('/create_apprentice_profile',  'createApprenticeProfile');
            Route::post('/edit_apprentice_profile',    'editApprenticeProfile');
            Route::get('/get_apprentice_profile',      'getApprenticeProfile');
            Route::post('/delete_apprentice_profile',  'deleteApprenticeProfile');
        });

        Route::middleware('hirer')->group(function(){
            Route::get('/get_all_apprentice_profiles',  'getAllApprenticeProfiles');
        });
    });
 
});
