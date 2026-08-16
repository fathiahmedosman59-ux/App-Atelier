<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Le diagnostic (posé après réception, cf. DossierReceptionController::diagnostiquer)
    // peut désormais aboutir à "garantie" en plus de "electrique"/"mecanique" — la
    // branche Garantie du schéma réception se décide au diagnostic, pas à l'accueil.
    public function up(): void
    {
        DB::statement("ALTER TABLE dossiers_reception MODIFY type_panne ENUM('electrique', 'mecanique', 'garantie') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE dossiers_reception MODIFY type_panne ENUM('electrique', 'mecanique') NULL");
    }
};
