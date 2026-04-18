<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, \Closure $next, string $type)
    {
        if (Auth::check() && Auth::user()->usertype === $type) {
            return $next($request);
        }

        abort(403, 'Bạn không có quyền truy cập.');
    }
}
