<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parametres_atelier', function (Blueprint $table) {
            $table->renameColumn('capacite_service_rapide_jour', 'capacite_service_rapide_simultanee');
        });
    }

    public function down(): void
    {
        Schema::table('parametres_atelier', function (Blueprint $table) {
            $table->renameColumn('capacite_service_rapide_simultanee', 'capacite_service_rapide_jour');
        });
    }
};
