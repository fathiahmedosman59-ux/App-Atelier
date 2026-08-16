<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pauses_atelier', function (Blueprint $table) {
            $table->tinyInteger('jour')->after('id'); // 0=Dim 1=Lun 2=Mar 3=Mer 4=Jeu 5=Ven 6=Sam
        });
    }

    public function down(): void
    {
        Schema::table('pauses_atelier', function (Blueprint $table) {
            $table->dropColumn('jour');
        });
    }
};
