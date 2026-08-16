<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Transcription des barèmes d'entretien constructeur (fournis par l'utilisateur
 * en PDF) pour 7 modèles supplémentaires : H6 HEV, Dargo, H9 Diesel, Jolion HEV,
 * Jolion Pro HEV, Tank 300 HEV, Tank 500 HEV.
 *
 * MÊME AVERTISSEMENT que pour GW4D20/GW4C20 (voir migration
 * 2026_08_04_151300_seed_entretien_gw4d20_gw4c20.php) : les lignes "R/I à
 * chaque palier" et les notes informatives (une seule règle textuelle, pas de
 * palier précis) sont transcrites avec confiance. Les lignes à motif
 * périodique/alterné (nettoyages, bougies, filtres air/climatisation...) sont
 * une transcription au meilleur effort — le volume est ici 7 fois plus
 * important que pour les 2 premiers moteurs, la vérification avec les
 * documents constructeur d'origine est donc d'autant plus recommandée avant
 * usage réel.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->seedH6HEV();
        $this->seedDargo();
        $this->seedH9Diesel();
        $this->seedJolionHEV();
        $this->seedJolionProHEV();
        $this->seedTank300HEV();
        $this->seedTank500HEV();
    }

    public function down(): void
    {
        $codes = ['H6-HEV', 'DARGO', 'H9-DIESEL', 'JOLION-HEV', 'JOLION-PRO-HEV', 'TANK300-HEV', 'TANK500-HEV'];
        $ids = DB::table('types_moteur')->whereIn('code', $codes)->pluck('id');
        DB::table('entretien_taches')->whereIn('type_moteur_id', $ids)->delete();
        DB::table('types_moteur')->whereIn('code', $codes)->delete();
    }

    private function creerMoteur(string $code, string $marque, string $modele): int
    {
        return DB::table('types_moteur')->insertGetId([
            'code' => $code, 'marque' => $marque, 'modele' => $modele, 'libelle' => $modele,
            'created_at' => now(), 'updated_at' => now(),
        ]);
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

    /** Insère une tâche à chaque palier de la grille (tous les mois/km fournis). */
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

    // ────────────────────────────────────────────────────────────────
    // H6 HEV (Haval) — B01-4B15D HEV
    // ────────────────────────────────────────────────────────────────
    private function seedH6HEV(): void
    {
        $id = $this->creerMoteur('H6-HEV', 'Haval', 'H6 HEV');
        $mois = [6,12,18,24,30,36,42,48,54,60,66,72,78,84,90,96,102,108,114,120,126];
        $km   = [5000,12500,20000,27500,35000,42500,50000,57500,65000,72500,80000,87500,95000,103000,110000,118000,125000,133000,140000,148000,155000];

        foreach (['Engine oil', "Washer-Drain plug of oil pan", 'Engine oil filter'] as $d) {
            $this->tous($id, $d, 'remplacer', $km, $mois);
        }
        foreach (['Important bolts and nuts', 'Disc brake', 'Tyre pressure and wear', 'Four-wheel alignment',
                  'Ball joint and dust cover', 'Radiator (aspect visuel)', 'Intercooler (aspect visuel)',
                  'Liquide de direction assistée', 'Batterie', 'Fuites (huile/eau/électricité/air)', 'Éclairage'] as $d) {
            $this->tous($id, $d, 'inspecter', $km, $mois);
        }
        $this->tous($id, 'Permutation des pneus', 'remplacer', $km, $mois);

        $this->auxIndices($id, 'Throttle valve (papillon des gaz)', 'nettoyer', $km, $mois, [1,3,5,7,9,11,13,15,17,19]);
        $this->auxIndices($id, "Internal of intercooler and pipeline", 'nettoyer', $km, $mois, [1,3,5,7,9,11,13,15,17,19]);
        $this->auxIndices($id, "Bougie d'allumage", 'remplacer', $km, $mois, [5,11,17]);

        $this->alterne($id, 'Élément du filtre à air', 'nettoyer', 'remplacer', $km, $mois);
        $this->alterne($id, 'Filtre de climatisation', 'nettoyer', 'remplacer', $km, $mois);

        // Liquide de refroidissement / frein : pas de rythme précis identifié dans le document, inspection à chaque palier
        foreach (['Liquide de refroidissement moteur', 'Liquide de frein'] as $d) {
            $this->tous($id, $d, 'inspecter', $km, $mois);
        }

        $this->inserer($id, 'Belt of generator and water pump — ne pas dépasser 100 000 km', 'inspecter', [100000], [null]);
        $this->inserer($id, 'Direct hydraulic transmission oil — remplacement à 60 000 km / 36 mois maximum', 'remplacer', [60000], [36]);

        // ── Section EV/HEV (page 2) ──
        foreach (['Bolts between power battery pack and chassis', 'Power battery pack housing',
                  'Power battery pack high/low voltage connector', 'Power battery pack SOH parameter',
                  'Leakage (oil/water/electricity/air) — bloc batterie', 'Lighting', 'Vehicle body condition'] as $d) {
            $this->tous($id, $d, 'inspecter', $km, $mois);
        }
        $this->auxIndices($id, 'Sunroof', 'lubrifier', $km, $mois, [0]);
        $this->auxIndices($id, 'Sunroof', 'inspecter', $km, $mois, range(1, 20));
        $this->inserer($id, 'Motor battery coolant — remplacement à 80 000 km / 48 mois maximum', 'remplacer', [80000], [48]);
    }

    // ────────────────────────────────────────────────────────────────
    // Dargo (Haval)
    // ────────────────────────────────────────────────────────────────
    private function seedDargo(): void
    {
        $id = $this->creerMoteur('DARGO', 'Haval', 'Dargo');
        $mois = [6,12,18,24,30,36,42,48,54,60,66,72,78,84];
        $km   = [5000,12500,20000,27500,35000,42500,50000,57500,65000,72500,80000,87500,95000,102500];

        foreach (['Huile moteur', "Joint / bouchon de vidange du carter d'huile", 'Filtre à huile moteur'] as $d) {
            $this->tous($id, $d, 'remplacer', $km, $mois);
        }
        foreach (['Frein à disque', 'Boulons et écrous importants', 'Pression et usure des pneus',
                  'Radiator (aspect visuel)', 'Intercooler (aspect visuel)', 'Batterie', 'Fuites', 'Éclairage'] as $d) {
            $this->tous($id, $d, 'inspecter', $km, $mois);
        }

        $this->auxIndices($id, 'Papillon des gaz', 'nettoyer', $km, $mois, [2,5,8,11]);
        $this->auxIndices($id, "Intérieur de l'échangeur et tuyauterie", 'nettoyer', $km, $mois, [2,5,8,11]);
        $this->auxIndices($id, "Bougie d'allumage", 'remplacer', $km, $mois, [5,11]);

        $this->auxIndices($id, 'Permutation des pneus', 'remplacer', $km, $mois, [1,3,5,7,9,11,13]);
        $this->auxIndices($id, 'Parallélisme des 4 roues', 'inspecter', $km, $mois, [1,3,5,7,9,11,13]);
        $this->auxIndices($id, 'Rotule et soufflet de protection', 'inspecter', $km, $mois, [1,3,5,7,9,11,13]);

        $this->alterne($id, 'Élément du filtre à air', 'nettoyer', 'remplacer', $km, $mois);
        $this->alterne($id, 'Filtre de climatisation', 'nettoyer', 'remplacer', $km, $mois);
        $this->auxIndices($id, 'Filtre du réservoir à charbon actif (canister)', 'nettoyer', $km, $mois, [2]);
        $this->auxIndices($id, 'Filtre du réservoir à charbon actif (canister)', 'remplacer', $km, $mois, [5]);

        $this->auxIndices($id, 'Sunroof', 'lubrifier', $km, $mois, [0]);
        $this->auxIndices($id, 'Sunroof', 'inspecter', $km, $mois, range(1, 13));
        $this->auxIndices($id, 'Sunroof drain pipe', 'inspecter', $km, $mois, range(1, 13));

        $this->inserer($id, 'Belt of generator and water pump — ne pas dépasser 100 000 km', 'inspecter', [100000], [null]);
        $this->inserer($id, 'Transmission oil (AT) — remplacement à 80 000 km / 4 ans maximum (avec filtre pression)', 'remplacer', [80000], [48]);
        $this->inserer($id, "Huile de boîte de transfert — 1er remplacement à 50 000 km / 3 ans, puis tous les 100 000 km / 3 ans", 'remplacer', [50000], [36]);
        $this->inserer($id, "Huile de pont arrière — 1er remplacement à 50 000 km / 3 ans, puis tous les 100 000 km / 3 ans", 'remplacer', [50000], [36]);
        $this->inserer($id, 'Torque manager lubricants — ne pas dépasser 60 000 km', 'lubrifier', [60000], [null]);
        $this->inserer($id, 'Liquide de refroidissement moteur — ne pas dépasser 2 ans / 40 000 km', 'remplacer', [40000], [24]);
        $this->inserer($id, 'Liquide de frein — ne pas dépasser 2 ans / 40 000 km', 'remplacer', [40000], [24]);
    }

    // ────────────────────────────────────────────────────────────────
    // H9 Diesel (Haval)
    // ────────────────────────────────────────────────────────────────
    private function seedH9Diesel(): void
    {
        $id = $this->creerMoteur('H9-DIESEL', 'Haval', 'H9 Diesel');
        $mois = [6,12,18,24,30,36,42,48,54,60,66,72,78,84,90,96,102,108,114];
        $km   = [5000,10000,15000,20000,25000,30000,35000,40000,45000,50000,55000,60000,65000,70000,75000,80000,85000,90000,95000];

        foreach (['Huile moteur', "Joint / bouchon de vidange du carter d'huile", 'Filtre à huile moteur'] as $d) {
            $this->tous($id, $d, 'remplacer', $km, $mois);
        }
        foreach (['Boulons et écrous importants', 'Frein à disque', 'Pression et usure des pneus',
                  'Ball joint and dust cover', 'Radiator (aspect visuel)', 'Intercooler (aspect visuel)',
                  'Batterie', 'High temperature overflow tank level'] as $d) {
            $this->tous($id, $d, 'inspecter', $km, $mois);
        }
        $this->tous($id, 'Air filter element', 'remplacer', $km, $mois);

        // High/low pressure EGR valve and cooler : nettoyage tous les 2 paliers (indices impairs 1,3,5,7,9,11,13,15,17)
        $this->auxIndices($id, 'High pressure EGR valve and cooler', 'nettoyer', $km, $mois, [1,3,5,7,9,11,13,15,17]);
        $this->auxIndices($id, 'Low pressure EGR valve and cooler', 'nettoyer', $km, $mois, [1,3,5,7,9,11,13,15,17]);
        $this->auxIndices($id, 'Tension wheel / transition wheel / belt wheel', 'inspecter', $km, $mois, [1,5,9,13,17]);

        $this->auxIndices($id, 'Wheel transposition (permutation pneus)', 'remplacer', $km, $mois, [2,5,8,11,14,17]);
        $this->auxIndices($id, 'Fuel filter (filtre à carburant)', 'remplacer', $km, $mois, [0,2,4,6,8,10,12,14,16,18]);

        $this->inserer($id, 'Timing belt (courroie de distribution) — inspection ≤20 000 km, remplacement ≤80 000 km', 'inspecter', [80000], [null]);
        $this->inserer($id, 'Generator/silicone fan belt — inspection ≤2 ans/20 000 km', 'inspecter', [20000], [24]);
        $this->inserer($id, 'Transmission oil — remplacement ≤48 mois/80 000 km', 'remplacer', [80000], [48]);
        $this->inserer($id, 'Press filter — remplacement ≤48 mois/80 000 km', 'remplacer', [80000], [48]);
        $this->inserer($id, 'Transfer case oil — remplacement ≤100 000 km (lourd/tout-terrain : ≤50 000 km)', 'remplacer', [100000], [null]);
        $this->inserer($id, 'Front reducer oil — 1er remplacement ≤48 mois/50 000 km, puis ≤48 mois/100 000 km', 'remplacer', [50000], [48]);
        $this->inserer($id, 'Rear reducer oil — 1er remplacement ≤48 mois/50 000 km, puis ≤48 mois/100 000 km', 'remplacer', [50000], [48]);
        $this->inserer($id, 'Engine coolant — inspection ≤1 an/20 000 km, remplacement ≤4 ans/80 000 km', 'remplacer', [80000], [48]);
        $this->inserer($id, 'Brake fluid — inspection ≤1 an/20 000 km, remplacement ≤3 ans/80 000 km', 'remplacer', [80000], [36]);

        $this->auxIndices($id, 'Sunroof', 'lubrifier', $km, $mois, [0]);
        $this->auxIndices($id, 'Sunroof', 'inspecter', $km, $mois, range(1, 18));
        foreach (['Sunroof drainage pipe', 'Leakage (oil/water/electricity/air)', 'Lighting', 'Door handle'] as $d) {
            $this->tous($id, $d, 'inspecter', $km, $mois);
        }
        $this->inserer($id, 'Vehicle body condition — inspection ≤48 mois puis tous les 24 mois', 'inspecter', [end($km)], [24]);
    }

    // ────────────────────────────────────────────────────────────────
    // Jolion HEV (Haval)
    // ────────────────────────────────────────────────────────────────
    private function seedJolionHEV(): void
    {
        $id = $this->creerMoteur('JOLION-HEV', 'Haval', 'Jolion HEV');
        $mois = [6,12,18,24,30,36,42,48,54,60,66,72,78,84];
        $km   = [5000,12500,20000,27500,35000,42500,50000,57500,65000,72500,80000,87500,95000,102500];

        foreach (['Huile moteur', "Joint / bouchon de vidange du carter d'huile", 'Filtre à huile moteur'] as $d) {
            $this->tous($id, $d, 'remplacer', $km, $mois);
        }
        foreach (['Frein à disque', 'Boulons et écrous importants', 'Pression et usure des pneus',
                  'Radiator (aspect visuel)', 'Battery and connection', 'Leakage', 'Lights',
                  'Power battery and lower body connection bolt torque', 'Power battery housing',
                  'High and low voltage connectors of Power battery pack', 'SOH parameters of power battery'] as $d) {
            $this->tous($id, $d, 'inspecter', $km, $mois);
        }

        $this->auxIndices($id, 'Papillon des gaz', 'nettoyer', $km, $mois, [2,5,8,11]);
        $this->auxIndices($id, "Bougie d'allumage", 'remplacer', $km, $mois, [2,5,8,11]);
        $this->inserer($id, 'DHT oil — remplacement à 3 ans / 60 000 km maximum', 'remplacer', [60000], [36]);

        $this->auxIndices($id, 'Permutation des pneus', 'remplacer', $km, $mois, [1,3,5,7,9,11,13]);
        $this->auxIndices($id, 'Ball joint and dust cover', 'inspecter', $km, $mois, [1,3,5,7,9,11,13]);

        $this->alterne($id, 'Élément du filtre à air', 'nettoyer', 'remplacer', $km, $mois);
        $this->alterne($id, 'Filtre de climatisation', 'nettoyer', 'remplacer', $km, $mois);
        $this->auxIndices($id, 'Filtre du réservoir à charbon actif (canister)', 'nettoyer', $km, $mois, [2]);
        $this->auxIndices($id, 'Filtre du réservoir à charbon actif (canister)', 'remplacer', $km, $mois, [5]);

        $this->auxIndices($id, 'Sunroof', 'lubrifier', $km, $mois, [0]);
        $this->auxIndices($id, 'Sunroof', 'inspecter', $km, $mois, range(1, 13));
        $this->auxIndices($id, 'Sunroof drain pipe', 'inspecter', $km, $mois, range(1, 12));

        $this->inserer($id, 'Engine coolant — ne pas dépasser 2 ans / 40 000 km', 'remplacer', [40000], [24]);
        $this->inserer($id, 'Brake fluid — ne pas dépasser 2 ans / 40 000 km', 'remplacer', [40000], [24]);
        $this->inserer($id, 'Drive motor coolant — remplacement à 4 ans / 80 000 km maximum', 'remplacer', [80000], [48]);
    }

    // ────────────────────────────────────────────────────────────────
    // Jolion Pro HEV (Haval)
    // ────────────────────────────────────────────────────────────────
    private function seedJolionProHEV(): void
    {
        $id = $this->creerMoteur('JOLION-PRO-HEV', 'Haval', 'Jolion Pro HEV');
        $mois = [6,12,18,24,30,36,42,48,54,60,66,72];
        $km   = [5000,12500,20000,27500,35000,42500,50000,57500,65000,72500,80000,87500];

        foreach (['Huile moteur', "Joint / bouchon de vidange du carter d'huile", 'Filtre à huile moteur'] as $d) {
            $this->tous($id, $d, 'remplacer', $km, $mois);
        }
        foreach (['Boulons et écrous importants', 'Frein à disque', 'Pression et usure des pneus',
                  'Ball joint and dust cover', 'High temperature overflow tank level', 'Low temperature overflow tank level',
                  'Radiator (aspect visuel)', 'Batterie', 'Leakage', 'Lighting', 'High-voltage wiring harness', 'Door handle'] as $d) {
            $this->tous($id, $d, 'inspecter', $km, $mois);
        }

        $this->auxIndices($id, 'Papillon des gaz', 'nettoyer', $km, $mois, range(0, 11));
        $this->auxIndices($id, "Bougie d'allumage", 'remplacer', $km, $mois, range(0, 11));
        $this->auxIndices($id, 'Permutation des pneus', 'remplacer', $km, $mois, [1,3,5,7,9,11]);

        $this->alterne($id, 'Élément du filtre à air', 'nettoyer', 'remplacer', $km, $mois);
        $this->tous($id, 'Filtre de climatisation', 'inspecter', $km, $mois);
        $this->auxIndices($id, 'Sunroof', 'lubrifier', $km, $mois, [0]);
        $this->auxIndices($id, 'Sunroof', 'inspecter', $km, $mois, range(1, 11));
        $this->tous($id, 'Sunroof drain pipe', 'inspecter', $km, $mois);
        $this->tous($id, 'Full car four leaks (oil/water/electricity/air)', 'inspecter', $km, $mois);
        $this->auxIndices($id, 'Appearance of power battery pack', 'inspecter', $km, $mois, [1,3,5,7,9,11]);
        $this->tous($id, 'Electric drive system assembly', 'inspecter', $km, $mois);

        $this->inserer($id, 'Transmission oil — remplacement à 48 mois / 80 000 km maximum', 'remplacer', [80000], [48]);
        $this->inserer($id, 'Coolant (moteur) — inspection ≤12 mois/20 000 km, remplacement ≤36 mois/80 000 km', 'remplacer', [80000], [36]);
        $this->inserer($id, 'Coolant (batterie/moteur électrique) — inspection ≤12 mois/20 000 km, remplacement ≤60 mois/100 000 km', 'remplacer', [100000], [60]);
        $this->inserer($id, 'Brake fluid — inspection ≤12 mois/20 000 km, remplacement ≤36 mois/80 000 km', 'remplacer', [80000], [36]);
        $this->inserer($id, 'Carbon canister filter — inspection/nettoyage ≤40 000 km', 'nettoyer', [40000], [null]);
        $this->inserer($id, 'Vehicle body condition — inspection ≤48 mois puis tous les 24 mois', 'inspecter', [end($km)], [24]);
    }

    // ────────────────────────────────────────────────────────────────
    // Tank 300 HEV
    // ────────────────────────────────────────────────────────────────
    private function seedTank300HEV(): void
    {
        $id = $this->creerMoteur('TANK300-HEV', 'Tank', 'Tank 300 HEV');
        $mois = [12,24,36,48,60,72,84,96,108,120,132,144,156,168,180];
        $km   = [10000,20000,30000,40000,50000,60000,70000,80000,90000,100000,110000,120000,130000,140000,150000];

        foreach (['Huile moteur', "Joint / bouchon de vidange du carter d'huile", 'Filtre à huile moteur'] as $d) {
            $this->tous($id, $d, 'remplacer', $km, $mois);
        }
        foreach (['Boulons et écrous importants', 'Frein à disque', 'Pression et usure des pneus', 'Four-wheel alignment',
                  'Radiator (aspect visuel)', 'Intercooler (aspect visuel)', 'Power battary pack', 'Power battary pack high/low voltage connector',
                  'Power battary pack SOH paremeter', 'Bots between power battary pack and chassis', 'Battary and connection',
                  'Leakage (oil/water/electricity/air)', 'Sunroof drainage pipe', 'Lighting', 'Parking brake operation'] as $d) {
            $this->tous($id, $d, 'inspecter', $km, $mois);
        }
        $this->tous($id, 'Wheel transposition (permutation pneus)', 'remplacer', $km, $mois);

        $this->auxIndices($id, 'Interior and connection pipes of intercooler', 'inspecter', $km, $mois, [1,3,5,7,9,11,13]);
        $this->auxIndices($id, "Bougie d'allumage", 'remplacer', $km, $mois, [2,5,8,11]);
        $this->auxIndices($id, 'Ball joint and dust cover', 'inspecter', $km, $mois, [1,3,5,7,9,11,13]);
        $this->alterne($id, 'Air filter element', 'inspecter', 'remplacer', $km, $mois);
        $this->auxIndices($id, 'Air conditioner element', 'remplacer', $km, $mois, [1,3,5,7,9,11,13]);
        $this->auxIndices($id, 'Carbon canister filter', 'nettoyer', $km, $mois, [1,3,5]);

        $this->inserer($id, 'Fuel filter — 1er remplacement à 12 500 km/12 mois, puis tous les 15 000 km/12 mois', 'remplacer', [12500], [12]);
        $this->inserer($id, 'Generator/water pump belt — ne pas dépasser 100 000 km', 'inspecter', [100000], [null]);
        $this->inserer($id, 'Transfer case oil — remplacement tous les 50 000 km', 'remplacer', [50000], [null]);
        $this->inserer($id, 'Differential gear oil — 1er remplacement à 50 000 km/36 mois, puis tous les 100 000 km/36 mois', 'remplacer', [50000], [36]);
        $this->inserer($id, '9HAT Oil — remplacement tous les 60 000 km/36 mois (avec filtre pression)', 'remplacer', [60000], [36]);
        $this->inserer($id, 'Engine coolant — remplacement tous les 60 000 km/48 mois maximum', 'remplacer', [60000], [48]);
        $this->inserer($id, "Brake fluid — 1er remplacement à 45 000 km/36 mois, puis tous les 450 000 km/24 mois", 'remplacer', [45000], [36]);
        $this->inserer($id, 'Power battery pack coolant — remplacement tous les 60 000 km/48 mois', 'remplacer', [60000], [48]);
        $this->inserer($id, 'Vehicle body condition — inspection tous les 24 mois', 'inspecter', [40000], [24]);
    }

    // ────────────────────────────────────────────────────────────────
    // Tank 500 HEV
    // ────────────────────────────────────────────────────────────────
    private function seedTank500HEV(): void
    {
        $id = $this->creerMoteur('TANK500-HEV', 'Tank', 'Tank 500 HEV');
        $mois = [12,24,36,48,60,72,84,96,108,120,132,144,156,168,180];
        $km   = [10000,20000,30000,40000,50000,60000,70000,80000,90000,100000,110000,120000,130000,140000,150000];

        foreach (['Huile moteur', "Joint / bouchon de vidange du carter d'huile", 'Filtre à huile moteur'] as $d) {
            $this->tous($id, $d, 'remplacer', $km, $mois);
        }
        foreach (['Boulons et écrous importants', 'Frein à disque', 'Pression et usure des pneus', 'Four-wheel alignment',
                  'Radiator (aspect visuel)', 'Intercooler (aspect visuel)', 'Power battary pack', 'Power battary pack high/low voltage connector',
                  'Power battary pack SOH paremeter', 'Bots between power battary pack and chassis', 'Battary and connection',
                  'Leakage (oil/water/electricity/air)', 'Sunroof drainage pipe', 'Lighting'] as $d) {
            $this->tous($id, $d, 'inspecter', $km, $mois);
        }
        $this->tous($id, 'Wheel transposition (permutation pneus)', 'remplacer', $km, $mois);

        $this->auxIndices($id, 'Interior and connection pipes of intercooler', 'inspecter', $km, $mois, [1,3,5,7,9,11,13]);
        $this->auxIndices($id, "Bougie d'allumage", 'remplacer', $km, $mois, [2,5,8,11]);
        $this->auxIndices($id, 'Ball joint and dust cover', 'inspecter', $km, $mois, [1,3,5,7,9,11,13]);
        $this->alterne($id, 'Air filter element', 'inspecter', 'remplacer', $km, $mois);
        $this->auxIndices($id, 'Air conditioner element', 'remplacer', $km, $mois, [1,3,5,7,9,11,13]);
        $this->auxIndices($id, 'Carbon canister filter', 'nettoyer', $km, $mois, [1,3,5]);

        $this->inserer($id, 'Fuel filter — 1er remplacement à 12 500 km/12 mois, puis tous les 15 000 km/12 mois', 'remplacer', [12500], [12]);
        $this->inserer($id, 'Transfer case oil — remplacement tous les 50 000 km', 'remplacer', [50000], [null]);
        $this->inserer($id, 'Differential gear oil — 1er remplacement à 50 000 km/36 mois, puis tous les 100 000 km/36 mois', 'remplacer', [50000], [36]);
        $this->inserer($id, '9HAT Oil — remplacement tous les 60 000 km/36 mois (avec filtre pression)', 'remplacer', [60000], [36]);
        $this->inserer($id, 'Engine coolant — remplacement tous les 60 000 km/48 mois maximum', 'remplacer', [60000], [48]);
        $this->inserer($id, "Brake fluid — 1er remplacement à 45 000 km/36 mois, puis tous les 450 000 km/24 mois", 'remplacer', [45000], [36]);
        $this->inserer($id, 'Power battery pack / electric drive system coolant — remplacement tous les 80 000 km/48 mois maximum', 'remplacer', [80000], [48]);
        $this->inserer($id, 'Vehicle body condition — inspection tous les 24 mois', 'inspecter', [40000], [24]);
    }
};
