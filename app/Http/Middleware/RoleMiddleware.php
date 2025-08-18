<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$requiredPermissions)
    {
        $user = Auth::user();

        // Check if user is authenticated
        if (!$user) {
            return redirect('/TDS/auth/login');
        }

        // Get the user's role with privileges
        $role = $user->roles;
        // Check if role exists and has privileges
        if (!$role || !isset($role->privileges)) {
            return redirect('/TDS/auth/login');
        }

        // Check each required permission
        foreach ($requiredPermissions as $permission) {
            // Split permission into parts (menu and optional action)
            $parts = explode('.', $permission);
            $menu = $parts[0];
            $action = $parts[1] ?? null; // null means any action in the menu

            // Check menu exists in privileges
            if (!array_key_exists($menu, $role->privileges)) {
                return redirect('/TDS/auth/login');
            }

            // If specific action is required
            if ($action && !in_array($action, $role->privileges[$menu])) {
                return redirect('/TDS/auth/login');
            }
        }

        return $next($request);
    }
}
