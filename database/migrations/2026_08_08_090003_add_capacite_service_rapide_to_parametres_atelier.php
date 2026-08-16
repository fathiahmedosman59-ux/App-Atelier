<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // La table parametres_atelier est marquée comme migrée mais n'existe pas en base
        // (créée manquante lors d'une migration précédente) — on la recrée ici si besoin
        // avant d'y ajouter la nouvelle colonne, pour ne pas bloquer ce déploiement.
        if (! Schema::hasTable('parametres_atelier')) {
            Schema::create('parametres_atelier', function (Blueprint $table) {
                $table->id();
                $table->time('heure_debut')->default('08:00');
                $table->time('heure_fin')->default('17:00');
                $table->timestamps();
            });

            DB::table('parametres_atelier')->insert([
                'heure_debut' => '08:00',
                'heure_fin'   => '17:00',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        Schema::table('parametres_atelier', function (Blueprint $table) {
            // Nombre max de véhicules Service Rapide pris en charge par jour. Null = pas de limite.
            $table->unsignedInteger('capacite_service_rapide_jour')->nullable()->after('heure_fin');
        });
    }

    public function down(): void
    {
        Schema::table('parametres_atelier', function (Blueprint $table) {
            $table->dropColumn('capacite_service_rapide_jour');
        });
    }
};
