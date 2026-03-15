<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureTenant — vérifie que l'utilisateur est lié à un magasin actif
 * et configure la locale de l'application.
 */
class EnsureTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, __('auth.no_store'));
        }

        if ($user->isSuperAdmin()) {
            view()->share('currentUser', $user);
            view()->share('currentStore', null);
            view()->share('currency', 'CDF');
            return $next($request);
        }

        if (!$user->store_id) {
            abort(403, __('auth.no_store'));
        }

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', __('auth.account_disabled'));
        }

        // Charger le magasin en mémoire (évite les N+1)
        $store = $user->load('store')->store;

        if (!$store || $store->subscription_status === 'expired') {
            return redirect()->route('subscription.expired');
        }

        // Appliquer la locale: priorité à la session (si l'utilisateur change manuellement)
        // sinon préférence utilisateur, sinon préférence magasin.
        $locale = session('locale') ?? $user->locale ?? $store->locale ?? config('app.locale');
        App::setLocale($locale);

        // Partager avec toutes les vues
        $currency = $store->currency ?: 'CDF';

        view()->share('currentStore', $store);
        view()->share('currentUser', $user);
        view()->share('currency', $currency);

        return $next($request);
    }
}
