<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscribeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if(!$request->user()->isSuperAdmin) {
            if(!$request->user()->subscriptionDetails)
            {
                return response()->json(['subscribe' => 'You need to buy a package to use the service.'], Response::HTTP_FORBIDDEN);
            }

            if($request->user()->subscriptionDetails['is_expired'])
            {
                return response()->json(['subscribe' => 'You need to buy a package to use the service.'], Response::HTTP_FORBIDDEN);
            }
        }

        return $next($request);
    }
}
