<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE devis MODIFY taux_tva DECIMAL(5,2) NOT NULL DEFAULT 10.00');
        DB::statement('ALTER TABLE factures MODIFY taux_tva DECIMAL(5,2) NOT NULL DEFAULT 10.00');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE devis MODIFY taux_tva DECIMAL(5,2) NOT NULL DEFAULT 19.00');
        DB::statement('ALTER TABLE factures MODIFY taux_tva DECIMAL(5,2) NOT NULL DEFAULT 19.00');
    }
};
