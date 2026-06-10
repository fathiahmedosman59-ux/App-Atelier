<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Anti-clickjacking : aucun iframe autorisé
        $response->headers->set('X-Frame-Options', 'DENY');
        // Empêche le MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        // XSS filter navigateur (legacy)
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        // Ne pas envoyer l'URL complète en Referer vers d'autres domaines
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        // Désactiver les APIs navigateur inutiles
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        // Aucun accès cross-domain aux ressources Flash / PDF
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // Pages authentifiées : interdire la mise en cache navigateur (bouton Précédent)
        if (auth()->check()) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}
