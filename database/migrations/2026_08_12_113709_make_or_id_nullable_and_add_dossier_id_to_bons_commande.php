<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Le bon de commande peut désormais être généré dès la création du devis
 * (avant acceptation), donc avant qu'un OR n'existe pour un devis encore
 * rattaché à un dossier de réception — même précédent que pour `devis`
 * (2026_08_08_090002_make_or_id_nullable_and_add_dossier_id_to_devis.php).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bons_commande', function (Blueprint $table) {
            $table->dropForeign(['or_id']);
        });

        Schema::table('bons_commande', function (Blueprint $table) {
            $table->foreignId('or_id')->nullable()->change();
            $table->foreignId('dossier_id')->nullable()->after('or_id')->constrained('dossiers_reception')->nullOnDelete();
        });

        Schema::table('bons_commande', function (Blueprint $table) {
            $table->foreign('or_id')->references('id')->on('ordres_reparations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bons_commande', function (Blueprint $table) {
            $table->dropForeign(['or_id']);
            $table->dropForeign(['dossier_id']);
            $table->dropColumn('dossier_id');
        });

        Schema::table('bons_commande', function (Blueprint $table) {
            $table->foreignId('or_id')->nullable(false)->change();
            $table->foreign('or_id')->references('id')->on('ordres_reparations')->cascadeOnDelete();
        });
    }
};
