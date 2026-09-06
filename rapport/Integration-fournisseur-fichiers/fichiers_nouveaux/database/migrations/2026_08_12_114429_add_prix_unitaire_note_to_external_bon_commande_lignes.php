<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Le garage (app-atelier) envoie désormais le bon de commande dès la
 * création du devis, avant validation, pour que le prix de vente et la
 * disponibilité reviennent se remplir sur le devis avant même son
 * acceptation. Le vendeur doit donc pouvoir renseigner un prix (auto-rempli
 * depuis le produit sélectionné) et une note libre (ex: raison d'une
 * indisponibilité) par ligne.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('external_bon_commande_lignes', function (Blueprint $table) {
            $table->decimal('prix_unitaire', 12, 2)->nullable()->after('disponible');
            $table->string('note')->nullable()->after('prix_unitaire');
        });
    }

    public function down(): void
    {
        Schema::table('external_bon_commande_lignes', function (Blueprint $table) {
            $table->dropColumn(['prix_unitaire', 'note']);
        });
    }
};
