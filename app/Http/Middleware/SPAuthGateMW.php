<?php

namespace App\Http\Middleware;

use App\Models\PermissionRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;

class SPAuthGateMW
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('admin')->check()) {
            // Super admin — allow everything
            Gate::before(fn () => true);
            return $next($request);
        }

        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $roleId = $user->role_id;


        if (!$roleId) {
            return $next($request);
        }

        // Cache permissions for 1 hour (or per role)
        $cacheKey = "user_permissions_role_{$roleId}";
        $permissions = Cache::remember($cacheKey, 3600, function () use ($roleId) {
            return DB::table('permission_roles')
                ->join('permissions', 'permission_roles.permission_id', '=', 'permissions.id')
                ->where('permission_roles.role_id', $roleId)
                ->pluck('permissions.permission_key')
                ->toArray();
        });

        // Define gates once per request (still needed, but now fast + cached)
        foreach ($permissions as $permissionKey) {
            Gate::define($permissionKey, fn () => true);
        }

        return $next($request);
    }
}
