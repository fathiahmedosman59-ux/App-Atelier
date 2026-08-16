<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            // Service Rapide "Autre" à tarif 0 FDJ, créé sans devis (cf.
            // DossierReceptionController::genererDevisServiceRapide) : pas de
            // facturation à faire, contrôle qualité/lavage sautés — affectation
            // technicien puis clôture directe.
            $table->boolean('service_gratuit')->default(false)->after('service');
        });
    }

    public function down(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->dropColumn('service_gratuit');
        });
    }
};
