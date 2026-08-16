<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * L'upload de la fiche de réception signée (scan/photo du papier) n'existait
 * que sur l'OR — inutilisable pour un dossier de réception qui n'a pas encore
 * été transformé en OR (cas le plus courant à ce stade). On ajoute le même
 * champ ici ; il est recopié sur l'OR à la création (cf. creerOrDepuisDossier()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossiers_reception', function (Blueprint $table) {
            $table->string('fiche_signee')->nullable()->after('photos');
        });
    }

    public function down(): void
    {
        Schema::table('dossiers_reception', function (Blueprint $table) {
            $table->dropColumn('fiche_signee');
        });
    }
};
