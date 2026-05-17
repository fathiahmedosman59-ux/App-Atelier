<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->boolean('credit_accorde')->default(false)->after('statut');
            $table->timestamp('credit_accorde_at')->nullable()->after('credit_accorde');
            $table->foreignId('credit_accorde_par')->nullable()->constrained('users')->after('credit_accorde_at');
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropForeign(['credit_accorde_par']);
            $table->dropColumn(['credit_accorde', 'credit_accorde_at', 'credit_accorde_par']);
        });
    }
};
