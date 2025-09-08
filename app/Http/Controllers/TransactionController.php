<?php

namespace App\Http\Controllers;

use App\Notifications\TransactionAlert;
use App\Services\HttpResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\TransactionsExport;
use App\Jobs\SendTransactionReport;
use App\Services\ExchangeRateService;
use Maatwebsite\Excel\Facades\Excel;

/**
 * @OA\Tag(
 *     name="Transactions",
 *     description="Operations related to user financial transactions"
 * )
 */
class TransactionController extends Controller
{
    protected $http;

    public function __construct(HttpResponseService $http)
    {
        $this->http = $http;
    }

   
    /**
     * @OA\Get(
     *     path="/api/transactions",
     *     tags={"Transactions"},
     *     summary="List transactions",
     *     description="Retrieve a paginated list of the authenticated user's transactions.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of results per page",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of transactions",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="description", type="string", example="Grocery shopping"),
     *                     @OA\Property(property="amount", type="number", format="float", example=125.50),
     *                     @OA\Property(property="currency", type="string", example="GBP"),
     *                     @OA\Property(property="type", type="string", enum={"income","expense"}, example="expense"),
     *                     @OA\Property(property="date", type="string", format="date", example="2025-09-08"),
     *                     @OA\Property(property="category_id", type="integer", example=2)
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Transaction list")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Access denied"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
       
         /** @var \App\Models\User $user */
        $user = Auth::user();
        if(!$user->tokenCan('view_all_transactions')) {
            return $this->http->forbidden('Access denied');
        }

        //filter
        $filters = $request->only([
            'category_ids', 'type', 'date_from', 'date_to', 
            'amount_min', 'amount_max', 'sort_by', 'order', 
            'page', 'per_page'
        ]);

        $perPage = $filters['per_page'] ?? 10; // 10 results per page

        //List all transactions of the authenticated user
        $transactions = $user->transactions()
                            ->with('category')
                            ->filter($filters)
                            ->paginate($perPage);
        
        return $this->http->ok($transactions, 'Transaction list');
    }

    /**
     * Store a newly created resource in storage.
     */
     /**
         * @OA\Post(
         *     path="/api/transactions",
         *     summary="Create a new transaction",
         *     tags={"Transactions"},
         *     security={{"sanctum":{}}},
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"description","amount","type","date","category_id"},
         *             @OA\Property(property="description", type="string", example="Grocery shopping"),
         *             @OA\Property(property="amount", type="number", format="float", example=120.50),
         *             @OA\Property(property="currency", type="string", example="USD"),
         *             @OA\Property(property="type", type="string", enum={"income","expense"}, example="expense"),
         *             @OA\Property(property="date", type="string", format="date", example="2025-09-08"),
         *             @OA\Property(property="category_id", type="integer", example=1)
         *         )
         *     ),
         *     @OA\Response(response=201, description="Transaction created successfully"),
         *     @OA\Response(response=400, description="Validation error"),
         *     @OA\Response(response=403, description="Forbidden")
         * )
         */
    public function store(Request $request, ExchangeRateService $exchange)
    {

         /** @var \App\Models\User $user */
        $user = Auth::user();
        if(!$user->tokenCan('create_transaction')) {
            return $this->http->forbidden('Access denied');
        }

        $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'], // USD, EUR, etc.
            'type' => ['required', 'in:income,expense'],
            'date' => ['required', 'date'],
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        $amount = $request->input('amount');
        $currency = $request->input('currency', 'GBP');
        $convertedAmount = $amount;

        if(empty($currency)) {
            $currency = 'GBP';
        }

        if ($currency !== 'GBP') {
            $converted = $exchange->convert($amount, $currency, 'GBP');
            $convertedAmount = $converted['amount'];
        }

        $transaction = $request->user()->transactions()->create([
            'description' => $request->input('description'),
            'amount' => $convertedAmount, // sempre em GBP
            'original_amount' => $amount,
            'currency' => $currency,
            'type' => $request->input('type'),
            'date' => $request->input('date'),
            'category_id' => $request->input('category_id'),
        ]);

        // Dispara a notificação
        $user->notify(new TransactionAlert($transaction, 'created'));

        return response()->json([
            'message' => 'Transaction created successfully',
            'transaction' => $transaction
        ],201);
    }

    /**
     * Display the specified resource.
     */
     /**
         * @OA\Get(
         *     path="/api/transactions/{id}",
         *     summary="Get a transaction by ID",
         *     tags={"Transactions"},
         *     security={{"sanctum":{}}},
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="Transaction ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(response=200, description="Transaction retrieved successfully"),
         *     @OA\Response(response=404, description="Transaction not found"),
         *     @OA\Response(response=403, description="Forbidden")
         * )
         */
    public function show($id)
    {   

         /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->tokenCan('view_transaction')) {
            return $this->http->forbidden('Access denied');
        }

        $transaction = $user->transactions()->with('category')->find($id);

        if (!$transaction) {
            return $this->http->notFound('Transaction not found or access denied');
        }

        return $this->http->ok($transaction);
        
    }


    /**
     * Update the specified resource in storage.
     */
    /**
         * @OA\Put(
         *     path="/api/transactions/{id}",
         *     summary="Update a transaction",
         *     tags={"Transactions"},
         *     security={{"sanctum":{}}},
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="Transaction ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\RequestBody(
         *         required=false,
         *         @OA\JsonContent(
         *             @OA\Property(property="description", type="string", example="Updated description"),
         *             @OA\Property(property="amount", type="number", format="float", example=200.00),
         *             @OA\Property(property="currency", type="string", example="EUR"),
         *             @OA\Property(property="type", type="string", enum={"income","expense"}, example="income"),
         *             @OA\Property(property="date", type="string", format="date", example="2025-09-10"),
         *             @OA\Property(property="category_id", type="integer", example=2)
         *         )
         *     ),
         *     @OA\Response(response=200, description="Transaction updated successfully"),
         *     @OA\Response(response=404, description="Transaction not found"),
         *     @OA\Response(response=403, description="Forbidden")
         * )
         */
   public function update(Request $request, $id, ExchangeRateService $exchange)
    {
        
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->tokenCan('create_transaction')) {
            return $this->http->forbidden('Access denied');
        }

        $transaction = $user->transactions()->find($id);

        if (!$transaction) {
            return $this->http->notFound('Transaction not found or access denied');
        }

        $validated = $request->validate([
            'description' => ['sometimes', 'string', 'max:255'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'], // USD, EUR, etc.
            'type' => ['sometimes', 'in:income,expense'],
            'date' => ['sometimes', 'date'],
            'category_id' => ['sometimes', 'exists:categories,id'],
        ]);

        // Se amount foi enviado, recalcula
        if ($request->has('amount')) {
            $amount = $validated['amount'];
            $currency = $request->input('currency', $transaction->currency ?? 'GBP');
            $convertedAmount = $amount;

            if ($currency !== 'GBP') {
                $converted = $exchange->convert($amount, $currency, 'GBP');
                $convertedAmount = $converted['amount'];
            }

            $transaction->amount = $convertedAmount;
            $transaction->original_amount = $amount;
            $transaction->currency = $currency;
        }

        // Update only request sent
        if ($request->has('description')) {
            $transaction->description = $validated['description'];
        }

        if ($request->has('type')) {
            $transaction->type = $validated['type'];
        }

        if ($request->has('date')) {
            $transaction->date = $validated['date'];
        }

        if ($request->has('category_id')) {
            $transaction->category_id = $validated['category_id'];
        }

        $transaction->save();

        $user->notify(new TransactionAlert($transaction, 'updated'));

        return $this->http->ok($transaction, 'Transaction updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    
        /**
         * @OA\Delete(
         *     path="/api/transactions/{id}",
         *     summary="Delete a transaction",
         *     tags={"Transactions"},
         *     security={{"sanctum":{}}},
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="Transaction ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(response=200, description="Transaction deleted successfully"),
         *     @OA\Response(response=404, description="Transaction not found"),
         *     @OA\Response(response=403, description="Forbidden")
         * )
         */
    public function destroy($id)
    {


         /** @var \App\Models\User $user */
        $user = Auth::user();
        if(!$user->tokenCan('delete_transaction')) {
           return $this->http->forbidden('Access denied');
        }

        $transaction = $user->transactions()->find($id);

        if (!$transaction) {
            return $this->http->notFound('Transaction not found or access denied');
        }

        $transaction->delete();

        return $this->http->ok('Transaction deleted successfully');
    }


    //Exporting archieves
     /**
         * @OA\Get(
         *     path="/api/transactions/export",
         *     summary="Export transactions to file",
         *     tags={"Transactions"},
         *     security={{"sanctum":{}}},
         *     @OA\Parameter(name="format", in="query", description="File format (xlsx or csv)", required=false, @OA\Schema(type="string")),
         *     @OA\Response(response=200, description="File download response"),
         *     @OA\Response(response=403, description="Forbidden")
         * )
         */
    public function exportFile(Request $request)
    {

       
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if(!$user->tokenCan('view_all_transactions')) {
           return $this->http->forbidden('Access denied');
        }

        // Captura filtros opcionais da requisição
        $filters = $request->only(['type', 'start_date', 'end_date']);
        $format = $request->get('format', 'xlsx'); // 'xlsx' ou 'csv'

        $fileName = 'transactions_' . now()->format('Ymd_His') . '.' . $format;

        //Retorna download do arquivo
        return Excel::download(
            new TransactionsExport($user, $filters),
            $fileName,
            $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
        );

    }

    //export DATA to download as PDF,CSV or XLSX
    
        /**
         * @OA\Get(
         *     path="/api/transactions/export-data",
         *     summary="Export transactions as JSON data",
         *     tags={"Transactions"},
         *     security={{"sanctum":{}}},
         *     @OA\Response(response=200, description="Transactions export data"),
         *     @OA\Response(response=403, description="Forbidden")
         * )
         */
    public function exportData(Request $request)
    {


        /** @var \App\Models\User $user */
        $user = Auth::user();

        if(!$user->tokenCan('view_all_transactions')) {
           return $this->http->forbidden('Access denied');
        }

        // Captura filtros opcionais da requisição
        $filters = $request->only(['type', 'start_date', 'end_date', 'category_ids']);

        // Consulta as transações do usuário com filtros aplicados
        $transactions = $user->transactions()
                            ->with('category')
                            ->filter($filters)
                            ->get()
                            ->map(function ($transaction) {
                                return [
                                    'id' => $transaction->id,
                                    'description' => $transaction->description,
                                    'amount' => $transaction->amount,
                                    'original_amount' => $transaction->original_amount,
                                    'currency' => $transaction->currency,
                                    'type' => $transaction->type,
                                    'date' => $transaction->date->format('Y-m-d'),
                                    'category' => $transaction->category ? $transaction->category->name : null,
                                    'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                                ];
                            });

        return $this->http->ok($transactions, 'Transaction export data');
    }

    //create report
     /**
         * @OA\Post(
         *     path="/api/transactions/generate-report",
         *     summary="Generate transaction report asynchronously",
         *     tags={"Transactions"},
         *     security={{"sanctum":{}}},
         *     @OA\RequestBody(
         *         required=false,
         *         @OA\JsonContent(
         *             @OA\Property(property="type", type="string", enum={"income","expense"}),
         *             @OA\Property(property="start_date", type="string", format="date"),
         *             @OA\Property(property="end_date", type="string", format="date")
         *         )
         *     ),
         *     @OA\Response(response=200, description="Report generation queued"),
         *     @OA\Response(response=403, description="Forbidden")
         * )
         */
    public function generateReport(Request $request)
    {

        $user = Auth::user();
        /** @var \App\Models\User $user */
        if(!$user->tokenCan('view_all_transactions')) {
           return $this->http->forbidden('Access denied');
        }

        $filters = $request->validate([
            'type' => 'nullable|in:income,expense',
            'start_date' => 'nullable|date|before_or_equal:end_date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
        // $filters = $request->only(['type', 'start_date', 'end_date']);
        // shot the information in job queue
        SendTransactionReport::dispatch($user->id, $filters)->delay(now()->addMinutes(1));

        return response()->json([
            'message' => 'Transaction report is being generated. You will be notified once it is ready.'
        ]);
    }
}
