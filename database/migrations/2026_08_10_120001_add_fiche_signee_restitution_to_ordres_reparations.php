<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Scan/photo de la fiche de restitution signée par le client (même principe
 * que fiche_signee pour la réception, mais côté sortie du véhicule).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->string('fiche_signee_restitution')->nullable()->after('signature_restitution');
        });
    }

    public function down(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->dropColumn('fiche_signee_restitution');
        });
    }
};
