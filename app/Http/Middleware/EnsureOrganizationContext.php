<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Check if user has an organization
        if (! $user->org_id) {
            return response()->json([
                'message' => 'No organization context. Please contact support.',
                'error' => 'missing_organization',
            ], 403);
        }

        // Load the organization and check if it's active
        $organization = $user->organization;

        if (! $organization) {
            return response()->json([
                'message' => 'Organization not found.',
                'error' => 'organization_not_found',
            ], 404);
        }

        if (! $organization->isActive()) {
            return response()->json([
                'message' => 'Your organization is suspended or inactive.',
                'error' => 'organization_inactive',
                'reason' => $organization->suspension_reason,
            ], 403);
        }

        // Add organization to request for easy access
        $request->attributes->set('organization', $organization);

        return $next($request);
    }
}
