<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter 'lavage' à l'enum statut
        DB::statement("ALTER TABLE ordres_reparations MODIFY COLUMN statut ENUM('ouvert','diagnostic','devis_envoye','devis_accepte','en_cours','controle_qualite','lavage','pret','facture','livre','annule') NOT NULL DEFAULT 'ouvert'");

        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->timestamp('heure_debut_travaux')->nullable()->after('date_sortie_reelle');
            $table->timestamp('heure_fin_travaux')->nullable()->after('heure_debut_travaux');
            $table->unsignedSmallInteger('duree_estimee')->nullable()->after('heure_fin_travaux'); // en minutes
        });
    }

    public function down(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->dropColumn(['heure_debut_travaux', 'heure_fin_travaux', 'duree_estimee']);
        });
        DB::statement("ALTER TABLE ordres_reparations MODIFY COLUMN statut ENUM('ouvert','diagnostic','devis_envoye','devis_accepte','en_cours','controle_qualite','pret','facture','livre','annule') NOT NULL DEFAULT 'ouvert'");
    }
};
