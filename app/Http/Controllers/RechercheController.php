<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Vehicule;
use Illuminate\Http\Request;

class RechercheController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (mb_strlen($q) < 2) {
            return view('recherche.index', [
                'q'        => $q,
                'vehicules' => collect(),
                'clients'   => collect(),
                'trop_court' => mb_strlen($q) > 0,
            ]);
        }

        // Véhicules : immatriculation ou VIN
        $vehicules = Vehicule::with([
                'client',
                'ordresReparations' => fn($q) => $q->with(['facture', 'devis', 'technicien'])->orderByDesc('date_entree'),
            ])
            ->where('immatriculation', 'LIKE', '%' . $q . '%')
            ->orWhere('vin', 'LIKE', '%' . $q . '%')
            ->orderBy('immatriculation')
            ->limit(10)
            ->get();

        // Clients : nom, prénom, raison sociale ou téléphone
        $clients = Client::with([
                'vehicules',
                'ordresReparations' => fn($q) => $q->with(['vehicule', 'facture', 'devis', 'technicien'])->orderByDesc('date_entree'),
            ])
            ->where(function ($query) use ($q) {
                $query->where('nom',            'LIKE', '%' . $q . '%')
                      ->orWhere('prenom',         'LIKE', '%' . $q . '%')
                      ->orWhere('raison_sociale', 'LIKE', '%' . $q . '%')
                      ->orWhere('telephone',      'LIKE', '%' . $q . '%');
            })
            ->orderBy('nom')
            ->limit(10)
            ->get();

        // Exclure des résultats clients les véhicules déjà trouvés
        // (évite d'afficher deux fois la même info)
        $idsVehiculesDejaAffichés = $vehicules->pluck('client_id')->unique();

        return view('recherche.index', compact('q', 'vehicules', 'clients'));
    }
}
