<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['particulier', 'societe', 'assurance'])->default('particulier');

            // Particulier
            $table->string('nom');
            $table->string('prenom')->nullable();

            // Société / Assurance
            $table->string('raison_sociale')->nullable();
            $table->string('rc')->nullable();           // Registre commercial
            $table->string('nif')->nullable();          // Numéro identification fiscale
            $table->string('contact_nom')->nullable();  // Nom du responsable

            // Commun
            $table->string('telephone');
            $table->string('telephone2')->nullable();
            $table->string('email')->nullable();
            $table->text('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('wilaya')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
