<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->text('motif_approbation_garantie')->nullable()->after('motif_refus_garantie');
        });
    }

    public function down(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->dropColumn('motif_approbation_garantie');
        });
    }
};
