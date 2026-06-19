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
        Schema::create('notifications_internes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destinataire_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');                              // 'garantie_nouvelle', etc.
            $table->string('titre');
            $table->text('corps');
            $table->foreignId('or_id')->nullable()->constrained('ordres_reparations')->cascadeOnDelete();
            $table->timestamp('lu_at')->nullable();             // null = non lu
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_internes');
    }
};
