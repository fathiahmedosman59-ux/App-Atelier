<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Le rôle "magasinier" est supprimé : la gestion des bons de commande
 * pièces est désormais assurée par le système fournisseur (stcd-magasin).
 * app-atelier ne garde qu'un suivi en lecture seule pour chef_garage/admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','chef_garage','receptionniste','mecanicien','caissier','responsable_garantie') NOT NULL DEFAULT 'receptionniste'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','chef_garage','receptionniste','mecanicien','magasinier','caissier','responsable_garantie') NOT NULL DEFAULT 'receptionniste'");
    }
};
