<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->unsignedInteger('entretien_km_seuil')->nullable()->after('kilometrage_entree');
        });
    }

    public function down(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->dropColumn('entretien_km_seuil');
        });
    }
};
