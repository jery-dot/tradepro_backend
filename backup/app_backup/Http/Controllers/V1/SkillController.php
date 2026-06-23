<?php

namespace App\Http\Controllers\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{

    /**
     * GET /api/skills
     * Query Parameters (Optional):
     * - ?limit=20
     * - ?page=1

     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSkills(Request $request)
    {
        // Read limit from query, default 20, with a sane max cap
        $limit = (int) $request->query('limit', 20);
        $limit = $limit > 0 ? min($limit, 100) : 20;

        // Laravel uses ?page= automatically for pagination.[web:300][web:296]
        $paginator = Skill::orderBy('id')->paginate($limit);

        // Map data to required shape
        $data = $paginator->getCollection()->map(function (Skill $skill) {
            return [
                'id'   => $skill->code, // "skill_001"
                'name' => $skill->name,
            ];
        })->values()->all();

        $meta = [
            'page'  => $paginator->currentPage(),
            'limit' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];

        // Note: this API uses "success" instead of "status" per your spec
        return ApiResponse::success(
            'Skills loaded successfully',
            ['data' => $data],
            200,
            ['meta' => $meta]
        );
    }
}
