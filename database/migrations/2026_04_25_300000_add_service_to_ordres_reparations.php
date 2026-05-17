<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->enum('service', ['rapide', 'mecanique', 'electricite', 'carrosserie', 'peinture'])
                  ->nullable()
                  ->after('technicien_id');

            $table->timestamp('date_affectation')->nullable()->after('service');
            $table->foreignId('chef_id')->nullable()->constrained('users')->after('date_affectation');
        });
    }

    public function down(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->dropForeign(['chef_id']);
            $table->dropColumn(['service', 'date_affectation', 'chef_id']);
        });
    }
};
