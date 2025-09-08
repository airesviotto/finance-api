<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\HttpResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Report",
 *     description="Operations related to create reports"
 * )
 */
class ReportController extends Controller
{
    protected $http;

    public function __construct(HttpResponseService $http)
    {
        $this->http = $http;
    }

    /**
     * @OA\Get(
     *     path="/api/report/monthly-average",
     *     tags={"Reports"},
     *     summary="Get the user's monthly average income and expenses",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Monthly averages returned"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function monthlyAverage(): JsonResponse
    {   
        $user = Auth::user();

        $data = Transaction::selectRaw('YEAR(date) as year, MONTH(date) as month, AVG(amount) as avg_amount')
            ->where('user_id', $user->id)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return $this->http->ok($data);
    }

    /**
     * @OA\Get(
     *     path="/api/report/category-comparison",
     *     tags={"Reports"},
     *     summary="Compare expenses by category",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Category comparison returned"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function categoryComparison(): JsonResponse
    {
        $user = Auth::user();
        $month = now()->month;
        $year = now()->year;

        $data = Transaction::selectRaw('categories.name, SUM(amount) as total')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $user->id)
            ->whereMonth('transactions.date', $month)
            ->whereYear('transactions.date', $year)
            ->groupBy('categories.name')
            ->get();

        return $this->http->ok($data);
    }

    /**
     * @OA\Get(
     *     path="/api/report/top-expenses",
     *     tags={"Reports"},
     *     summary="Get top 10 highest expenses",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Top expenses returned"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function topExpenses(): JsonResponse
    {
        $user = Auth::user();

        $data = Transaction::where('user_id', $user->id)
            ->where('type',  ['expense', 'Expense', 'EXPENSE'])
            ->orderByDesc('amount')
            ->limit(10)
            ->get();

        return $this->http->ok($data);
    }
}
