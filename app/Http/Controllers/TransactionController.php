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

        // Atualiza apenas campos enviados
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
                                    'type' => $transaction->type,
                                    'date' => $transaction->date->format('Y-m-d'),
                                    'category' => $transaction->category ? $transaction->category->name : null,
                                    'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                                ];
                            });

        return $this->http->ok($transactions, 'Transaction export data');
    }

    //create report
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
