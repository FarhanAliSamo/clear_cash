<?php

namespace App\Http\Middleware;

use App\Models\CustomerAccountDetails;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            $user = Auth::user();

         
            if($user->hasRole(['super admin', 'admin'])) {
                return redirect()->route('admin.dashboard');
            }
            elseif($user->hasRole('staff')) {
                return redirect()->route('staff.dashboard');
            }
            elseif($user->hasRole('customer')) {
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
