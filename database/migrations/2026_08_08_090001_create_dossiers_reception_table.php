<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossiers_reception', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();   // DR-2026-0001

            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('vehicule_id')->constrained('vehicules');
            $table->foreignId('conseiller_id')->constrained('users');
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();

            // Arbre de décision du schéma de réception
            $table->enum('motif_visite', ['panne', 'accident', 'service_rapide']);
            $table->enum('type_panne', ['electrique', 'mecanique'])->nullable();
            $table->enum('canal_service', ['entretien_periodique', 'autre'])->nullable();
            $table->enum('canal_arrivee', ['sans_rdv', 'avec_rdv'])->nullable();
            $table->foreignId('type_moteur_id')->nullable()->constrained('types_moteur');
            $table->unsignedInteger('entretien_km_seuil')->nullable();

            // État du véhicule à l'entrée (mêmes champs que sur l'OR — copiés vers l'OR à la conversion)
            $table->unsignedInteger('kilometrage_entree');
            $table->enum('niveau_carburant', ['vide', '1/4', '1/2', '3/4', 'plein'])->default('1/2');
            $table->enum('proprete_interne', ['bon', 'acceptable', 'mauvais'])->default('bon');
            $table->enum('proprete_externe', ['bon', 'acceptable', 'mauvais'])->default('bon');
            $table->text('etat_exterieur')->nullable();
            $table->text('motif_entree');
            $table->boolean('accessoires_presents')->default(false);
            $table->text('liste_accessoires')->nullable();
            $table->json('equipements')->nullable();
            $table->json('dommages_carrosserie')->nullable();
            $table->boolean('signature_client')->default(false);

            $table->date('date_entree');
            $table->time('heure_entree')->nullable();
            $table->enum('urgence', ['normal', 'urgent', 'tres_urgent'])->default('normal');
            $table->text('notes_internes')->nullable();

            // Cycle de vie du dossier, avant transformation en OR
            $table->enum('statut', ['nouveau', 'diagnostic', 'devis_en_cours', 'transforme_en_or', 'en_attente_client', 'annule'])->default('nouveau');
            $table->foreignId('or_id')->nullable()->constrained('ordres_reparations')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossiers_reception');
    }
};
