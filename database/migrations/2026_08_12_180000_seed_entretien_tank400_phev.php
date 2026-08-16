<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Transcription du barème d'entretien constructeur GWM Tank 400 PHEV
 * (document "Tank400-Marche_autorise-FR.pdf") fourni par l'utilisateur —
 * tableau unique, pas de variantes régionales dans ce document.
 *
 * Mêmes choix de transcription que pour Tank 700 PHEV (2026_08_12_150000) :
 * - « Parallélisme des quatre roues » est omis : le document ne donne pas de
 *   kilométrage fixe (contrôle seulement après choc/ornière/crevaison).
 * - Les items combinant "vérifier à X" puis "remplacer à Y" (courroie
 *   striée, liquides) sont transcrits en 2 lignes distinctes.
 * - « État de la carrosserie » (1ère fois à 48 mois) utilise un kilométrage
 *   dérivé du ratio constant km/mois de cette grille (2500/3 ≈ 833,33
 *   km/mois, identique à la grille Tank 700).
 * Différence notable avec Tank 700 : ici « Permutation des pneus » est déjà
 * en 'I' (inspection) dans le document, pas d'alternance R — transcrite
 * telle quelle, aucun arbitrage nécessaire. « Huile de transmission » et
 * « Filtre de pression » ont un intervalle différent (72 mois/150 000 km).
 */
return new class extends Migration
{
    public function up(): void
    {
        $id = DB::table('types_moteur')->insertGetId([
            'code' => 'TANK400-PHEV', 'marque' => 'Tank', 'modele' => 'Tank 400 PHEV',
            'libelle' => 'Tank 400 PHEV', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $mois = [12, 24, 36, 48, 60, 72, 84, 96, 108, 120, 132, 144];
        $km   = [10000, 20000, 30000, 40000, 50000, 60000, 70000, 80000, 90000, 100000, 110000, 120000];

        // ── Full palier ──
        foreach (['Huile moteur', 'Rondelle du bouchon de vidange du carter d\'huile', 'Filtre à huile moteur'] as $d) {
            $this->tous($id, $d, 'remplacer', $km, $mois);
        }
        foreach ([
            'Boulons et écrous importants', 'Frein à disque', 'Pression et usure des pneus', 'Permutation des pneus',
            'Rotule et soufflet de protection', 'Niveau du réservoir de trop-plein haute température',
            'Niveau du réservoir de trop-plein basse température', 'Radiateur (aspect extérieur)', 'Batterie',
            'Tuyau d\'évacuation du toit ouvrant', 'Poignée de porte',
            'Boîtier de la batterie de traction (bloc-batterie B)',
            'Connecteurs haute/basse tension de la batterie de traction (bloc-batterie B)',
            'Paramètres d\'état de la batterie de traction (SOC/température/tension/isolement)',
            'Système de faisceau haute tension', 'Prise de charge', 'Tuyau d\'évacuation de la prise de charge',
            'Système du groupe motopropulseur électrique', 'Système d\'alimentation haute tension',
        ] as $d) {
            $this->tous($id, $d, 'inspecter', $km, $mois);
        }

        // Filtre de climatisation : vérifié/nettoyé à chaque révision.
        $this->tous($id, 'Filtre de climatisation (remplacer si nécessaire)', 'nettoyer', $km, $mois);

        // Toit ouvrant : lubrifié aux 2 premiers paliers, inspecté ensuite.
        $this->auxIndices($id, 'Toit ouvrant', 'lubrifier', $km, $mois, [0, 1]);
        $this->auxIndices($id, 'Toit ouvrant', 'inspecter', $km, $mois, range(2, 11));

        // ── Notes à seuil unique / paire vérifier+remplacer ──
        $this->inserer($id, 'Papillon des gaz', 'nettoyer', [20000], [null]);
        $this->inserer($id, 'Bougies d\'allumage', 'remplacer', [70000], [null]);

        $this->inserer($id, 'Courroie striée (remplacer si nécessaire)', 'inspecter', [20000], [24]);
        $this->inserer($id, 'Courroie striée', 'remplacer', [150000], [null]);

        $this->inserer($id, 'Huile de boîte de transfert (usage sévère/tout-terrain : tous les 50 000 km)', 'remplacer', [100000], [null]);

        $this->inserer($id, 'Huile de transmission', 'remplacer', [150000], [72]);
        $this->inserer($id, 'Filtre de pression', 'remplacer', [150000], [72]);

        $this->inserer($id, 'Filtre du réservoir à charbon actif (remplacer si nécessaire)', 'nettoyer', [40000], [null]);

        $this->inserer($id, 'Huile du réducteur principal avant (1ère vidange)', 'remplacer', [50000], [48]);
        $this->inserer($id, 'Huile du réducteur principal avant', 'remplacer', [100000], [48]);
        $this->inserer($id, 'Huile du réducteur principal arrière (1ère vidange)', 'remplacer', [50000], [48]);
        $this->inserer($id, 'Huile du réducteur principal arrière', 'remplacer', [100000], [48]);

        $this->inserer($id, 'Liquide de refroidissement moteur (vérifier)', 'inspecter', [20000], [12]);
        $this->inserer($id, 'Liquide de refroidissement moteur', 'remplacer', [80000], [48]);

        $this->inserer($id, 'Liquide de refroidissement (batterie de traction / moteur électrique) (vérifier)', 'inspecter', [20000], [12]);
        $this->inserer($id, 'Liquide de refroidissement (batterie de traction / moteur électrique)', 'remplacer', [100000], [60]);

        $this->inserer($id, 'Liquide de frein (vérifier)', 'inspecter', [20000], [12]);
        $this->inserer($id, 'Liquide de frein', 'remplacer', [80000], [36]);

        $this->inserer($id, 'Élément de filtre à air (nettoyer)', 'nettoyer', [15000], [12]);
        $this->inserer($id, 'Élément de filtre à air', 'remplacer', [30000], [24]);

        $this->inserer($id, 'État de la carrosserie (1ère fois à 48 mois ; puis tous les 24 mois)', 'inspecter', [40000], [48]);
    }

    public function down(): void
    {
        $id = DB::table('types_moteur')->where('code', 'TANK400-PHEV')->value('id');
        if ($id) {
            DB::table('entretien_taches')->where('type_moteur_id', $id)->delete();
            DB::table('types_moteur')->where('id', $id)->delete();
        }
    }

    /** Insère une tâche pour une liste de paliers km donnés. */
    private function inserer(int $typeMoteurId, string $designation, string $action, array $kmSeuils, ?array $moisSeuils = null): void
    {
        $rows = [];
        foreach ($kmSeuils as $i => $km) {
            $rows[] = [
                'type_moteur_id' => $typeMoteurId, 'designation' => $designation, 'action' => $action,
                'km_seuil' => $km, 'mois_seuil' => $moisSeuils[$i] ?? null, 'ordre' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ];
        }
        DB::table('entretien_taches')->insert($rows);
    }

    /** Insère une tâche à chaque palier de la grille. */
    private function tous(int $typeMoteurId, string $designation, string $action, array $km, array $mois): void
    {
        $this->inserer($typeMoteurId, $designation, $action, $km, $mois);
    }

    /** Insère une tâche uniquement aux indices donnés de la grille (0-based). */
    private function auxIndices(int $typeMoteurId, string $designation, string $action, array $km, array $mois, array $indices): void
    {
        $this->inserer(
            $typeMoteurId, $designation, $action,
            array_map(fn ($i) => $km[$i], $indices),
            array_map(fn ($i) => $mois[$i], $indices)
        );
    }
};
