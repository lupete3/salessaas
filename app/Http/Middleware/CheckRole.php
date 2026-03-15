<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckRole — vérifie que l'utilisateur possède le rôle requis
 * Usage: ->middleware('role:proprietaire,pharmacien')
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !$user->role) {
            abort(403, __('auth.unauthorized'));
        }

        if (!empty($roles) && !in_array($user->role->slug, $roles)) {
            abort(403, __('auth.unauthorized'));
        }

        return $next($request);
    }
}
