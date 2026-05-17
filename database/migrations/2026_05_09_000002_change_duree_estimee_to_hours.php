<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Convertir les valeurs existantes (minutes → heures) avant de changer le type
        DB::statement('UPDATE ordres_reparations SET duree_estimee = ROUND(duree_estimee / 60, 2) WHERE duree_estimee IS NOT NULL');
        DB::statement('ALTER TABLE ordres_reparations MODIFY duree_estimee DECIMAL(5,2) NULL');
    }

    public function down(): void
    {
        DB::statement('UPDATE ordres_reparations SET duree_estimee = ROUND(duree_estimee * 60) WHERE duree_estimee IS NOT NULL');
        DB::statement('ALTER TABLE ordres_reparations MODIFY duree_estimee SMALLINT UNSIGNED NULL');
    }
};
