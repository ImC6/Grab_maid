<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        switch (strtolower($role)) {
            case 'admin':
                $roleId = 1;
                break;
            case 'vendor':
                $roleId = 2;
                break;
            case 'cleaner':
                $roleId = 3;
                break;
            case 'user':
                $roleId = 4;
                break;

            default:
                $roleId = 0;
        }

        if (! $request->user()->hasRole($roleId)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 403,
                    'message' => 'Forbidden'
                ]);
            }
            return back();
        }

        return $next($request);
    }
}
