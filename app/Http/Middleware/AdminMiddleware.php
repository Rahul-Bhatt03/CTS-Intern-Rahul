<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
       if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required'
            ], 403);
        }

        return $next($request);
    }
}
