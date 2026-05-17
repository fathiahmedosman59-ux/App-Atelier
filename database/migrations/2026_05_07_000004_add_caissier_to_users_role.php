<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','chef_garage','mecanicien','receptionniste','magasinier','caissier') NOT NULL DEFAULT 'receptionniste'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','chef_garage','mecanicien','receptionniste','magasinier') NOT NULL DEFAULT 'receptionniste'");
    }
};
