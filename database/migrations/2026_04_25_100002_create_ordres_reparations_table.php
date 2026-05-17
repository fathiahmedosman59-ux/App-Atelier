<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordres_reparations', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();   // OR-2026-0001

            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('vehicule_id')->constrained('vehicules');
            $table->foreignId('conseiller_id')->constrained('users');  // Réceptionniste

            // Type d'intervention
            $table->enum('type', ['normal', 'garantie', 'sinistre', 'entretien'])->default('normal');

            // Statut principal
            $table->enum('statut', [
                'ouvert',
                'diagnostic',
                'devis_envoye',
                'devis_accepte',
                'en_cours',
                'controle_qualite',
                'pret',
                'facture',
                'livre',
                'annule',
            ])->default('ouvert');

            // Garantie (si type = garantie)
            $table->enum('statut_garantie', ['en_attente', 'approuve', 'refuse'])->nullable();
            $table->text('motif_refus_garantie')->nullable();

            // Réception véhicule
            $table->unsignedInteger('kilometrage_entree');
            $table->enum('niveau_carburant', ['vide', '1/4', '1/2', '3/4', 'plein'])->default('1/2');
            $table->text('etat_exterieur')->nullable();        // Observations visuelles
            $table->text('motif_entree');                      // Motif de la visite
            $table->boolean('accessoires_presents')->default(false);
            $table->text('liste_accessoires')->nullable();     // Roue de secours, cric...

            // Dates
            $table->date('date_entree');
            $table->date('date_sortie_prevue')->nullable();
            $table->date('date_sortie_reelle')->nullable();

            // Technicien assigné
            $table->foreignId('technicien_id')->nullable()->constrained('users');
            $table->enum('urgence', ['normal', 'urgent', 'tres_urgent'])->default('normal');

            $table->text('notes_internes')->nullable();

            $table->timestamps();
        });

        Schema::create('photos_reception', function (Blueprint $table) {
            $table->id();
            $table->foreignId('or_id')->constrained('ordres_reparations')->cascadeOnDelete();
            $table->string('chemin');
            $table->enum('type', ['avant', 'arriere', 'gauche', 'droite', 'interieur', 'autre'])->default('autre');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos_reception');
        Schema::dropIfExists('ordres_reparations');
    }
};
