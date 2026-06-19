<?php

namespace App\Http\Controllers;

use App\Models\NotificationInterne;
use App\Models\OrdreReparation;

class GarantieController extends Controller
{
    public function index()
    {
        if (! auth()->user()->hasPermission('voir_garanties')) abort(403);

        // Marquer toutes les notifications garantie comme lues
        NotificationInterne::where('destinataire_id', auth()->id())
            ->whereNull('lu_at')
            ->update(['lu_at' => now()]);

        $enAttente = OrdreReparation::where('type', 'garantie')
            ->where('statut_garantie', 'en_attente')
            ->with(['client', 'vehicule', 'conseiller'])
            ->latest()
            ->get();

        $approuves = OrdreReparation::where('type', 'garantie')
            ->where('statut_garantie', 'approuve')
            ->with(['client', 'vehicule'])
            ->latest()
            ->limit(30)
            ->get();

        $refuses = OrdreReparation::where('type', 'garantie')
            ->where('statut_garantie', 'refuse')
            ->with(['client', 'vehicule'])
            ->latest()
            ->limit(30)
            ->get();

        return view('garanties.index', compact('enAttente', 'approuves', 'refuses'));
    }
}
