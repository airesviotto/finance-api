<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\HttpResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
      protected $http;

    public function __construct(HttpResponseService $http)
    {
        $this->http = $http;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(!Auth::user()->tokenCan('view_all_categories')) {
           return $this->http->forbidden('Access denied');
        }
         // List all categories
        $categories = Category::all();
        
         return $this->http->ok($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         if(!Auth::user()->tokenCan('create_category')) {
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
     * Display the specified resource.
     */
    public function show($id)
    {

        if(!Auth::user()->tokenCan('view_category')) {
            return $this->http->forbidden('Access denied');
        }

        $category = Category::find($id);

        if(!$category) {
             return $this->http->notFound('Transaction not found or access denied');
        }
        return $this->http->ok($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        
        if(!Auth::user()->tokenCan('update_category')) {
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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if(!Auth::user()->tokenCan('delete_category')) {
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
