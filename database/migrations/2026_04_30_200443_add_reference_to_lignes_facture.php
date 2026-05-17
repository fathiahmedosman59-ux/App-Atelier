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
        Schema::table('lignes_facture', function (Blueprint $table) {
            $table->string('reference', 100)->nullable()->after('designation');
        });
    }

    public function down(): void
    {
        Schema::table('lignes_facture', function (Blueprint $table) {
            $table->dropColumn('reference');
        });
    }
};
