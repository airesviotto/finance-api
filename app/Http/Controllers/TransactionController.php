<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\HttpResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
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
        if(!Auth::user()->tokenCan('view_all_transactions')) {
            return $this->http->forbidden('Access denied');
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
            return $this->http->forbidden('Access denied');
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
            return $this->http->forbidden('Access denied');
        }

        $transaction = Auth::user()->transactions()->with('category')->find($id);

        if (!$transaction) {
            return $this->http->notFound('Transaction not found or access denied');
        }

        return $this->http->ok($transaction);
        
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

         if(!Auth::user()->tokenCan('create_transaction')) {
            return $this->http->forbidden('Access denied');
        }

        $transaction = Auth::user()->transactions()->find($id);

        if (!$transaction) {
             return $this->http->notFound('Transaction not found or access denied');
        }

        $request->validate([
            'description' => ['sometimes', 'string', 'max:255'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'type' => ['sometimes', 'in:income,expense'],
            'date' => ['sometimes', 'date'],
            'category_id' => ['sometimes', 'exists:categories,id'],
        ]);

        $transaction->update($request->all());

        return $this->http->ok($transaction, 'Transaction updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if(!Auth::user()->tokenCan('delete_transaction')) {
           return $this->http->forbidden('Access denied');
        }

        $transaction = Auth::user()->transactions()->find($id);

        if (!$transaction) {
            return $this->http->notFound('Transaction not found or access denied');
        }

        $transaction->delete();

        return $this->http->ok('Transaction deleted successfully');
    }
}
