<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->unsignedInteger('kilometrage_sortie')->nullable()->after('date_sortie_reelle');
            $table->enum('niveau_carburant_sortie', ['vide', '1/4', '1/2', '3/4', 'plein'])->nullable()->after('kilometrage_sortie');
            $table->enum('proprete_interne_sortie', ['bon', 'acceptable', 'mauvais'])->nullable()->after('niveau_carburant_sortie');
            $table->enum('proprete_externe_sortie', ['bon', 'acceptable', 'mauvais'])->nullable()->after('proprete_interne_sortie');
            $table->json('equipements_sortie')->nullable()->after('proprete_externe_sortie');
            $table->text('notes_restitution')->nullable()->after('equipements_sortie');
            $table->boolean('signature_restitution')->default(false)->after('notes_restitution');
            $table->foreignId('restitue_par_id')->nullable()->constrained('users')->after('signature_restitution');
        });
    }

    public function down(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->dropForeign(['restitue_par_id']);
            $table->dropColumn([
                'kilometrage_sortie', 'niveau_carburant_sortie',
                'proprete_interne_sortie', 'proprete_externe_sortie',
                'equipements_sortie', 'notes_restitution',
                'signature_restitution', 'restitue_par_id',
            ]);
        });
    }
};
