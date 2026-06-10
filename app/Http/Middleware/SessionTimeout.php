<?php

namespace App\Http\Middleware;

use App\Models\Activite;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    private const TIMEOUT = 600; // 10 minutes en secondes

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $lastActivity = $request->session()->get('last_activity');

        if ($lastActivity !== null && (time() - $lastActivity) > self::TIMEOUT) {
            // Journaliser AVANT logout (auth()->user() encore disponible)
            Activite::journaliser('session_expiree', 'Session expirée après 10 min d\'inactivité');

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('timeout', 'Votre session a expiré après 10 minutes d\'inactivité. Veuillez vous reconnecter.');
        }

        $request->session()->put('last_activity', time());

        return $next($request);
    }
}
