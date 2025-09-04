<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(!Auth::user()->tokenCan('view_all_transactions')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $user = Auth::user();

        //List all transactions of the authenticated user
        $transactions = $user->transactions()->with('category')->get();
        
        return response()->json($transactions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(!Auth::user()->tokenCan('create_transaction')) {
             return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'type' => ['required', 'in:income,expense'],
            'date' => ['required', 'date'],
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        $transaction = Auth::user()->transactions()->create($request->all());

        return response()->json([
            'message' => 'Transaction created successfully',
            'transaction' => $transaction
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {   
        
        if (!Auth::user()->tokenCan('view_transaction')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $transaction = Auth::user()->transactions()->with('category')->find($id);

        if (!$transaction) {
            return response()->json([
                'error' => 'Transaction not found or access denied'
            ], 404);
        }

        return response()->json($transaction->user_id);
        
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

         if(!Auth::user()->tokenCan('create_transaction')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $transaction = Auth::user()->transactions()->find($id);

        if (!$transaction) {
            return response()->json([
                'error' => 'Transaction not found or access denied'
            ], 404);
        }

        $request->validate([
            'description' => ['sometimes', 'string', 'max:255'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'type' => ['sometimes', 'in:income,expense'],
            'date' => ['sometimes', 'date'],
            'category_id' => ['sometimes', 'exists:categories,id'],
        ]);

        $transaction->update($request->all());

        return response()->json([
            'message' => 'Transaction updated successfully',
            'transaction' => $transaction
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if(!Auth::user()->tokenCan('delete_transaction')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $transaction = Auth::user()->transactions()->find($id);

        if (!$transaction) {
            return response()->json([
                'error' => 'Transaction not found or access denied'
            ], 404);
        }
        
        $transaction->delete();

        return response()->json([
            'message'=> 'Transaction deleted successfully'
        ]);
    }
}
