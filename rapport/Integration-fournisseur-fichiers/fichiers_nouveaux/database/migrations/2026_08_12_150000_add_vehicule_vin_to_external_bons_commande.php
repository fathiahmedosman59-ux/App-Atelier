<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * app-atelier envoie désormais le VIN du véhicule dans le payload du bon de
 * commande (clé "vehicule.vin") — jusqu'ici silencieusement ignoré côté
 * réception (absent de la validation, du fillable et de l'affichage).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('external_bons_commande', function (Blueprint $table) {
            $table->string('vehicule_vin', 100)->nullable()->after('vehicule_immatriculation');
        });
    }

    public function down(): void
    {
        Schema::table('external_bons_commande', function (Blueprint $table) {
            $table->dropColumn('vehicule_vin');
        });
    }
};
