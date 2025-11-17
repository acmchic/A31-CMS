<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!backpack_user()) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        $user = backpack_user();
        $permissions = explode('|', $permission);
        
        $hasPermission = false;
        foreach ($permissions as $perm) {
            $perm = trim($perm);
            if ($user->hasPermissionTo($perm)) {
                $hasPermission = true;
                break;
            }
        }
        
        if (!$hasPermission) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        return $next($request);
    }
}


