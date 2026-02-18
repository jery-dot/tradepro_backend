<?php

namespace App\Http\Controllers\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Condition;

class ListingMetaController extends Controller
{
    /**
     * GET /api/category_list
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoryList()
    {
        $categories = Category::orderBy('is_popular', 'desc')
            ->orderBy('name')
            ->get(['id', 'name', 'image', 'is_popular']); // typical dropdown query.

        $categories = $categories->map(function (Category $cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'image' => $cat->image,
                'is_popular' => (bool) $cat->is_popular,
            ];
        })->all();

        // return ApiResponse::success(
        //     'Category load successfully',
        //     $categories, 200);

        return response()->json([
            'status'   => true,
            'message'  => 'Category load successfully',
            'categories' => $categories
        ]);
    }
    
    /**
     * GET /api/condition_list
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function conditionList()
    {
        $conditions = Condition::orderBy('id')->get(['id', 'name']); // simple lookup table for dropdowns.

        // return ApiResponse::success(
        //     'Condition list loaded successfully',
        //     $conditions->map(fn ($c) => [
        //         'id' => $c->id,
        //         'name' => $c->name,
        //     ])->all(), 200);
        return response()->json([
            'status'   => true,
            'message'  => 'Condition list loaded successfully',
            'conditions' => $conditions->map(fn ($c) => [
                'id'   => $c->id,
                'name' => $c->name,
            ])->all(),
        ]);
    }
}
