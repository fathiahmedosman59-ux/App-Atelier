<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->time('heure_entree')->nullable()->after('date_entree');

            // Propreté
            $table->enum('proprete_interne', ['bon', 'acceptable', 'mauvais'])->default('bon')->after('niveau_carburant');
            $table->enum('proprete_externe', ['bon', 'acceptable', 'mauvais'])->default('bon')->after('proprete_interne');

            // Équipements présents dans le véhicule (JSON)
            $table->json('equipements')->nullable()->after('liste_accessoires');

            // Dommages carrosserie (positions sur le schéma, JSON)
            $table->json('dommages_carrosserie')->nullable()->after('equipements');

            // Signature
            $table->boolean('signature_client')->default(false)->after('dommages_carrosserie');
        });
    }

    public function down(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->dropColumn([
                'heure_entree', 'proprete_interne', 'proprete_externe',
                'equipements', 'dommages_carrosserie', 'signature_client',
            ]);
        });
    }
};
