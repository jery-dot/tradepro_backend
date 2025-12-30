<?php

namespace App\Http\Controllers\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Specialization;
use Illuminate\Http\Request;

class SpecializationController extends Controller
{
    /**
     * GET /api/specializations
     *
     * Headers:
     *  - Authorization: Bearer <ACCESS_TOKEN>
     *  - Content-Type: application/json
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = auth('api')->user();

        if(!$user){
            return ApiResponse::warning('Unauthorized', 401);
        }
        
        // Select only fields needed for dropdown
        $specializations = Specialization::select('id', 'name')
            ->orderBy('id')
            ->get(); // Simple Eloquent query to fetch all rows.[web:29][web:368]

        return ApiResponse::success(
            'Specializations load successfully',
            ['specializations' => $specializations]
        );
    }
}
