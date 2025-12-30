<?php

namespace App\Http\Controllers\V1\Auth;

use App\Enums\UserType;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * POST /api/auth/register
     */
    public function register(Request $request)
    {
        // -------------------------------------------------------------
        // 1. Validate request, including nested location fields
        // -------------------------------------------------------------
        try {
            // Wrap validation in try/catch so we can customize error output
            $validated = $request->validate([
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'user_type' => ['required', Rule::in(UserType::values())],
                // 'user_type' => 'required|in:0,1,2,3',
                'location' => 'nullable|array',
                'location.latitude' => 'nullable|numeric|between:-90,90',
                'location.longitude' => 'nullable|numeric|between:-180,180',
            ]);
        } catch (ValidationException $e) {

            // Check if the specific error is "email already taken"
            if ($e->errors()['email'] ?? false) {
                return ApiResponse::warning('Email already exists', 422);
            }

            // For other validation errors you can either:
            // 1) return a generic message, or
            // 2) forward the original messages.
            return ApiResponse::error('Validation error', 422, $e->errors());
        }

        // -------------------------------------------------------------
        // 2. Prepare user payload for response
        // -------------------------------------------------------------
        $location = $request->input('location', []);

        // -------------------------------------------------------------
        // 3. Create user with hashed password
        // -------------------------------------------------------------
        $user = User::create([
            'name' => $request->name ?? '',
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => UserType::from($validated['user_type'])->value,
            // 'user_type' => $validated['user_type'],
            'latitude' => $location['latitude'],
            'longitude' => $location['longitude'],
            // 'name' can be filled later by profile update endpoint
        ]);

        // -------------------------------------------------------------
        // 4. Generate JWT token for the new user
        // -------------------------------------------------------------
        $token = JWTAuth::fromUser($user); // Typical jwt-auth workflow for issuing tokens.

        $userPayload = [
            'id' => (string) $user->id,
            'name' => $user->name ?? '',
            'user_type' => (int) $user->user_type,
            'email' => $user->email,
            'location' => [
                'latitude' => $location['latitude'] ?? null,
                'longitude' => $location['longitude'] ?? null,
            ],
        ];

        // -------------------------------------------------------------
        // 5. Return API response
        // -------------------------------------------------------------
        return ApiResponse::success('Account created successfully', [
            'token' => $token,
            'user' => $userPayload,
        ], 201);
    }

    /**
     * POST /api/auth/login
     */
    public function login(Request $request)
    {
        // 1. Validate request
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'location' => 'nullable|array',
            'location.latitude' => 'nullable|numeric|between:-90,90',
            'location.longitude' => 'nullable|numeric|between:-180,180',
            'available_today' => 'nullable|boolean',
        ]); // Nested validation via dot-notation for JSON objects is standard in Laravel.

        // 2. Attempt auth
        $credentials = $request->only('email', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return ApiResponse::error('Invalid email or password', 401);
        } // JWT guard's attempt method returns a token on success and false on failure.

        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        $location = $request->input('location', []);
        $availableToday = (bool) $request->input('available_today', false);

        // Update the user's latitude and longitude if provided
        if ($request->filled(['location'])) {
            $user->update([
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
            ]);
        }

        // Update the user's availability if provided
        if ($request->filled(['available_today'])) {
            $user->update([
                'available_today' => $request->input('available_today'),
            ]);
        }

        // 3. Eager-load profile based on user_type
        // 0 = contractor, 1 = subcontractor, 2 = laborer, 3 = apprentice
        switch ((int) $user->user_type->value) {
            case UserType::CONTRACTOR:
                $user->load('contractor');
                break;
            case UserType::SUBCONTRACTOR:
                $user->load('subcontractor');
                break;
            case UserType::LABORER:
                $user->load('laborer');
                break;
            case UserType::APPRENTICE:
                $user->load('apprentice');
                break;
        } // Conditional eager loading like this avoids N+1 and only loads the needed relation.

        // 4. Build base user payload
        $userPayload = [
            'id' => (string) $user->id,
            'name' => $user->name ?? '',
            'user_type' => (int) $user->user_type->value,
            'email' => $user->email,
            'location' => [
                'latitude' => $user->latitude ?? null,
                'longitude' => $user->longitude ?? null,
            ],
            'available_today' => $user->available_today,
        ];

        // 5. Merge type-specific profile details
        if ($user->user_type === 2 && $user->relationLoaded('laborer') && $user->laborer) {
            $laborer = $user->laborer;
            $userPayload['laborer'] = [
                'id' => $laborer->id,
                'specialization_id' => $laborer->specialization_id,
                'custom_specialization' => $laborer->custom_specialization,
                'experience_level' => $laborer->experience_level,
                'age' => $laborer->age,
                'gender' => $laborer->gender,
                'has_insurance' => (bool) $laborer->has_insurance,
                'background_check_completed' => (bool) $laborer->background_check_completed,
                'looking_for_apprenticeship' => (bool) $laborer->looking_for_apprenticeship,
                'trade_school' => [
                    'name' => $laborer->trade_school_name,
                    'program_year' => $laborer->trade_school_program_year,
                ],
                'profile_completion' => (bool) $laborer->profile_completion,
            ];
        }

        if ($user->user_type === 1 && $user->relationLoaded('subcontractor') && $user->subcontractor) {
            $sub = $user->subcontractor;
            $userPayload['subcontractor'] = [
                'id' => $sub->id,
                'location' => [
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude,
                ],
                'insurance_file_url' => $sub->insurance_file_path ?? null,
                'profile_completion' => (bool) $sub->profile_completion,
            ];
        }

        if ($user->user_type === 0 && $user->relationLoaded('contractor') && $user->contractor) {
            $contractor = $user->contractor->load('jobRequirements'); // load requirements with contractor.

            $userPayload['contractor'] = [
                'id' => $contractor->id,
                'location' => [
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude,
                ],
                'file_url' => $contractor->file_path ?? null,
                'job_requirements' => $contractor->jobRequirements
                    ->pluck('slug')
                    ->values()
                    ->all(),
                'profile_completion' => (bool) $contractor->profile_completion,
            ];
        }

        if ($user->user_type === 3 && $user->relationLoaded('apprentice') && $user->apprentice) {
            $apprentice = $user->apprentice->load('tradeInterest');

            $userPayload['apprentice'] = [
                'id' => $apprentice->id,
                'trade_interest' => $apprentice->tradeInterest ? [
                    'id' => $apprentice->tradeInterest->id,
                    'name' => $apprentice->tradeInterest->name,
                ] : null,
                'trade_school' => [
                    'name' => $apprentice->trade_school_name,
                    'current_program_year' => $apprentice->current_program_year,
                ],
                'experience_level' => $apprentice->experience_level,
                'profile_completion' => (bool) $apprentice->profile_completion,
            ];
        }

        // 6. Return response
        return ApiResponse::success('Login successful', [
            'token' => $token,
            'user' => $userPayload,
        ]);
    }

    /**
     * POST /api/auth/forgot-password
     */
    public function forgotPassword(Request $request)
    {
        try {
            // 1. Validate email exists
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
            ]); // `exists:users,email` ensures only registered emails get an OTP.

        } catch (ValidationException $e) {

            // Check if the specific error is "email already taken"
            if ($e->errors()['email'] ?? false) {
                return ApiResponse::warning('The selected email is invalid.', 422);
            }

            // For other validation errors you can either:
            // 1) return a generic message, or
            // 2) forward the original messages.
            return ApiResponse::error('Validation error', 422, $e->errors());
        }

        $email = $validated['email'];

        // 2. Generate 4-digit OTP and expiry (30 seconds)
        $otp = random_int(1000, 9999); // cryptographically secure random.
        $expiresAt = Carbon::now()->addSeconds(30);

        // 3. Store or update OTP record for this email
        PasswordResetOtp::updateOrCreate(
            ['email' => $email],
            [
                'otp' => (string) $otp,
                'expires_at' => $expiresAt,
            ]
        );

        // 4. Send OTP via email (simple example)
        // Configure MAIL_* in .env first.
        Mail::raw("Your password reset OTP is: {$otp}", function ($message) use ($email) {
            $message->to($email)
                ->subject('Your password reset OTP');
        });

        // 5. Build response
        return ApiResponse::success('OTP sent successfully to your email', [
            'otp' => (int) $otp,       // you may remove this in production for security
            'otp_expiry' => 30,               // in seconds
        ]);
    }

    /**
     * POST /api/auth/resend-forgot-otp
     */
    public function resendForgotOtp(Request $request)
    {

        try {

            // 1. Validate that email exists in users table
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
            ]); // exists:users,email ensures only registered emails can request OTP again.

        } catch (ValidationException $e) {

            // Check if the specific error is "email already taken"
            if ($e->errors()['email'] ?? false) {
                return ApiResponse::warning('The selected email is invalid.', 422);
            }

            // For other validation errors you can either:
            // 1) return a generic message, or
            // 2) forward the original messages.
            return ApiResponse::error('Validation error', 422, $e->errors());
        }

        $email = $validated['email'];

        // 2. Generate new OTP and expiry (30 seconds)
        $otp = random_int(1000, 9999);
        $expiresAt = Carbon::now()->addSeconds(30); // set OTP expiry window.

        // 3. Update or create OTP entry for this email
        PasswordResetOtp::updateOrCreate(
            ['email' => $email],
            [
                'otp' => (string) $otp,
                'expires_at' => $expiresAt,
            ]
        );

        // 4. Resend OTP via email
        Mail::raw("Your password reset OTP is: {$otp}", function ($message) use ($email) {
            $message->to($email)
                ->subject('Your password reset OTP (resend)');
        }); // Using Mail::raw is a simple way to send OTP emails in Laravel.

        // 5. Return standardized success response
        return ApiResponse::success('OTP sent successfully to your email', [
            'otp' => (int) $otp, // include only because your spec shows it
            'otp_expiry' => 30,
        ]);
    }

    /**
     * POST /api/auth/verify-forgot-otp
     */
    public function verifyForgotOtp(Request $request)
    {
        // 1. Validate input
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string',
        ]);

        $email = $validated['email'];
        $otp = $validated['otp'];

        // 2. Find OTP record for this email + otp
        $otpRecord = PasswordResetOtp::where('email', $email)
            ->where('otp', $otp)
            ->first();

        if (! $otpRecord) {
            return ApiResponse::warning('Invalid OTP', 400);
        }

        // 3. Check OTP expiry (still only valid for 30 sec)
        if (Carbon::now()->greaterThan($otpRecord->expires_at)) {
            return ApiResponse::warning('OTP expired', 400);
        }

        // 4. Generate a new reset_token based on email
        //    - must be unique per call
        //    - valid for 30 seconds
        $resetToken = hash_hmac(
            'sha256',
            $email.'|'.Str::random(40).'|'.microtime(true), // ensures a different token every time
            config('app.key')
        );
        $resetTokenExpiresAt = Carbon::now()->addSeconds(30);

        // 5. Store reset_token and its expiry on the same row
        $otpRecord->update([
            // optional: keep or clear OTP; keeping it doesn’t hurt since token is what matters now
            'reset_token' => $resetToken,
            'reset_token_expires_at' => $resetTokenExpiresAt,
        ]);

        // 6. Return response with reset_token
        return ApiResponse::success('OTP verified successfully', [
            'reset_token' => $resetToken,
        ]);
    }


    /**
     * POST /api/auth/reset-password
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'reset_token' => 'required|string',
            'new_password' => 'required|min:8',
        ]);

        $resetToken = $validated['reset_token'];

        // Find OTP record by reset token
        $otpRecord = PasswordResetOtp::where('reset_token', $resetToken)->first();

        if (! $otpRecord) {
            // Invalid or already used token
            return ApiResponse::warning('Reset token expired', 400);
        }

        // Check reset token expiry
        if (Carbon::now()->greaterThan($otpRecord->reset_token_expires_at)) {
            // Optionally delete or clear token
            $otpRecord->update([
                'reset_token' => null,
                'reset_token_expires_at' => null,
            ]);

            return ApiResponse::error('Reset token expired', 400);
        }

        // Find user by email from OTP record
        $user = User::where('email', $otpRecord->email)->first();

        if (! $user) {
            // Should not normally happen; treat as expired/inactive token
            $otpRecord->update([
                'reset_token' => null,
                'reset_token_expires_at' => null,
            ]);

            return ApiResponse::error('Reset token expired', 400);
        }

        // Update password
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        // Invalidate token after use
        $otpRecord->update([
            'reset_token' => null,
            'reset_token_expires_at' => null,
        ]);

        return ApiResponse::success('Password reset successfully');
    }
}
