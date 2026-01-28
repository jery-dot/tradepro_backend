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
use App\Http\Controllers\V1\NotificationController;
use App\Http\Controllers\V1\OpportunityController;
use App\Http\Controllers\V1\ReviewController;
use App\Http\Controllers\V1\SpecializationController;
use App\Http\Controllers\V1\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;

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
        Route::get('/listings', 'index');
        Route::get('/my_listings', 'myListings');
        Route::get('/listing_details', 'show');
    });

    // Listing Meta
    Route::controller(ListingMetaController::class)->group(function () {
        Route::get('/category_list', 'categoryList');
        Route::get('/condition_list', 'conditionList');
    });

    // Opportunity APIs
    Route::controller(OpportunityController::class)->group(function () {
        Route::post('/post_opportunity', 'postOpportunity');
        Route::get('/get_opportunities', 'getOpportunities');
        Route::get('/my_opportunities', 'myOpportunities');
        Route::post('/edit_opportunity', 'editOpportunity');
        Route::post('/delete_opportunity', 'deleteOpportunity');
    });

    // Apprentice Profile
    Route::controller(ApprenticeProfileController::class)->group(function () {

        Route::middleware('worker')->group(function () {
            Route::post('/create_apprentice_profile', 'createApprenticeProfile');
            Route::post('/edit_apprentice_profile', 'editApprenticeProfile');
            Route::get('/get_apprentice_profile', 'getApprenticeProfile');
            Route::post('/delete_apprentice_profile', 'deleteApprenticeProfile');
        });

        Route::middleware('hirer')->group(function () {
            Route::get('/get_all_apprentice_profiles', 'getAllApprenticeProfiles');
        });
    });

    // Notification APIs
    Route::middleware('auth:api')->group(function () {
        Route::get('/get_notifications', [NotificationController::class, 'getNotifications']);
        Route::get('/get_notification_count', [NotificationController::class, 'getNotificationCount']);
    });

    // update user infos.
    Route::get('/get_labor_profile', [LaborerController::class, 'getLaborProfile']);
    Route::post('/update_profile_image', [UserController::class, 'updateProfileImage']);
    Route::post('/update_user_insurance_status', [LaborerController::class, 'updateInsuranceStatus']);
    Route::post('/update_user_background_check_status', [LaborerController::class, 'updateBackgroundCheckStatus']);
    Route::post('/update_user_apprenticeship_status', [LaborerController::class, 'updateApprenticeshipStatus']);
    Route::post('/update_job_requirement', [UserController::class, 'updateJobRequirement']);
    Route::post('/update_profile_document', [UserController::class, 'updateProfileDocument']);

    Route::get('/get_contractor_profile', [UserController::class, 'getContractorProfile']);
    Route::get('/get_subcontractor_profile', [UserController::class, 'getSubcontractorProfile']);

    Route::get('/get_apprentice_profile', [UserController::class, 'getApprenticeProfile']);

    Route::post('/update_experience_level', [ApprenticeController::class, 'updateExperienceLevel']);

    Route::get('/get_user_reviews', [ReviewController::class, 'getUserReviews']);

     Route::post('/delete_account', [UserController::class, 'deleteAccount']);

     Route::post('/send_notification', [NotificationController::class, 'sendNotification']);

     // User settings
      Route::get('/get_user_settings', [UserController::class, 'getUserSettings']);

      // Update notification status
      Route::post('/update_notification_status', [UserController::class, 'updateNotificationStatus']);

      // Update user location
      Route::post('/update_location', [UserController::class, 'updateLocation']);

      // update user FCM token
      Route::post('/update_fcm_token', [UserController::class, 'updateFcmToken']);
});


Route::post('/contact/send', [ContactController::class, 'send']);
