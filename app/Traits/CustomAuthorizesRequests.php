<?php

namespace App\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests as BaseAuthorizesRequests;

trait CustomAuthorizesRequests
{
    use BaseAuthorizesRequests {
        authorize as protected baseAuthorize; // Alias the original authorize method
    }

    /**
     * Authorize a given action for the current user, with admin guard support.
     *
     * @param string $ability
     * @param mixed $arguments
     * @return Response|void
     * @throws AuthorizationException
     */
    public function authorize($ability, $arguments = [])
    {
        // Check if the user is authenticated via the admin guard
        if (Auth::guard('admin')->check()) {
            return; // Admins bypass authorization checks entirely
        }

        // Fall back to the base authorize method for non-admin users
        return $this->baseAuthorize($ability, $arguments);
    }
}
