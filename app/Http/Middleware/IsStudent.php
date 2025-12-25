<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsStudent
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            // لو مش مسجل دخول
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->role !== 'student') {
            // لو مش طالب
            return response()->json(['message' => 'Forbidden: فقط الطلاب يمكنهم الوصول'], 403);
        }

        return $next($request);
    }
}
