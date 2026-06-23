<?php

namespace App\Http\Controllers\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\JobAcceptance;
use App\Models\JobPost;
use App\Models\Laborer;
use App\Models\User;
use Illuminate\Http\Request;
use App\Enums\UserType;

class JobAcceptanceController extends Controller
{
    public function acceptJob(Request $request)
    {
        $acceptor = auth('api')->user();

        if(!$acceptor){
            return ApiResponse::warning('Unauthorized', 403);
        }

        $request->validate([
            'job_id' => 'required|exists:job_posts,id',
            'labor_id' => 'required|exists:users,id',
        ]);

        // Check if the job exist or not
        $job = JobPost::findOrFail($request->job_id);
        if(!$job){
            return ApiResponse::warning('Job not found!', 404);
        }

        // check if the user exist and that the type is laborer
        $laborer = Laborer::where('user_id', $request->labor_id)->first();
        if(!$laborer){
            return ApiResponse::warning('Laborer not found!', 404);
        }

        // Check already accepted
        if (JobAcceptance::where('job_post_id', $job->id)->where('labor_id', $request->labor_id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This Laborer already accepted for this job'
            ], 400);
        }

        $acceptorId = $job->user_id;

        $owner = User::find($acceptorId);
        // Check for match between owner and current login acceptor
        if($acceptor->id != $owner->id){
            return ApiResponse::warning('Unauthorized', 403);
        }
        // return response()->json([
        //     $owner->user_type->value,
        //     UserType::CONTRACTOR
        // ]);

        // Detect who owns this job
        if ($owner->user_type == UserType::CONTRACTOR) {
            $acceptorType = 'contractor';
        } elseif ($owner->user_type == UserType::SUBCONTRACTOR) {
            $acceptorType = 'subcontractor';
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No contractor assigned to this job'
            ], 400);
        }

        JobAcceptance::create([
            'job_post_id'       => $job->id,
            'labor_id'     => $request->labor_id,
            'acceptor_id'  => $acceptorId,
            'acceptor_type'=> $acceptorType,
            'status'       => 'accepted',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'contractor accepted for job'
        ]);
    }

    //
    public function getJobLabors(Request $request)
    {
        $user = auth('api')->user();

        if (!$user) {
            return ApiResponse::warning('Unauthorized', 403);
        }

        $request->validate([
            'job_id' => 'required|exists:job_posts,id',
        ]);

        $job = JobPost::find($request->job_id);

        // Ensure job belongs to logged-in contractor/subcontractor
        if ($job->user_id != $user->id) {
            return ApiResponse::warning('Unauthorized', 403);
        }

        // Get accepted labor ids
        $acceptedLaborIds = JobAcceptance::where('job_post_id', $job->id)
                            ->pluck('labor_id')
                            ->toArray();

        // Get applied labor ids (if you have job_applications table)
        // $appliedLaborIds = JobApplication::where('job_post_id', $job->id)
        //                     ->pluck('labor_id')
        //                     ->toArray();

        $allLaborIds = array_unique($acceptedLaborIds);//array_unique(array_merge($acceptedLaborIds, $appliedLaborIds));

        $labors = User::whereIn('id', $allLaborIds)
            ->with('laborer') // relation to laborer table
            ->get()
            ->map(function ($user) use ($acceptedLaborIds) {

                return [
                    'id' => $user->id,
                    'labor_id' => $user->laborer?->id,
                    'full_name' => $user->name,
                    'role' => 'Labor',
                    'status' => in_array($user->id, $acceptedLaborIds) ? 'Working' : 'Applied',
                    'rating' => $user->rating ?? 0,
                    'profile_image_url' => $user->profile_image
                            ? asset('profiles/'.$user->profile_image)
                            : null,
                    'location' => $user->city . ', ' . $user->state . ', ' . $user->country,
                    'email' => $user->email,
                    'insurance' => $user->laborer->has_insurance ?? false,
                    'background_check_completed' => $user->laborer->background_check_completed ?? false,
                    'looking_for_apprenticeship' => $user->laborer->looking_for_apprenticeship ?? false,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $labors
        ]);
    }
}
