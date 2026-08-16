<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Nullable en base pour ne pas casser les réservations déjà prises avant
            // ce champ — la création (ReservationController::store) les rend obligatoires.
            $table->time('heure_rdv')->nullable()->after('date_rdv');
            $table->string('tache')->nullable()->after('canal_service');
            $table->decimal('duree_estimee', 4, 2)->nullable()->after('heure_rdv');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['heure_rdv', 'tache', 'duree_estimee']);
        });
    }
};
