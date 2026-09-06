<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_bon_commande_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('external_bon_commande_id')->constrained('external_bons_commande')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference');
            $table->string('designation')->nullable();
            $table->decimal('quantite_demandee', 10, 2);
            $table->decimal('quantite_disponible', 10, 2)->nullable();
            $table->boolean('disponible')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_bon_commande_lignes');
    }
};
