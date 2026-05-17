<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encaissements_globaux', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('client_id')->constrained('clients');
            $table->decimal('montant_total', 12, 2)->default(0);
            $table->enum('statut', ['emis', 'paye'])->default('emis');
            $table->string('mode_paiement')->default('especes');
            $table->date('date_emission');
            $table->date('date_paiement')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('factures', function (Blueprint $table) {
            $table->foreignId('encaissement_global_id')
                  ->nullable()
                  ->constrained('encaissements_globaux')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\EncaissementGlobal::class);
            $table->dropColumn('encaissement_global_id');
        });
        Schema::dropIfExists('encaissements_globaux');
    }
};
