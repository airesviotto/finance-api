<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(!Auth::user()->tokenCan('view_all_categories')) {
            return response()->json([
                'Access denied'
            ],500);
        }
         // List all categories
        $categories = Category::all();
        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         if(!Auth::user()->tokenCan('create_category')) {
            return response()->json([
                'Access denied'
            ],500);
        }

        $request->validate([
            'name' => ['required','string','max:255','unique:categories,name'],
            'type' => ['required','in:income,expense'],
        ]);

        $category = Category::create($request->all());

        return response()->json([
            'message'  => 'Category created successfully',
            'category' => $category
        ], 201);
        
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        if(!Auth::user()->tokenCan('view_category')) {
            return response()->json([
                'Access denied'
            ],500);
        }

        $category = Category::find($id);

        if(!$category) {
              return response()->json([
                'error' => 'Transaction not found or access denied'
            ], 404);
        }
        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        
        if(!Auth::user()->tokenCan('update_category')) {
            return response()->json([
                'Access denied'
            ],500);
        }

        $category = Category::find($id);

          if(!$category) {
              return response()->json([
                'error' => 'Transaction not found or access denied'
            ], 404);
        }

        $request->validate([
            'name' => ['sometimes','string','max:255','unique:categories,name,' . $category->id],
            'type' => ['sometimes','in:income,expense'],
        ]);

        $category->update($request->all());

        return response()->json([
            'message'  => 'Category updated successfully',
            'category' => $category
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if(!Auth::user()->tokenCan('delete_category')) {
            return response()->json([
                'Access denied'
            ],500);
        }

        $category = Category::find($id);

        if(!$category) {
              return response()->json([
                'error' => 'Transaction not found or access denied'
            ], 404);
        }

        $category->delete(); // soft delete

        return response()->json([
            'message' => 'Category deleted successfully'
        ],200);
    }
}
