<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devis', function (Blueprint $table) {
            $table->dropForeign(['or_id']);
        });

        Schema::table('devis', function (Blueprint $table) {
            $table->foreignId('or_id')->nullable()->change();
            $table->foreignId('dossier_id')->nullable()->after('or_id')->constrained('dossiers_reception')->nullOnDelete();
        });

        Schema::table('devis', function (Blueprint $table) {
            $table->foreign('or_id')->references('id')->on('ordres_reparations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('devis', function (Blueprint $table) {
            $table->dropForeign(['or_id']);
            $table->dropForeign(['dossier_id']);
            $table->dropColumn('dossier_id');
        });

        Schema::table('devis', function (Blueprint $table) {
            $table->foreignId('or_id')->nullable(false)->change();
            $table->foreign('or_id')->references('id')->on('ordres_reparations')->cascadeOnDelete();
        });
    }
};
