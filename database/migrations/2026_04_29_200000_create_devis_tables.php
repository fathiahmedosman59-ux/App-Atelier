<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devis', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('or_id')->constrained('ordres_reparations')->cascadeOnDelete();
            $table->enum('statut', ['brouillon','envoye','accepte','refuse'])->default('brouillon');
            $table->date('date_envoi')->nullable();
            $table->date('date_validation')->nullable();
            $table->string('fichier_signe')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('montant_ht', 10, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(19.00);
            $table->decimal('montant_tva', 10, 2)->default(0);
            $table->decimal('montant_ttc', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('lignes_devis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devis_id')->constrained('devis')->cascadeOnDelete();
            $table->enum('type', ['main_oeuvre','piece','forfait','autre'])->default('main_oeuvre');
            $table->string('designation');
            $table->decimal('quantite', 8, 2)->default(1);
            $table->decimal('prix_unitaire', 10, 2)->default(0);
            $table->decimal('remise', 5, 2)->default(0);
            $table->decimal('total_ht', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_devis');
        Schema::dropIfExists('devis');
    }
};
