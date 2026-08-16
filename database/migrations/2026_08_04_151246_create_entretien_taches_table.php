<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entretien_taches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_moteur_id')->constrained('types_moteur')->cascadeOnDelete();
            $table->string('designation');
            $table->enum('action', ['remplacer', 'inspecter', 'nettoyer', 'lubrifier']);
            $table->unsignedInteger('km_seuil');
            $table->unsignedInteger('mois_seuil')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamps();

            $table->index(['type_moteur_id', 'km_seuil']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entretien_taches');
    }
};
