<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parametres_atelier', function (Blueprint $table) {
            // Prix fixes de main d'œuvre Service Rapide, réglés par le chef d'atelier :
            // { "controle_filtre_air": 1500, ..., "entretien_periodique": 3000 }
            $table->json('tarifs_service_rapide')->nullable()->after('capacite_service_rapide_simultanee');
        });

        Schema::table('dossiers_reception', function (Blueprint $table) {
            // Clé du catalogue config/services_rapides.php si le service "Autre" choisi en
            // correspond à une entrée exacte (fiable pour la tarification, contrairement au
            // texte libre de motif_entree qui peut être modifié par le réceptionniste).
            $table->string('service_cle')->nullable()->after('canal_service');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->string('service_cle')->nullable()->after('canal_service');
        });
    }

    public function down(): void
    {
        Schema::table('parametres_atelier', function (Blueprint $table) {
            $table->dropColumn('tarifs_service_rapide');
        });
        Schema::table('dossiers_reception', function (Blueprint $table) {
            $table->dropColumn('service_cle');
        });
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('service_cle');
        });
    }
};
