<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPremiumExpired
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            $user = $request->user();
            
            if ($user->is_premium && $user->premium_expires_at && $user->premium_expires_at->isPast()) {
                $user->update(['is_premium' => false]);
            }
        }

        return $next($request);
    }
}
