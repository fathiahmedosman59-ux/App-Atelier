<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('parametres_atelier');

        Schema::create('horaires_atelier', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('jour')->unique(); // 0=Dim 1=Lun 2=Mar 3=Mer 4=Jeu 5=Ven 6=Sam
            $table->string('nom_jour', 20);
            $table->time('heure_debut')->nullable();
            $table->time('heure_fin')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // Seed par défaut : Sam→Mer 07h-18h, Jeu 07h-14h, Ven fermé
        $jours = [
            ['jour' => 6, 'nom_jour' => 'Samedi',   'heure_debut' => '07:00', 'heure_fin' => '18:00', 'actif' => 1],
            ['jour' => 0, 'nom_jour' => 'Dimanche',  'heure_debut' => '07:00', 'heure_fin' => '18:00', 'actif' => 1],
            ['jour' => 1, 'nom_jour' => 'Lundi',     'heure_debut' => '07:00', 'heure_fin' => '18:00', 'actif' => 1],
            ['jour' => 2, 'nom_jour' => 'Mardi',     'heure_debut' => '07:00', 'heure_fin' => '18:00', 'actif' => 1],
            ['jour' => 3, 'nom_jour' => 'Mercredi',  'heure_debut' => '07:00', 'heure_fin' => '18:00', 'actif' => 1],
            ['jour' => 4, 'nom_jour' => 'Jeudi',     'heure_debut' => '07:00', 'heure_fin' => '14:00', 'actif' => 1],
            ['jour' => 5, 'nom_jour' => 'Vendredi',  'heure_debut' => null,    'heure_fin' => null,    'actif' => 0],
        ];

        foreach ($jours as $j) {
            DB::table('horaires_atelier')->insert(array_merge($j, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('horaires_atelier');
    }
};
