<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Transcription du barème d'entretien constructeur GWM Wingle 7 fourni par
 * l'utilisateur en PDF (tableau unique, pas de variantes régionales).
 *
 * « Permutation des pneus » est enregistrée en 'inspecter' plutôt que
 * 'remplacer' (comme pour Tank 700 PHEV, cf. 2026_08_12_150000) : il s'agit
 * d'une rotation des pneus déjà montés, pas d'un achat de pneus neufs — un
 * 'remplacer' ajouterait automatiquement une pièce "pneus" sur le devis à
 * tort.
 *
 * Les lignes "Au maximum X km" sans lettre I/R/C/√ associée (courroies,
 * huile de boîte de transfert) sont transcrites en 'remplacer' : ce sont des
 * pièces d'usure avec une limite de remplacement, cohérent avec le
 * vocabulaire "remplacer au maximum tous les X km" utilisé ailleurs dans ce
 * même document pour des pièces équivalentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        $id = DB::table('types_moteur')->insertGetId([
            'code' => 'WINGLE7', 'marque' => 'GWM', 'modele' => 'Wingle 7',
            'libelle' => 'Wingle 7', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $mois = [6, 12, 18, 24, 30, 36, 42, 48, 54, 60, 66, 72, 78, 84, 90, 96, 102];
        $km   = [5000, 10000, 16000, 22000, 28000, 34000, 40000, 46000, 52000, 58000, 64000, 70000, 76000, 82000, 88000, 94000, 100000];

        // ── Full palier ──
        foreach (['Huile moteur', 'Rondelle du bouchon de vidange du carter d\'huile', 'Filtre à huile moteur',
                  'Filtre à carburant', 'Préfiltre à carburant'] as $d) {
            $this->tous($id, $d, 'remplacer', $km, $mois);
        }
        foreach (['Boulons et écrous importants', 'Frein à disque', 'Frein de stationnement', 'Pression et usure des pneus',
                  'Radiateur (aspect extérieur)', 'Échangeur intermédiaire - intercooler (aspect extérieur)', 'Batterie',
                  'Fuites sur le véhicule (huile / eau / électricité / air)', 'Éclairage du véhicule'] as $d) {
            $this->tous($id, $d, 'inspecter', $km, $mois);
        }
        foreach (['Serrure de porte', 'Câble de la trappe à carburant'] as $d) {
            $this->tous($id, $d, 'lubrifier', $km, $mois);
        }

        // ── Sous-ensemble de paliers ──
        $this->auxIndices($id, 'Galet tendeur, galet de renvoi et poulies', 'inspecter', $km, $mois, [3, 6, 9, 12, 15]);
        $this->auxIndices($id, 'Vanne EGR', 'nettoyer', $km, $mois, [2, 5, 8, 11, 14]);
        $this->auxIndices($id, 'Intérieur et tuyaux de raccordement de l\'échangeur intermédiaire (intercooler)', 'nettoyer', $km, $mois, [2, 5, 8, 11, 14]);
        $this->auxIndices($id, 'Huile de transmission', 'remplacer', $km, $mois, [0, 6, 12]);

        // Permutation des pneus / Parallélisme des 4 roues / Rotule et soufflet : vérifiés 1 palier sur 2.
        $this->auxIndices($id, 'Permutation des pneus', 'inspecter', $km, $mois, [1, 3, 5, 7, 9, 11, 13, 15]);
        $this->auxIndices($id, 'Parallélisme des quatre roues', 'inspecter', $km, $mois, [1, 3, 5, 7, 9, 11, 13, 15]);
        $this->auxIndices($id, 'Rotule et soufflet de protection', 'inspecter', $km, $mois, [1, 3, 5, 7, 9, 11, 13, 15]);

        // Nettoyage/remplacement alterné à chaque palier.
        $this->alterne($id, 'Élément de filtre à air', 'nettoyer', 'remplacer', $km, $mois);
        $this->alterne($id, 'Filtre de climatisation', 'nettoyer', 'remplacer', $km, $mois);

        // Liquides : inspection à chaque palier, remplacement 1 palier sur 4.
        $indicesRemplacement = [3, 7, 11, 15];
        $indicesInspection   = array_values(array_diff(range(0, 16), $indicesRemplacement));
        foreach (['Liquide de refroidissement moteur', 'Liquide de frein', 'Liquide de direction assistée'] as $d) {
            $this->auxIndices($id, $d, 'inspecter', $km, $mois, $indicesInspection);
            $this->auxIndices($id, $d, 'remplacer', $km, $mois, $indicesRemplacement);
        }

        // ── Notes à seuil unique ──
        $this->inserer($id, 'Courroie de distribution', 'remplacer', [80000], [null]);
        $this->inserer($id, 'Courroie d\'alternateur / pompe à eau', 'remplacer', [100000], [null]);
        $this->inserer($id, 'Courroie de pompe de direction assistée', 'remplacer', [100000], [null]);
        $this->inserer($id, 'Huile de boîte de transfert', 'remplacer', [80000], [null]);
        $this->inserer($id, 'Huile de différentiel (1ère vidange)', 'remplacer', [50000], [36]);
        $this->inserer($id, 'Huile de différentiel', 'remplacer', [100000], [36]);
    }

    public function down(): void
    {
        $id = DB::table('types_moteur')->where('code', 'WINGLE7')->value('id');
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

    /** Insère une tâche en alternant deux actions à chaque palier (ex: C,R,C,R...). */
    private function alterne(int $typeMoteurId, string $designation, string $action1, string $action2, array $km, array $mois): void
    {
        foreach ($km as $i => $k) {
            $this->inserer($typeMoteurId, $designation, $i % 2 === 0 ? $action1 : $action2, [$k], [$mois[$i]]);
        }
    }
};
