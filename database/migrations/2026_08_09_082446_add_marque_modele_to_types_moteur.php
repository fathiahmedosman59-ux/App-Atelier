<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Chaque barème d'entretien est propre à un modèle de véhicule précis (pas
 * seulement à un code moteur) : on ajoute marque/modèle pour ne proposer,
 * à la réception, que les types correspondant au véhicule reçu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('types_moteur', function (Blueprint $table) {
            $table->string('marque')->nullable()->after('code');
            $table->string('modele')->nullable()->after('marque');
        });

        // Les deux moteurs déjà catalogués concernent le GWM Poer
        DB::table('types_moteur')->whereIn('code', ['GW4D20', 'GW4C20'])->update([
            'marque' => 'GWM',
            'modele' => 'Poer',
        ]);
    }

    public function down(): void
    {
        Schema::table('types_moteur', function (Blueprint $table) {
            $table->dropColumn(['marque', 'modele']);
        });
    }
};
