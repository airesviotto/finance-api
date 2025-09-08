<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConvertTransactionsRequest;
use App\Services\ExchangeRateService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="ExchangeRate",
 *     description="Operations related to exchanges"
 * )
 */
class ExchangeRateController extends Controller
{
     protected $exchange;

    public function __construct(ExchangeRateService $exchange)
    {
        $this->exchange = $exchange;
    }

    /**
     * @OA\Get(
     *     path="/api/exchange",
     *     tags={"Exchange"},
     *     summary="List all supported currencies and rates",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="List of currencies and rates")
     * )
     */
    public function allCurrency(Request $request)
    {
        $base = $request->get('base', 'GBP'); // default currancy
        $data = $this->exchange->getAllRates($base);

        if (empty($data)) {
            return response()->json(['error' => 'Error to get quotation'], 500);
        }

        return response()->json([
            'base' => $base,
            'rates' => $data['rates'] ?? [],
            'updated_at' => $data['time_last_update_utc'] ?? null,
        ]);
    }

   /**
     * @OA\Get(
     *     path="/api/exchange/convert",
     *     tags={"Exchange"},
     *     summary="Convert an amount from one currency to another",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="amount", in="query", required=true, @OA\Schema(type="number")),
     *     @OA\Parameter(name="from", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="to", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Conversion result")
     * )
     */
    public function convert(Request $request)
    {

         $request->validate([
            'amount' => 'required|numeric',
            'from' => 'required|string|size:3',
            'to' => 'required|string|size:3',
        ]);

        $converted = $this->exchange->convert(
            $request->amount,
            strtoupper($request->from),
            strtoupper($request->to)
        );

        return response()->json([
            'original' => [
                'amount' => $request->amount,
                'currency' => strtoupper($request->from),
            ],
            'converted' => $converted,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/exchange/transactions",
     *     tags={"Exchange"},
     *     summary="Convert multiple transactions to a target currency",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"transactions","target_currency"},
     *             @OA\Property(property="transactions", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="amount", type="number"),
     *                     @OA\Property(property="currency", type="string")
     *                 )
     *             ),
     *             @OA\Property(property="target_currency", type="string", example="GBP")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Transactions converted")
     * )
     */
    public function convertTransactions(Request $request)
    {
        $request->validate([
            'transactions' => 'required|array',
            'transactions.*.amount' => 'required|numeric',
            'transactions.*.currency' => 'required|string|size:3',
            'to' => 'required|string|size:3',
        ]);

        $target = strtoupper($request->to);
        $result = [];

        foreach ($request->transactions as $tx) {
            $converted = $this->exchange->convert(
                $tx['amount'],
                strtoupper($tx['currency']),
                $target
            );

            $result[] = [
                'original' => $tx,
                'converted' => $converted,
            ];
        }

        return response()->json([
            'target_currency' => $target,
            'transactions' => $result,
        ]);
    }

    public function convertBatch(ConvertTransactionsRequest $request, ExchangeRateService $exchange)
    {
        $transactions = $request->input('transactions');
        $to = $request->input('to');

        $converted = [];
        foreach ($transactions as $transaction) {
            $converted[] = [
                'original_amount' => $transaction['amount'],
                'original_currency' => $transaction['currency'],
                'converted_amount' => $exchange->convert($transaction['amount'], $transaction['currency'], $to),
                'converted_currency' => $to,
            ];
        }

        return response()->json([
            'data' => $converted,
        ]);
    }
}