<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('photos_or', function (Blueprint $table) {
            // 'entree' = prises à la réception, 'sortie' = prises à la restitution.
            // Toutes les photos déjà en base sont des photos de réception.
            $table->enum('moment', ['entree', 'sortie'])->default('entree')->after('or_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photos_or', function (Blueprint $table) {
            $table->dropColumn('moment');
        });
    }
};
