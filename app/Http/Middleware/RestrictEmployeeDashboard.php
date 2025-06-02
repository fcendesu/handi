<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictEmployeeDashboard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // If user is a company employee, deny access to web dashboard
        if ($user && $user->isCompanyEmployee()) {
            abort(403, 'Company employees cannot access the web dashboard. Please use the mobile application.');
        }

        return $next($request);
    }
}
