<?php

namespace App\Http\Middleware;

use App\Helpers\AppHelper;
use App\Models\Role;
use Closure;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check admin guard first
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            if (!$user) {
                $request->session()->invalidate();
                return redirect()->route('admin.login');
            }
            return $next($request);
        }

        // Then check default guard
        if (Auth::check()) {
            $user = Auth::user();
            if (!$user || !in_array($user->role?->slug, AppHelper::getBackendLoginAuthorizedRole())) {
                $request->session()->invalidate();
                return redirect()->route('admin.login');
            }
            return $next($request);
        }

        // If neither guard is authenticated
        $request->session()->invalidate();
        return redirect()->route('admin.login');
    }
}



