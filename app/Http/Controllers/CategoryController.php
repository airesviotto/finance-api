<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\HttpResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Category",
 *     description="Operations related to user categories"
 * )
 */
class CategoryController extends Controller
{
    protected $http;

    public function __construct(HttpResponseService $http)
    {
        $this->http = $http;
    }
    
    /**
     * @OA\Get(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     summary="List all categories",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="List of categories"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if(!$user->tokenCan('view_all_categories')) {
           return $this->http->forbidden('Access denied');
        }
         // List all categories
        $categories = Category::all();

         return $this->http->ok($categories);
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     summary="Create a new category",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Entertainment")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Category created"),
     *     @OA\Response(response=403, description="Access denied")
     * )
     */
    public function store(Request $request)
    {   
        /** @var \App\Models\User $user */
        $user = Auth::user();
         if(!$user->tokenCan('create_category')) {
            return $this->http->forbidden('Access denied');
        }

        $request->validate([
            'name' => ['required','string','max:255','unique:categories,name'],
            'type' => ['required','in:income,expense'],
        ]);

        $category = Category::create($request->all());

        return $this->http->ok($category, 'Category created successfully');
        
    }

    /**
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     summary="Retrieve a single category",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Category details"),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function show($id)
    {
         /** @var \App\Models\User $user */
        $user = Auth::user();

        if(!$user->tokenCan('view_category')) {
            return $this->http->forbidden('Access denied');
        }

        $category = Category::find($id);

        if(!$category) {
             return $this->http->notFound('Transaction not found or access denied');
        }
        return $this->http->ok($category);
    }

    /**
     * @OA\Put(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     summary="Update a category",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Category Name")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Category updated"),
     *     @OA\Response(response=403, description="Access denied"),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function update(Request $request, $id)
    {
         /** @var \App\Models\User $user */
        $user = Auth::user();
        if(!$user->tokenCan('update_category')) {
           return $this->http->forbidden('Access denied');
        }

        $category = Category::find($id);

          if(!$category) {
            return $this->http->notFound('Transaction not found or access denied');
        }

        $request->validate([
            'name' => ['sometimes','string','max:255','unique:categories,name,' . $category->id],
            'type' => ['sometimes','in:income,expense'],
        ]);

        $category->update($request->all());

        return $this->http->ok($category, 'Category updated successfully');
    }

   /**
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     summary="Delete a category",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Category deleted"),
     *     @OA\Response(response=403, description="Access denied"),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function destroy($id)
    {
         /** @var \App\Models\User $user */
        $user = Auth::user();
        if(!$user->tokenCan('delete_category')) {
            return $this->http->forbidden('Access denied');
        }

        $category = Category::find($id);

        if(!$category) {
             return $this->http->notFound('Transaction not found or access denied');
        }

        $category->delete(); // soft delete

        return $this->http->ok('Category deleted successfully');
    }
}
