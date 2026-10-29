<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Permission
{
    public function handle(Request $request, Closure $next, string $key)
    {
        abort_unless($request->user()?->hasPerm($key), 403, 'You do not have permission to access this page.');

        return $next($request);
    }
}
