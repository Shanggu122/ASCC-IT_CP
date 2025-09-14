<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request): ?string
    {
        if ($request->expectsJson()) {
            return null; // JSON requests get 401
        }
        // Prefer guard-aware detection: if route uses auth:admin middleware redirect there
        try {
            $route = $request->route();
            if ($route) {
                $middlewares = method_exists($route, 'gatherMiddleware') ? $route->gatherMiddleware() : [];
                foreach ($middlewares as $mw) {
                    if (is_string($mw) && str_starts_with($mw, 'auth:') && str_contains($mw, 'admin')) {
                        return url('/login/admin');
                    }
                }
            }
        } catch (\Throwable $e) { /* ignore */ }
        $path = $request->path();
        if (str_starts_with($path, 'admin') || str_contains($path, 'admin/')) { // fallback path heuristic
            return url('/login/admin');
        }
        // Default guest redirect (student area); professor handled by EnsureProfessorAuthenticated
        return url('/login');
    }
}
