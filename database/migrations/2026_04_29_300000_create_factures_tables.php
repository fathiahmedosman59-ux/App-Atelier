<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('or_id')->constrained('ordres_reparations');
            $table->foreignId('devis_id')->nullable()->constrained('devis')->nullOnDelete();
            $table->foreignId('client_id')->constrained('clients');
            $table->enum('statut', ['brouillon','emise','payee','annulee'])->default('brouillon');
            $table->enum('mode_paiement', ['especes','cheque','carte','virement','compte'])->default('especes');
            $table->date('date_emission');
            $table->date('date_echeance')->nullable();
            $table->date('date_paiement')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('montant_ht', 10, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(19.00);
            $table->decimal('montant_tva', 10, 2)->default(0);
            $table->decimal('montant_ttc', 10, 2)->default(0);
            $table->decimal('montant_paye', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('lignes_facture', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('factures')->cascadeOnDelete();
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
        Schema::dropIfExists('lignes_facture');
        Schema::dropIfExists('factures');
    }
};
