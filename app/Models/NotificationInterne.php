<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationInterne extends Model
{
    protected $table = 'notifications_internes';

    protected $fillable = [
        'destinataire_id', 'type', 'titre', 'corps', 'or_id', 'lu_at',
    ];

    protected $casts = [
        'lu_at' => 'datetime',
    ];

    public function destinataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destinataire_id');
    }

    public function ordreReparation(): BelongsTo
    {
        return $this->belongsTo(OrdreReparation::class, 'or_id');
    }

    public function estLue(): bool
    {
        return $this->lu_at !== null;
    }

    public function marquerLue(): void
    {
        if (! $this->lu_at) {
            $this->update(['lu_at' => now()]);
        }
    }

    /**
     * Envoie une notification à tous les utilisateurs ayant le rôle responsable_garantie.
     */
    public static function notifierResponsablesGarantie(string $titre, string $corps, int $orId): void
    {
        $responsables = User::where('role', 'responsable_garantie')->orWhere('role', 'admin')->get();

        foreach ($responsables as $user) {
            self::create([
                'destinataire_id' => $user->id,
                'type'            => 'garantie_nouvelle',
                'titre'           => $titre,
                'corps'           => $corps,
                'or_id'           => $orId,
            ]);
        }
    }

    /**
     * Envoie une notification à tous les utilisateurs pouvant restituer un véhicule
     * (permission restituer_vehicule — rôle par défaut ou surcharge individuelle),
     * dès qu'un OR passe au statut "prêt".
     */
    public static function notifierVehiculePret(string $titre, string $corps, int $orId): void
    {
        $destinataires = User::all()->filter(fn (User $user) => $user->hasPermission('restituer_vehicule'));

        foreach ($destinataires as $user) {
            self::create([
                'destinataire_id' => $user->id,
                'type'            => 'vehicule_pret',
                'titre'           => $titre,
                'corps'           => $corps,
                'or_id'           => $orId,
            ]);
        }
    }
}
