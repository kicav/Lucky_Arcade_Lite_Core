<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminArea
{
    public function handle(Request $request, Closure $next, string $area): Response
    {
        abort_unless($request->user()?->canAccessAdminArea($area), 403);
        return $next($request);
    }
}
