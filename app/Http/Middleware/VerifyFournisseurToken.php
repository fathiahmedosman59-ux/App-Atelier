<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vérifie que l'appel entrant provient bien du système fournisseur
 * (stcd-magasin), via un jeton secret partagé (pas un compte utilisateur).
 */
class VerifyFournisseurToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $attendu = config('services.stcd_magasin.inbound_token');

        if (! $token || ! $attendu || ! hash_equals($attendu, $token)) {
            abort(401, 'Jeton fournisseur invalide.');
        }

        return $next($request);
    }
}
