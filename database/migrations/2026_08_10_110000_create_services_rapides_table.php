<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Remplace le catalogue statique config/services_rapides.php par une table
 * pilotable par l'admin/chef d'atelier : chaque service a désormais son
 * propre tarif ET sa propre capacité (postes simultanés), au lieu de prix
 * réglables sur un catalogue figé et d'une capacité globale partagée.
 *
 * Le "slug" reprend exactement les clés historiques du fichier de config
 * pour que les réservations/dossiers de réception déjà enregistrés (qui
 * stockent ce slug en texte libre dans `service_cle`) continuent à pointer
 * vers la bonne colonne du planning sans migration de données à part.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services_rapides', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('nom');
            $table->unsignedInteger('duree_min')->nullable();
            $table->unsignedInteger('duree_max')->nullable();
            $table->decimal('tarif', 12, 2)->default(0);
            $table->unsignedInteger('capacite_simultanee')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // Reprend le catalogue historique (config/services_rapides.php) et les
        // tarifs déjà réglés par l'admin (parametres_atelier.tarifs_service_rapide),
        // pour ne rien perdre au moment du passage à la table.
        $catalogue = [
            'controle_filtre_air'      => ['label' => 'Contrôle/nettoyage filtre à air',  'duree_min' => 10, 'duree_max' => 15],
            'controle_pression_pneus'  => ['label' => 'Contrôle pression pneus',           'duree_min' => 5,  'duree_max' => 10],
            'permutation_pneus'        => ['label' => 'Permutation pneus (avant/arrière)', 'duree_min' => 20, 'duree_max' => 30],
            'equilibrage_roues'        => ['label' => 'Équilibrage roues (4 roues)',       'duree_min' => 40, 'duree_max' => 60],
            'lavage_exterieur'         => ['label' => 'Lavage extérieur',                  'duree_min' => 15, 'duree_max' => 20],
            'lavage_interieur'         => ['label' => 'Lavage intérieur complet',          'duree_min' => 30, 'duree_max' => 45],
        ];

        $tarifsExistants = [];
        if (Schema::hasTable('parametres_atelier')) {
            $ligne = DB::table('parametres_atelier')->first();
            if ($ligne && $ligne->tarifs_service_rapide) {
                $tarifsExistants = json_decode($ligne->tarifs_service_rapide, true) ?? [];
            }
        }

        $maintenant = now();
        foreach ($catalogue as $slug => $s) {
            DB::table('services_rapides')->insert([
                'slug'                => $slug,
                'nom'                 => $s['label'],
                'duree_min'           => $s['duree_min'],
                'duree_max'           => $s['duree_max'],
                'tarif'               => $tarifsExistants[$slug] ?? 0,
                'capacite_simultanee' => null,
                'actif'               => true,
                'created_at'          => $maintenant,
                'updated_at'          => $maintenant,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('services_rapides');
    }
};
