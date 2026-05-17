<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->dropForeign(['conseiller_id']);
            $table->foreignId('conseiller_id')->nullable()->change();
            $table->foreign('conseiller_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ordres_reparations', function (Blueprint $table) {
            $table->dropForeign(['conseiller_id']);
            $table->foreignId('conseiller_id')->nullable(false)->change();
            $table->foreign('conseiller_id')->references('id')->on('users');
        });
    }
};
