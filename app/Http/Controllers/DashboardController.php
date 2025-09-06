<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Services\HttpResponseService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $http;

    public function __construct(HttpResponseService $http)
    {
        $this->http = $http;
    }

    public function summary(Request $request)
    {   
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if(!$user->tokenCan('view_dashboard')) {
            return $this->http->forbidden('Access denied');
        }

        // Total por categoria
        $byCategory = $user->transactions()
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(function($t) {
                return [
                    'category' => $t->category->name,
                    'total' => $t->total
                ];
            });

        // Total por tipo
        $byType = $user->transactions()
            ->select('type', DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->get();

        // Total por mês (ano-mês)
        $byMonth = $user->transactions()
            ->select(DB::raw("DATE_FORMAT(date, '%Y-%m') as month"), DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'message' => 'Dashboard summary',
            'data' => [
                'by_category' => $byCategory,
                'by_type' => $byType,
                'by_month' => $byMonth
            ]
        ]);
    }


    public function advancedSummary(Request $request)
    {

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if(!$user->tokenCan('view_dashboard')) {
            return $this->http->forbidden('Access denied');
        }

        // Optional filters
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = $user->transactions();

        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }

        // Totals per type (income/expense)
        $totalsByType = (clone $query) // close avoid that the same query be modified in each calculation
            ->select('type', DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->get();

        // Top 5 categories per total
        $byCategory = (clone $query)
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function($t) use ($user, $dateFrom, $dateTo) {

                // Recalculate income and expense for each category
                $transactions = $user->transactions()
                    ->where('category_id', $t->category_id);

                if ($dateFrom) $transactions->whereDate('date', '>=', $dateFrom);
                if ($dateTo) $transactions->whereDate('date', '<=', $dateTo);

                $income = $transactions->where('type', 'income')->sum('amount');
                $expense = $transactions->where('type', 'expense')->sum('amount');

                return [
                    'category' => $t->category->name,
                    'total' => $t->total,
                    'type_breakdown' => [
                        'income' => $income,
                        'expense' => $expense
                    ]
                ];
            });

        // Totals per month
        $byMonth = (clone $query)
            ->select(DB::raw("DATE_FORMAT(date, '%Y-%m') as month"), DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'message' => 'Advanced dashboard summary',
            'data' => [
                'totals_by_type' => $totalsByType,
                'top_categories' => $byCategory,
                'totals_by_month' => $byMonth
            ]
        ]);
    }
}
