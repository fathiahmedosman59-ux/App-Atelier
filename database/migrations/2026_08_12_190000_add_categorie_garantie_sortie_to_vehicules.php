<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicules', function (Blueprint $table) {
            // Catégorie du véhicule — sert uniquement à déterminer la limite d'âge
            // d'éligibilité garantie (pick-up : 3 ans, suv : 5 ans). Les autres
            // véhicules (null) n'ont pas de limite d'âge appliquée.
            $table->string('categorie')->nullable()->after('motorisation');

            // Marque définitivement le véhicule comme définitivement sorti de la
            // garantie (décision de l'équipe garantie) — une fois renseigné, plus
            // jamais reproposé au circuit garantie, quel que soit sous_garantie.
            $table->date('garantie_sortie_le')->nullable()->after('fin_garantie');
        });
    }

    public function down(): void
    {
        Schema::table('vehicules', function (Blueprint $table) {
            $table->dropColumn(['categorie', 'garantie_sortie_le']);
        });
    }
};
