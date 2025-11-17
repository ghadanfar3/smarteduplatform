<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsTeacher
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->user() ||
            (auth()->user() && auth()->user()->role != "admin" && auth()->user()->role != "teacher" )){
            return \response()->json(["message" => "you are not authorized"],403);
        }
        return $next($request);

    }
}
