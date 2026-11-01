<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_if($user && $user->disabled, 403, 'This account is disabled. Contact a panel administrator.');

        return $next($request);
    }
}
