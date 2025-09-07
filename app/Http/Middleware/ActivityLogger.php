<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;

class ActivityLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //calculate time
        $start = microtime(true);

        $response = $next($request);

        //calculation in milisecond
        $duration = round((microtime(true) - $start) * 1000, 2);
        if ($request->user()) {
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => $request->method() . ' ' . $request->path(),
                'ip_address' => $request->ip(),
                'details'    => [
                    'input'      => $request->all(),              // payload do body
                    'query'      => $request->query(),            // query params da URL
                    'user_agent' => $request->header('User-Agent', $request->server('HTTP_USER_AGENT')), // navegador/app cliente
                    'status'     => $response->getStatusCode(),
                    'duration_ms'=> $duration, 
                ],
            ]);
        }

        return $response;

    }
}
