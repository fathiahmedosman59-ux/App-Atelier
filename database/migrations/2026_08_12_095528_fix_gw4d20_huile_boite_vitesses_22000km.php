<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige une erreur de transcription confirmée par l'utilisateur (capture du
 * tableau constructeur GW4D20) : "Huile de boîte de vitesses (BVM)" est
 * marquée R (remplacer) à 24 mois / 22 000 km, alors que la migration de
 * seed originale (2026_08_04_151300) l'avait transcrite comme I (inspecter)
 * à ce palier — l'hypothèse de rythme "remplacement tous les 30 mois"
 * utilisée à l'époque était fausse sur ce point précis.
 */
return new class extends Migration
{
    public function up(): void
    {
        $id = DB::table('types_moteur')->where('code', 'GW4D20')->value('id');

        DB::table('entretien_taches')
            ->where('type_moteur_id', $id)
            ->where('designation', 'Huile de boîte de vitesses (BVM)')
            ->where('km_seuil', 22000)
            ->update(['action' => 'remplacer']);
    }

    public function down(): void
    {
        $id = DB::table('types_moteur')->where('code', 'GW4D20')->value('id');

        DB::table('entretien_taches')
            ->where('type_moteur_id', $id)
            ->where('designation', 'Huile de boîte de vitesses (BVM)')
            ->where('km_seuil', 22000)
            ->update(['action' => 'inspecter']);
    }
};
