<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicules', function (Blueprint $table) {
            $table->foreignId('type_moteur_id')->nullable()->after('motorisation')->constrained('types_moteur')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vehicules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('type_moteur_id');
        });
    }
};
