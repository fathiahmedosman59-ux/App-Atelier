<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();   // RDV-2026-0001

            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('vehicule_id')->constrained('vehicules');
            $table->foreignId('conseiller_id')->constrained('users');   // Réceptionniste qui a pris le RDV

            $table->enum('canal_service', ['entretien_periodique', 'autre']);
            $table->date('date_rdv');
            $table->enum('statut', ['planifie', 'honore', 'annule', 'no_show'])->default('planifie');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
