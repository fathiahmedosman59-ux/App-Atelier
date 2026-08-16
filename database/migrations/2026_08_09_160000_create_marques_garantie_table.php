<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Comptes de facturation "garantie constructeur" (ex: GWM) — quand une
     * panne est couverte par la garantie, les travaux sont facturés sur ce
     * compte plutôt qu'au client, jusqu'à un plafond défini par l'admin
     * (même principe que le compte crédit d'un client Société/Assurance).
     */
    public function up(): void
    {
        Schema::create('marques_garantie', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->decimal('plafond_credit', 12, 2);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marques_garantie');
    }
};
