<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Les techniciens (mécaniciens) ne sont plus des comptes de connexion (rôle
 * 'mecanicien' retiré) : ils deviennent de simples fiches, comme les clients.
 * Cette migration convertit les comptes mécanicien existants en fiches
 * technicien, reporte leurs OR déjà affectés, puis change la cible de la
 * contrainte de clé étrangère ordres_reparations.technicien_id de `users`
 * vers `techniciens`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->dropForeign(['technicien_id']);
        });

        $mecaniciens = DB::table('users')->where('role', 'mecanicien')->get();
        foreach ($mecaniciens as $user) {
            $mots   = preg_split('/\s+/', trim($user->name), 2);
            $prenom = $mots[0] ?? $user->name;
            $nom    = $mots[1] ?? '';

            $technicienId = DB::table('techniciens')->insertGetId([
                'nom'        => $nom !== '' ? $nom : $prenom,
                'prenom'     => $nom !== '' ? $prenom : null,
                'actif'      => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('ordres_reparations')->where('technicien_id', $user->id)->update(['technicien_id' => $technicienId]);
        }

        DB::table('users')->where('role', 'mecanicien')->delete();

        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->foreign('technicien_id')->references('id')->on('techniciens');
        });
    }

    public function down(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->dropForeign(['technicien_id']);
        });

        // Les fiches technicien et le remappage des OR ne sont pas reconstruits
        // (comptes utilisateurs supprimés) — on ne restaure que la contrainte.
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->foreign('technicien_id')->references('id')->on('users');
        });
    }
};
