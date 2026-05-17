<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('compte_actif')->default(false)->after('notes');
            $table->decimal('plafond_compte', 12, 2)->nullable()->after('compte_actif');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['compte_actif', 'plafond_compte']);
        });
    }
};
