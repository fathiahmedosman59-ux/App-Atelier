<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\PhotoOr;

/**
 * Modèle OrdreReparation.
 *
 * L'OR est le document central de l'atelier. Il est créé à l'entrée du véhicule
 * et suit toute la vie du dossier jusqu'à la restitution au client.
 *
 * Cycle de vie des statuts :
 *   ouvert → diagnostic → devis_envoye → devis_accepte → en_cours
 *   → controle_qualite → lavage → pret → facture → livre
 *   (annule possible à tout moment)
 */
class OrdreReparation extends Model
{
    protected $table = 'ordres_reparations';

    protected $fillable = [
        // Identification et affectation
        'numero', 'client_id', 'vehicule_id', 'conseiller_id', 'technicien_id',
        'service', 'date_affectation', 'chef_id',
        // Statut et type
        'type', 'statut', 'statut_garantie', 'motif_refus_garantie',
        // État du véhicule à l'entrée
        'kilometrage_entree', 'niveau_carburant', 'proprete_interne', 'proprete_externe',
        'etat_exterieur', 'motif_entree', 'accessoires_presents', 'liste_accessoires',
        'equipements', 'dommages_carrosserie', 'signature_client',
        // Dates et planning
        'date_entree', 'heure_entree', 'date_sortie_prevue', 'date_sortie_reelle',
        'urgence', 'notes_internes', 'fiche_signee',
        // Pointage des travaux
        'heure_debut_travaux', 'heure_fin_travaux', 'duree_estimee',
        // État du véhicule à la sortie (restitution)
        'kilometrage_sortie', 'niveau_carburant_sortie',
        'proprete_interne_sortie', 'proprete_externe_sortie',
        'equipements_sortie', 'notes_restitution', 'signature_restitution',
        'restitue_par_id',
    ];

    protected $casts = [
        'date_entree'          => 'date',
        'date_sortie_prevue'   => 'date',
        'date_sortie_reelle'   => 'date',
        'date_affectation'     => 'datetime',
        'heure_debut_travaux'  => 'datetime',
        'heure_fin_travaux'    => 'datetime',
        'accessoires_presents' => 'boolean',
        'signature_client'     => 'boolean',
        'equipements'          => 'array',   // Stocké en JSON, retourné comme tableau PHP
        'dommages_carrosserie' => 'array',   // Idem — zones de carrosserie endommagées
        'equipements_sortie'   => 'array',   // Équipements vérifiés à la restitution
    ];

    // ── Relations ──────────────────────────────────────────────────────

    /** Client propriétaire du véhicule */
    public function client(): BelongsTo     { return $this->belongsTo(Client::class); }

    /** Véhicule concerné par cet OR */
    public function vehicule(): BelongsTo   { return $this->belongsTo(Vehicule::class); }

    /** Réceptionniste qui a créé l'OR */
    public function conseiller(): BelongsTo { return $this->belongsTo(User::class, 'conseiller_id'); }

    /** Mécanicien affecté aux travaux */
    public function technicien(): BelongsTo { return $this->belongsTo(User::class, 'technicien_id'); }

    /** Chef de garage qui a validé l'affectation */
    public function chef(): BelongsTo       { return $this->belongsTo(User::class, 'chef_id'); }

    /** Réceptionniste qui a effectué la restitution du véhicule */
    public function restitueePar(): BelongsTo { return $this->belongsTo(User::class, 'restitue_par_id'); }

    /** Photos de réception originales (ancien système, table photos_reception) */
    public function photos(): HasMany       { return $this->hasMany(PhotoReception::class, 'or_id'); }

    /** Photos du véhicule prises lors de la réception (nouveau système, table photos_or) */
    public function photosOr(): HasMany     { return $this->hasMany(PhotoOr::class, 'or_id'); }

    /** Dernier devis établi pour cet OR */
    public function devis(): HasOne         { return $this->hasOne(Devis::class, 'or_id')->latestOfMany(); }

    /** Tous les devis établis pour cet OR (un OR peut avoir plusieurs devis successifs) */
    public function allDevis(): HasMany     { return $this->hasMany(Devis::class, 'or_id')->orderBy('id'); }

    /** Facture liée à cet OR */
    public function facture(): HasOne       { return $this->hasOne(Facture::class, 'or_id'); }

    // ── Calculs de temps et performance ────────────────────────────────

    /**
     * Calcule la durée réelle des travaux en heures (à partir des heures de début et fin).
     * Retourne null si l'une des deux heures n'est pas enregistrée.
     */
    public function getDureeReelleHeures(): ?float
    {
        if (!$this->heure_debut_travaux || !$this->heure_fin_travaux) return null;
        return round($this->heure_debut_travaux->diffInMinutes($this->heure_fin_travaux) / 60, 2);
    }

    /**
     * Calcule le taux de performance du mécanicien en pourcentage.
     * 100% = durée réelle = durée estimée. >100% = plus rapide que prévu.
     * Retourne null si les données nécessaires sont manquantes.
     */
    public function getPerformance(): ?int
    {
        $reel = $this->getDureeReelleHeures();
        if (!$reel || !$this->duree_estimee) return null;
        return (int) round($this->duree_estimee / $reel * 100);
    }

    /**
     * Formate un nombre d'heures en format lisible (ex: 1.5 → "1h30min").
     */
    public function formatDuree(float $heures): string
    {
        $h = (int) $heures;
        $m = (int) round(($heures - $h) * 60);
        return $h > 0 ? "{$h}h" . ($m > 0 ? "{$m}min" : '') : "{$m}min";
    }

    // ── Labels et couleurs pour l'affichage ────────────────────────────

    /** Retourne le libellé français du statut de l'OR */
    public function getStatutLabel(): string
    {
        return match($this->statut) {
            'ouvert'           => 'Ouvert',
            'diagnostic'       => 'Diagnostic',
            'devis_envoye'     => 'Devis envoyé',
            'devis_accepte'    => 'Devis accepté',
            'en_cours'         => 'En cours',
            'controle_qualite' => 'Contrôle qualité',
            'lavage'           => 'Lavage',
            'pret'             => 'Prêt',
            'facture'          => 'Facturé',
            'livre'            => 'Livré',
            'annule'           => 'Annulé',
            default            => $this->statut,
        };
    }

    /** Retourne la couleur Tailwind associée au statut (pour les badges) */
    public function getStatutColor(): string
    {
        return match($this->statut) {
            'ouvert'           => 'blue',
            'diagnostic'       => 'yellow',
            'devis_envoye'     => 'orange',
            'devis_accepte'    => 'indigo',
            'en_cours'         => 'purple',
            'controle_qualite' => 'pink',
            'lavage'           => 'cyan',
            'pret'             => 'teal',
            'facture'          => 'green',
            'livre'            => 'gray',
            'annule'           => 'red',
            default            => 'gray',
        };
    }

    /** Retourne le libellé du type d'OR (normal, garantie, sinistre, entretien) */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'normal'    => 'Normal',
            'garantie'  => 'Garantie',
            'sinistre'  => 'Sinistre',
            'entretien' => 'Entretien',
            default     => $this->type,
        };
    }

    /** Retourne le libellé du service atelier (mécanique, carrosserie, etc.) */
    public function getServiceLabel(): string
    {
        return match($this->service) {
            'rapide'      => 'Service Rapide',
            'mecanique'   => 'Mécanique',
            'electricite' => 'Électricité',
            'carrosserie' => 'Carrosserie',
            'peinture'    => 'Peinture',
            default       => 'Non défini',
        };
    }

    /** Retourne la couleur Tailwind associée au service pour les badges */
    public function getServiceColor(): string
    {
        return match($this->service) {
            'rapide'      => 'blue',
            'mecanique'   => 'orange',
            'electricite' => 'yellow',
            'carrosserie' => 'purple',
            'peinture'    => 'pink',
            default       => 'gray',
        };
    }

    /** Indique si l'OR a un mécanicien et un service affectés */
    public function isAffecte(): bool
    {
        return $this->technicien_id !== null && $this->service !== null;
    }

    /** Retourne le libellé du niveau d'urgence */
    public function getUrgenceLabel(): string
    {
        return match($this->urgence) {
            'normal'      => 'Normal',
            'urgent'      => 'Urgent',
            'tres_urgent' => 'Très urgent',
            default       => 'Normal',
        };
    }

    /**
     * Génère un numéro d'OR unique au format OR-AAAA-XXXX.
     * Exemple : OR-2026-0001, OR-2026-0002...
     * La séquence repart à 1 chaque année.
     */
    public static function genererNumero(): string
    {
        $annee   = now()->year;
        $dernier = self::whereYear('created_at', $annee)->max('numero');
        // Extrait les 4 derniers chiffres du dernier numéro et incrémente
        $sequence = $dernier ? (int) substr($dernier, -4) + 1 : 1;
        return sprintf('OR-%d-%04d', $annee, $sequence);
    }
}
