<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureOrganizationContext
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($user->organizations()->count() === 0) {
            return response()->json([
                'message' => 'No organization found. Please create or join one.',
                'error' => 'missing_organization'
            ], 403);
        }

        return $next($request);
    }
}
