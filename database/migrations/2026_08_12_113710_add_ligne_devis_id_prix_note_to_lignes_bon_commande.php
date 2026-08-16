<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lignes_bon_commande', function (Blueprint $table) {
            // Lien direct et fiable vers la ligne de devis d'origine — permet de
            // reporter la réponse du fournisseur (prix, disponibilité, note)
            // directement sur le devis sans dépendre d'un matching par position.
            $table->foreignId('ligne_devis_id')->nullable()->after('bon_commande_id')
                ->constrained('lignes_devis')->nullOnDelete();
            $table->decimal('prix_unitaire', 12, 2)->nullable()->after('quantite_disponible');
            $table->string('note')->nullable()->after('prix_unitaire');
        });
    }

    public function down(): void
    {
        Schema::table('lignes_bon_commande', function (Blueprint $table) {
            $table->dropForeign(['ligne_devis_id']);
            $table->dropColumn(['ligne_devis_id', 'prix_unitaire', 'note']);
        });
    }
};
