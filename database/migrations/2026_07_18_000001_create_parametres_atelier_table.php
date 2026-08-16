<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametres_atelier', function (Blueprint $table) {
            $table->id();
            $table->time('heure_debut')->default('08:00');
            $table->time('heure_fin')->default('17:00');
            $table->timestamps();
        });

        // Insérer la ligne unique de configuration par défaut
        DB::table('parametres_atelier')->insert([
            'heure_debut' => '08:00',
            'heure_fin'   => '17:00',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('parametres_atelier');
    }
};
