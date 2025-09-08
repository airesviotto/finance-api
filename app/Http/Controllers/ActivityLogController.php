<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\HttpResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="ActivityLog",
 *     description="Operations related to log activities"
 * )
 */
class ActivityLogController extends Controller
{
    protected $http;

    public function __construct(HttpResponseService $http)
    {
        $this->http = $http;
    }


    /**
     * @OA\Get(
     *     path="/api/logs/activity-logs",
     *     tags={"Activity Logs"},
     *     summary="List all activity logs",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Paginated activity logs returned"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Access denied")
     * )
     */
    public function index()
    {   
         /** @var \App\Models\User $user */
        $user = Auth::user();
        if(!$user->tokenCan('manage_logs')) {
            return $this->http->forbidden('Access denied');
        }
        return ActivityLog::with('user')
            ->latest()
            ->paginate(20);
    }


    /**
     * @OA\Get(
     *     path="/api/logs/activity-logs/stats",
     *     tags={"Activity Logs"},
     *     summary="Get aggregated activity log statistics",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Activity log stats returned"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Access denied")
     * )
     */
    public function logStatsDetail(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if(!$user->tokenCan('manage_logs')) {
            return $this->http->forbidden('Access denied');
        }
        
        //avarage time per endpoint (route)
        $avgDuration = ActivityLog::select(
        DB::raw("action"),
        DB::raw("ROUND(AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(details, '$.duration_ms')) AS DECIMAL(10,2))), 2) as avg_duration_ms"),
        DB::raw("COUNT(*) as total_requests")
        )
        ->groupBy('action')
        ->orderByDesc('total_requests')
        ->get();

        //POSTGRESQL
        // $avgDuration = ActivityLog::select(
        //         DB::raw("action"),
        //         DB::raw("ROUND(AVG(CAST(details->>'duration_ms' AS numeric)), 2) as avg_duration_ms"),
        //         DB::raw("COUNT(*) as total_requests")
        //     )
        //     ->groupBy('action')
        //     ->orderByDesc('total_requests')
        //     ->get();

        //Total requests per user
        $requestsPerUser = ActivityLog::select(
                'user_id',
                DB::raw('COUNT(*) as total_requests')
            )
            ->groupBy('user_id')
            ->with('user')
            ->get();

        return response()->json([
            'avg_duration_per_endpoint' => $avgDuration,
            'requests_per_user' => $requestsPerUser,
        ]);
    }
}
