<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->json('dommages_carrosserie_sortie')->nullable()->after('dommages_carrosserie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->dropColumn('dommages_carrosserie_sortie');
        });
    }
};
