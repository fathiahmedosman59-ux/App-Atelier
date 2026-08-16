<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\BonCommande;
use Illuminate\Http\Request;

/**
 * Reçoit les mises à jour de disponibilité envoyées par stcd-magasin
 * après identification manuelle d'une pièce par le vendeur (cas où le
 * garage n'avait pas transmis de référence à la création du BC).
 */
class FournisseurReponseController extends Controller
{
    public function updateLigne(Request $request, string $numero, int $index)
    {
        $data = $request->validate([
            'disponible'          => 'required|boolean',
            'quantite_disponible' => 'nullable|numeric',
            'prix_unitaire'       => 'nullable|numeric|min:0',
            'note'                => 'nullable|string|max:255',
        ]);

        $bc = BonCommande::where('numero', $numero)->firstOrFail();
        $bc->load('lignes');

        $ligne = $bc->lignes->values()->get($index);
        if (! $ligne) {
            abort(404, 'Ligne introuvable pour ce bon de commande.');
        }

        $ligne->update([
            'disponible'          => $data['disponible'],
            'quantite_disponible' => $data['quantite_disponible'] ?? null,
            'prix_unitaire'       => $data['prix_unitaire'] ?? null,
            'note'                => $data['note'] ?? null,
        ]);
        $ligne->propagerVersDevis();

        $bc->update(['fournisseur_repondu_at' => now()]);

        Activite::journaliser(
            'maj_disponibilite_fournisseur',
            "Fournisseur : {$ligne->designation} ({$bc->numero}) marquée " . ($data['disponible'] ? 'disponible' : 'indisponible'),
            $bc
        );

        return response()->json(['ok' => true]);
    }
}
