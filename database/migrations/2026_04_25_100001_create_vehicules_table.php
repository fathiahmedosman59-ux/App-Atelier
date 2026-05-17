<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();

            $table->string('immatriculation')->unique();
            $table->string('vin')->nullable()->unique();         // Numéro de châssis

            $table->string('marque');
            $table->string('modele');
            $table->string('version')->nullable();               // Ex: 1.6 HDi Confort
            $table->year('annee')->nullable();
            $table->string('couleur')->nullable();

            $table->enum('motorisation', ['essence', 'diesel', 'hybride', 'electrique', 'gpl', 'autre'])->default('diesel');
            $table->string('cylindree')->nullable();             // Ex: 1600cc
            $table->string('puissance_fiscale')->nullable();     // CV fiscaux

            $table->unsignedInteger('kilometrage')->default(0);
            $table->date('date_mise_circulation')->nullable();
            $table->date('date_expiration_assurance')->nullable();
            $table->date('date_expiration_vignette')->nullable();

            // Garantie constructeur
            $table->boolean('sous_garantie')->default(false);
            $table->date('fin_garantie')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicules');
    }
};
