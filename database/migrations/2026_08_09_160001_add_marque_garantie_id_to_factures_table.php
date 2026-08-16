<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Quand renseigné, cette facture est payée par le compte garantie
     * constructeur (marque du véhicule) plutôt que par le client (client_id
     * reste renseigné pour la traçabilité du véhicule/propriétaire).
     */
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->foreignId('marque_garantie_id')->nullable()->after('client_id')
                ->constrained('marques_garantie')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropConstrainedForeignId('marque_garantie_id');
        });
    }
};
