<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExternalBonCommande;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Reçoit les bons de commande envoyés en temps réel par app-atelier, dès la
 * création d'un devis pièces (avant même sa validation), et répond
 * immédiatement avec la disponibilité et le prix de chaque pièce (par
 * référence, sur le stock actuel).
 *
 * Un même BC (même numéro) peut être renvoyé plusieurs fois — par exemple si
 * le chef de garage modifie la quantité d'une pièce après coup. Dans ce cas,
 * une ligne déjà identifiée par un vendeur (product_id renseigné, à la main
 * ou automatiquement par référence) n'est jamais réinitialisée : on se
 * contente de rafraîchir la quantité demandée et la disponibilité recalculée
 * sur le stock actuel, pour ne jamais perdre le travail déjà fait.
 */
class FournisseurBonCommandeController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'numero'                          => 'required|string',
            'vehicule.marque'                 => 'nullable|string',
            'vehicule.modele'                 => 'nullable|string',
            'vehicule.immatriculation'        => 'nullable|string',
            'vehicule.vin'                    => 'nullable|string',
            'client.nom'                      => 'nullable|string',
            'client.telephone'                => 'nullable|string',
            'pieces'                           => 'required|array|min:1',
            'pieces.*.reference'              => 'nullable|string',
            'pieces.*.designation'            => 'nullable|string',
            'pieces.*.quantite'               => 'required|numeric|min:0.01',
        ]);

        $bc = DB::transaction(function () use ($data) {
            $bc = ExternalBonCommande::updateOrCreate(
                ['numero' => $data['numero']],
                [
                    'source_system'            => 'app-atelier',
                    'vehicule_marque'          => $data['vehicule']['marque'] ?? null,
                    'vehicule_modele'          => $data['vehicule']['modele'] ?? null,
                    'vehicule_immatriculation' => $data['vehicule']['immatriculation'] ?? null,
                    'vehicule_vin'             => $data['vehicule']['vin'] ?? null,
                    'client_nom'               => $data['client']['nom'] ?? null,
                    'client_telephone'         => $data['client']['telephone'] ?? null,
                    'statut'                   => 'recu',
                ]
            );

            $bc->load('lignes');
            $lignesParPosition = $bc->lignes->keyBy('position');
            $positionsGardees = [];

            foreach (array_values($data['pieces']) as $position => $piece) {
                $reference = trim((string) ($piece['reference'] ?? '')) ?: null;
                $quantiteDemandee = (float) $piece['quantite'];
                $existante = $lignesParPosition->get($position);

                if ($existante && $existante->product_id) {
                    // Déjà identifiée (par référence ou manuellement par un vendeur) :
                    // on garde le produit choisi, on rafraîchit juste la quantité
                    // demandée et la disponibilité sur le stock actuel.
                    $existante->update([
                        'designation'         => $piece['designation'] ?? $existante->designation,
                        'quantite_demandee'   => $quantiteDemandee,
                        'quantite_disponible' => $existante->product->quantity,
                        'disponible'          => $existante->product->quantity >= $quantiteDemandee,
                    ]);
                    $positionsGardees[] = $position;
                    continue;
                }

                if ($reference === null) {
                    // Pas de référence fournie (ex: main d'œuvre, peinture...) : impossible
                    // de vérifier le stock automatiquement, à traiter manuellement.
                    $bc->lignes()->updateOrCreate(
                        ['position' => $position],
                        [
                            'product_id'          => null,
                            'reference'           => null,
                            'designation'         => $piece['designation'] ?? null,
                            'quantite_demandee'   => $quantiteDemandee,
                            'quantite_disponible' => null,
                            'disponible'          => null,
                        ]
                    );
                    $positionsGardees[] = $position;
                    continue;
                }

                $product = Product::where('reference', $reference)->first();
                $quantiteDisponible = $product?->quantity ?? 0;

                $bc->lignes()->updateOrCreate(
                    ['position' => $position],
                    [
                        'product_id'          => $product?->id,
                        'reference'           => $reference,
                        'designation'         => $piece['designation'] ?? null,
                        'quantite_demandee'   => $quantiteDemandee,
                        'quantite_disponible' => $quantiteDisponible,
                        'disponible'          => $quantiteDisponible >= $quantiteDemandee,
                        'prix_unitaire'       => $product?->sale_price,
                    ]
                );
                $positionsGardees[] = $position;
            }

            // Pièces retirées du devis depuis le dernier envoi.
            $bc->lignes()->whereNotIn('position', $positionsGardees)->delete();

            return $bc;
        });

        $bc->load('lignes');

        return response()->json([
            'numero' => $bc->numero,
            'statut' => $bc->statut,
            'pieces' => $bc->lignes->sortBy('position')->values()->map(fn ($ligne) => [
                'index'               => $ligne->position,
                'reference'           => $ligne->reference,
                'disponible'          => $ligne->disponible,
                'quantite_disponible' => $ligne->quantite_disponible !== null ? (float) $ligne->quantite_disponible : null,
                'prix_unitaire'       => $ligne->prix_unitaire !== null ? (float) $ligne->prix_unitaire : null,
                'note'                => $ligne->note,
            ]),
        ]);
    }
}
