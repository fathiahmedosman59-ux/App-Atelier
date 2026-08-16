<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lignes_bon_commande', function (Blueprint $table) {
            $table->boolean('disponible')->nullable()->after('recu');
            $table->decimal('quantite_disponible', 10, 2)->nullable()->after('disponible');
        });

        Schema::table('bons_commande', function (Blueprint $table) {
            $table->timestamp('fournisseur_repondu_at')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('lignes_bon_commande', function (Blueprint $table) {
            $table->dropColumn(['disponible', 'quantite_disponible']);
        });

        Schema::table('bons_commande', function (Blueprint $table) {
            $table->dropColumn('fournisseur_repondu_at');
        });
    }
};
