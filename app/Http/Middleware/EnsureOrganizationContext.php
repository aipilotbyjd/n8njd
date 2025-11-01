<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureOrganizationContext
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($request->user()->organizations()->count() === 0) {
            return response()->json(['message' => 'No organization. Create or join one.', 'error' => 'no_organization'], 403);
        }

        return $next($request);
    }
}
