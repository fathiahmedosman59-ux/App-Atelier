<?php

namespace App\Http\Controllers;

use App\Models\NotificationInterne;

/**
 * Contrôleur des notifications internes (cloche en haut de l'écran).
 * Ces notifications préviennent un utilisateur d'un événement qui le concerne
 * (garantie à traiter, véhicule prêt à restituer...).
 */
class NotificationController extends Controller
{
    /**
     * Marque une notification comme lue et redirige vers l'OR concerné.
     */
    public function ouvrir(NotificationInterne $notification)
    {
        if ($notification->destinataire_id !== auth()->id()) abort(403);

        $notification->marquerLue();

        if ($notification->or_id) {
            return redirect()->route('ordres-reparations.show', $notification->or_id);
        }

        return back();
    }

    /**
     * Marque toutes les notifications de l'utilisateur connecté comme lues.
     */
    public function toutMarquerLu()
    {
        NotificationInterne::where('destinataire_id', auth()->id())
            ->whereNull('lu_at')
            ->update(['lu_at' => now()]);

        return back()->with('success', 'Notifications marquées comme lues.');
    }
}
