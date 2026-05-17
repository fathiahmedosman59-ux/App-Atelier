<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bons_commande', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('devis_id')->constrained('devis')->cascadeOnDelete();
            $table->foreignId('or_id')->constrained('ordres_reparations')->cascadeOnDelete();
            $table->enum('statut', ['en_attente', 'commande', 'recu'])->default('en_attente');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('lignes_bon_commande', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_commande_id')->constrained('bons_commande')->cascadeOnDelete();
            $table->string('designation');
            $table->string('reference', 100)->nullable();
            $table->decimal('quantite', 8, 2)->default(1);
            $table->string('fournisseur', 150)->nullable();
            $table->boolean('recu')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_bon_commande');
        Schema::dropIfExists('bons_commande');
    }
};
