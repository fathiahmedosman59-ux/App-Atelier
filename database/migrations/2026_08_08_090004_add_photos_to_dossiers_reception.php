<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossiers_reception', function (Blueprint $table) {
            // Photos prises à la réception, avant que l'OR (et donc photos_or) n'existe.
            // Repris tel quel dans photos_or au moment de la conversion en OR.
            $table->json('photos')->nullable()->after('signature_client');
        });
    }

    public function down(): void
    {
        Schema::table('dossiers_reception', function (Blueprint $table) {
            $table->dropColumn('photos');
        });
    }
};
