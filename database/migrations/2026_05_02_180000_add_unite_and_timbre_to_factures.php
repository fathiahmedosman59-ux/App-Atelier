<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE lignes_facture ADD COLUMN unite VARCHAR(20) NULL DEFAULT NULL AFTER reference");
        DB::statement("ALTER TABLE factures ADD COLUMN frais_timbre DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER notes");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE lignes_facture DROP COLUMN unite");
        DB::statement("ALTER TABLE factures DROP COLUMN frais_timbre");
    }
};
