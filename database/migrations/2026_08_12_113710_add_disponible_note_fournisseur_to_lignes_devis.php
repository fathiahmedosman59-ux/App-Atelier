<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lignes_devis', function (Blueprint $table) {
            // null = pas encore de réponse du fournisseur ; true/false = statué
            $table->boolean('disponible')->nullable()->after('reference');
            $table->string('note_fournisseur')->nullable()->after('disponible');
        });
    }

    public function down(): void
    {
        Schema::table('lignes_devis', function (Blueprint $table) {
            $table->dropColumn(['disponible', 'note_fournisseur']);
        });
    }
};
