<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Transcription des barèmes d'entretien constructeur Great Wall Motors
 * (calendriers fournis par l'utilisateur) pour les moteurs GW4D20 et GW4C20.
 *
 * IMPORTANT : les lignes "R à chaque palier" (huiles, filtres, boulons,
 * freins, pneus, batterie, fuites, éclairage...) sont transcrites avec
 * confiance (identiques sur toute la ligne du tableau). Les lignes à motif
 * périodique/alterné (poulies, vanne EGR, échangeur, boîte de vitesses,
 * permutation pneus, filtres air/climatisation, liquides refroidissement/
 * frein) sont une transcription au meilleur effort depuis le tableau fourni
 * — à vérifier avec le carnet d'entretien constructeur avant utilisation
 * réelle, une erreur de lecture du tableau serait sinon invisible.
 */
return new class extends Migration
{
    public function up(): void
    {
        $moteurGW4D20 = DB::table('types_moteur')->insertGetId([
            'code' => 'GW4D20',
            'libelle' => 'GW4D20',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $moteurGW4C20 = DB::table('types_moteur')->insertGetId([
            'code' => 'GW4C20',
            'libelle' => 'GW4C20',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->seedGW4D20($moteurGW4D20);
        $this->seedGW4C20($moteurGW4C20);
    }

    public function down(): void
    {
        $ids = DB::table('types_moteur')->whereIn('code', ['GW4D20', 'GW4C20'])->pluck('id');
        DB::table('entretien_taches')->whereIn('type_moteur_id', $ids)->delete();
        DB::table('types_moteur')->whereIn('code', ['GW4D20', 'GW4C20'])->delete();
    }

    /**
     * Insère une tâche pour une liste de paliers km donnés.
     */
    private function inserer(int $typeMoteurId, string $designation, string $action, array $kmSeuils, ?array $moisSeuils = null): void
    {
        $rows = [];
        foreach ($kmSeuils as $i => $km) {
            $rows[] = [
                'type_moteur_id' => $typeMoteurId,
                'designation'    => $designation,
                'action'         => $action,
                'km_seuil'       => $km,
                'mois_seuil'     => $moisSeuils[$i] ?? null,
                'ordre'          => 0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }
        DB::table('entretien_taches')->insert($rows);
    }

    private function seedGW4D20(int $id): void
    {
        $mois = [6, 12, 18, 24, 30, 36, 42, 48, 54, 60, 66, 72, 78, 84, 90, 96, 102];
        $km   = [5000, 10000, 16000, 22000, 28000, 34000, 40000, 46000, 52000, 58000, 64000, 70000, 76000, 82000, 88000, 94000, 100000];

        // ── Remplacements à chaque palier ──────────────────────────────
        foreach ([
            'Huile moteur',
            "Joint / bouchon de vidange du carter d'huile",
            'Filtre à huile moteur',
            'Filtre à carburant',
        ] as $designation) {
            $this->inserer($id, $designation, 'remplacer', $km, $mois);
        }

        // ── Inspections à chaque palier ─────────────────────────────────
        foreach ([
            'Boulons et écrous importants',
            'Frein à disque',
            'Frein de stationnement',
            'Pression et usure des pneus',
            'Parallélisme des 4 roues',
            'Rotule et soufflet de protection',
            'Radiateur (aspect visuel)',
            'Échangeur (aspect visuel)',
            'Liquide de direction assistée',
            'Batterie',
            'Fuites',
            'Éclairage / Feux',
        ] as $designation) {
            $this->inserer($id, $designation, 'inspecter', $km, $mois);
        }

        // ── Motifs périodiques (au meilleur effort — à vérifier) ────────
        // Poulie tendeuse/renvoi/courroie : inspection tous les 24 mois à partir de 18 mois (index 2,6,10,14)
        $this->inserer($id, 'Poulie tendeuse, poulie de renvoi et poulie de courroie', 'inspecter',
            [$km[2], $km[6], $km[10], $km[14]], [$mois[2], $mois[6], $mois[10], $mois[14]]);

        // Vanne EGR : nettoyage tous les 24 mois à partir de 30 mois (index 4,8,12,16)
        $this->inserer($id, 'Vanne EGR', 'nettoyer',
            [$km[4], $km[8], $km[12], $km[16]], [$mois[4], $mois[8], $mois[12], $mois[16]]);

        // Échangeur (intercooler) et tuyauterie de raccordement : même rythme que la vanne EGR
        $this->inserer($id, 'Échangeur (intercooler) et tuyauterie de raccordement', 'nettoyer',
            [$km[4], $km[8], $km[12], $km[16]], [$mois[4], $mois[8], $mois[12], $mois[16]]);

        // Huile de boîte de vitesses (BVM) : remplacement tous les 30 mois (index 0,5,10,15), inspection les autres paliers
        $paliersRemplacementBVM = [0, 5, 10, 15];
        foreach ($km as $i => $k) {
            $this->inserer($id, 'Huile de boîte de vitesses (BVM)', in_array($i, $paliersRemplacementBVM) ? 'remplacer' : 'inspecter', [$k], [$mois[$i]]);
        }

        // Permutation des pneus : tous les paliers pairs (24, 48, 72, 96 mois → index 3,7,11,15)
        $this->inserer($id, 'Permutation des pneus', 'remplacer',
            [$km[3], $km[7], $km[11], $km[15]], [$mois[3], $mois[7], $mois[11], $mois[15]]);

        // Élément du filtre à air : alterné nettoyage/remplacement (C,R,C,R...)
        foreach ($km as $i => $k) {
            $this->inserer($id, 'Élément du filtre à air', $i % 2 === 0 ? 'nettoyer' : 'remplacer', [$k], [$mois[$i]]);
        }

        // Filtre de climatisation : même alternance que le filtre à air
        foreach ($km as $i => $k) {
            $this->inserer($id, 'Filtre de climatisation', $i % 2 === 0 ? 'nettoyer' : 'remplacer', [$k], [$mois[$i]]);
        }

        // Liquide de refroidissement moteur / Liquide de frein : remplacement tous les 24 mois (index 3,7,11,15), inspection sinon
        foreach (['Liquide de refroidissement moteur', 'Liquide de frein'] as $designation) {
            foreach ($km as $i => $k) {
                $action = in_array($i, [3, 7, 11, 15]) ? 'remplacer' : 'inspecter';
                $this->inserer($id, $designation, $action, [$k], [$mois[$i]]);
            }
        }

        // ── Notes informatives (pas liées à un palier précis du tableau) ──
        $this->inserer($id, 'Courroie de distribution — ne pas dépasser 80 000 km', 'inspecter', [80000], [null]);
        $this->inserer($id, 'Courroie d\'alternateur / pompe à eau — ne pas dépasser 100 000 km maximum', 'inspecter', [100000], [null]);
        $this->inserer($id, 'Courroie de pompe de direction assistée — ne pas dépasser 100 000 km maximum', 'inspecter', [100000], [null]);
        $this->inserer($id, 'Huile de boîte de transfert — remplacement à 50 000 km, puis tous les 150 000 km', 'remplacer', [50000], [null]);
        $this->inserer($id, 'Huile de différentiel — remplacement à 50 000 km / 36 mois, puis tous les 100 000 km / 36 mois', 'remplacer', [50000], [36]);
    }

    private function seedGW4C20(int $id): void
    {
        $mois = [6, 12, 18, 24, 30, 36, 42, 48, 54, 60, 66, 72, 78, 84, 90];
        $km   = [5000, 12500, 20000, 27500, 35000, 42500, 50000, 57500, 65000, 72500, 80000, 87500, 95000, 102500, 110000];

        foreach ([
            'Huile moteur',
            "Joint / bouchon de vidange du carter d'huile",
            'Filtre à huile moteur',
        ] as $designation) {
            $this->inserer($id, $designation, 'remplacer', $km, $mois);
        }

        // Filtre à carburant : remplacement à partir du palier 24 mois (index 3) selon le tableau fourni
        $this->inserer($id, 'Filtre à carburant', 'remplacer', array_slice($km, 3), array_slice($mois, 3));

        foreach ([
            'Boulons et écrous importants',
            'Frein à disque',
            'Frein de stationnement',
            'Pression et usure des pneus',
            'Parallélisme des 4 roues',
            'Rotule et soufflet de protection',
            'Radiateur (aspect visuel)',
            'Échangeur (aspect visuel)',
            'Liquide de direction assistée',
            'Batterie',
            'Fuites',
            'Éclairage / Feux',
        ] as $designation) {
            $this->inserer($id, $designation, 'inspecter', $km, $mois);
        }

        // Papillon des gaz : nettoyage tous les 24 mois à partir de 24 mois (index 3,7,11)
        $this->inserer($id, 'Papillon des gaz', 'nettoyer',
            [$km[3], $km[7], $km[11]], [$mois[3], $mois[7], $mois[11]]);

        // Intérieur de l'échangeur et tuyauterie : même rythme que le papillon des gaz
        $this->inserer($id, "Intérieur de l'échangeur et tuyauterie", 'nettoyer',
            [$km[3], $km[7], $km[11]], [$mois[3], $mois[7], $mois[11]]);

        // Bougie d'allumage : remplacement tous les 24 mois à partir de 24 mois (index 3,7,11)
        $this->inserer($id, "Bougie d'allumage", 'remplacer',
            [$km[3], $km[7], $km[11]], [$mois[3], $mois[7], $mois[11]]);

        // Huile de boîte de vitesses (BVM) : remplacement paliers 0 et 5, inspection sinon
        $paliersRemplacementBVM = [0, 5];
        foreach ($km as $i => $k) {
            $this->inserer($id, 'Huile de boîte de vitesses (BVM)', in_array($i, $paliersRemplacementBVM) ? 'remplacer' : 'inspecter', [$k], [$mois[$i]]);
        }

        // Permutation des pneus : paliers pairs (24, 48, 72 mois → index 3,7,11)
        $this->inserer($id, 'Permutation des pneus', 'remplacer',
            [$km[3], $km[7], $km[11]], [$mois[3], $mois[7], $mois[11]]);

        // Élément du filtre à air : alterné nettoyage/remplacement
        foreach ($km as $i => $k) {
            $this->inserer($id, 'Élément du filtre à air', $i % 2 === 0 ? 'nettoyer' : 'remplacer', [$k], [$mois[$i]]);
        }

        // Filtre du réservoir à charbon actif (canister) : nettoyage tous les 24 mois à partir de 30 mois (index 4,8,12)
        $this->inserer($id, 'Filtre du réservoir à charbon actif (canister)', 'nettoyer',
            [$km[4], $km[8], $km[12]], [$mois[4], $mois[8], $mois[12]]);

        // Filtre de climatisation : même alternance que le filtre à air
        foreach ($km as $i => $k) {
            $this->inserer($id, 'Filtre de climatisation', $i % 2 === 0 ? 'nettoyer' : 'remplacer', [$k], [$mois[$i]]);
        }

        // Liquide de refroidissement moteur / Liquide de frein : remplacement tous les 24 mois (index 3,7,11), inspection sinon
        foreach (['Liquide de refroidissement moteur', 'Liquide de frein'] as $designation) {
            foreach ($km as $i => $k) {
                $action = in_array($i, [3, 7, 11]) ? 'remplacer' : 'inspecter';
                $this->inserer($id, $designation, $action, [$k], [$mois[$i]]);
            }
        }

        $this->inserer($id, 'Courroie d\'alternateur / pompe à eau — remplacement à 100 000 km maximum', 'inspecter', [100000], [null]);
        $this->inserer($id, 'Courroie de pompe de direction assistée — remplacement à 100 000 km maximum', 'inspecter', [100000], [null]);
        $this->inserer($id, 'Huile de boîte de transfert — remplacement à 50 000 km, puis tous les 150 000 km', 'remplacer', [50000], [null]);
        $this->inserer($id, 'Huile de différentiel — remplacement à 50 000 km / 36 mois, puis tous les 100 000 km / 36 mois', 'remplacer', [50000], [36]);
    }
};
