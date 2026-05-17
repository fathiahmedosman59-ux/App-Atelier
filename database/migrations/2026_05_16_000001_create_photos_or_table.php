<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photos_or', function (Blueprint $table) {
            $table->id();
            $table->foreignId('or_id')->constrained('ordres_reparations')->cascadeOnDelete();
            $table->string('chemin');
            $table->string('nom_original')->nullable();
            $table->unsignedBigInteger('taille')->nullable();
            $table->string('legende')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos_or');
    }
};
