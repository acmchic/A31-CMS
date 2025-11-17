<?php

namespace App\Http\Middleware;

use Closure;

class CheckIfAdmin
{
    /**
     * Checked that the logged in user is an administrator.
     *
     * --------------
     * VERY IMPORTANT
     * --------------
     * If you have both regular users and admins inside the same table, change
     * the contents of this method to check that the logged in user
     * is an admin, and not a regular user.
     *
     * Additionally, in Laravel 7+, you should change app/Providers/RouteServiceProvider::HOME
     * which defines the route where a logged in user (but not admin) gets redirected
     * when trying to access an admin route. By default it's '/home' but Backpack
     * does not have a '/home' route, use something you've built for your users
     * (again - users, not admins).
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|null  $user
     * @return bool
     */
    private function checkIfUserIsAdmin($user)
    {
        if (!$user) return false;
        
        if ($user->hasAnyPermission([
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'role.view', 'role.create', 'role.edit', 'role.delete',
            'permission.view', 'permission.create', 'permission.edit', 'permission.delete'
        ])) {
            return true;
        }
        
        return $user->getAllPermissions()->count() > 0;
    }

    /**
     * Answer to unauthorized access request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    private function respondToUnauthorizedRequest($request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response(trans('backpack::base.unauthorized'), 401);
        } else {
            return redirect()->guest(backpack_url('login'));
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (backpack_auth()->guest()) {
            return $this->respondToUnauthorizedRequest($request);
        }

        // Allow dashboard access for all authenticated users
        if ($request->routeIs('backpack.dashboard') || $request->is('dashboard') || $request->is('admin/dashboard')) {
            return $next($request);
        }

        // Allow profile routes for all authenticated users (they can only edit their own profile)
        $profileRoutes = [
            'backpack.account.info',
            'admin.profile.edit',
            'admin.profile.update',
            'admin.profile.upload-photo',
            'admin.profile.upload-signature',
            'admin.profile.change-password',
            'admin.profile.update-pin',
            'admin.profile.delete-photo',
            'admin.profile.delete-signature',
            'backpack.account.info.store'
        ];
        
        foreach ($profileRoutes as $routeName) {
            if ($request->routeIs($routeName)) {
                return $next($request);
            }
        }
        
        // Also check by path patterns
        if ($request->is('edit-account-info') || $request->is('account/info') || 
            $request->is('admin/edit-account-info') || $request->is('admin/account/info') ||
            $request->is('profile*') || $request->is('admin/profile*')) {
            return $next($request);
        }

        if (! $this->checkIfUserIsAdmin(backpack_user())) {
            return $this->respondToUnauthorizedRequest($request);
        }

        return $next($request);
    }
}
