<?php

namespace App\Services;

use App\Models\HoraireAtelier;
use App\Models\PauseAtelier;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HoraireService
{
    /**
     * Calcule la durée nette de travail en heures entre deux instants.
     *
     * Règles appliquées :
     *  - Seule la plage [heure_debut, heure_fin] de chaque jour ouvré est comptée
     *  - Si les travaux dépassent heure_fin, le décompte s'arrête à heure_fin
     *  - Le décompte reprend à heure_debut du prochain jour ouvré
     *  - Les jours fermés (actif=false) sont intégralement sautés
     *  - Les pauses actives de chaque jour sont soustraites
     */
    public static function calculerDureeNette(Carbon $debut, Carbon $fin): float
    {
        if ($debut >= $fin) {
            return 0.0;
        }

        // Charger horaires et pauses une seule fois pour éviter N+1
        $horaires = HoraireAtelier::all()->keyBy('jour');
        $pauses   = PauseAtelier::actives()
            ->orderBy('heure_debut')
            ->get()
            ->groupBy('jour');

        $totalMinutes = 0;
        $current      = $debut->copy();
        $securite     = 30; // max 30 jours de boucle

        while ($current < $fin && $securite-- > 0) {
            $jourNum = $current->dayOfWeek;
            $horaire = $horaires->get($jourNum);

            // Jour fermé ou non configuré → sauter au prochain jour ouvré
            if (!$horaire || !$horaire->actif || !$horaire->heure_debut) {
                $current = self::debutProchainJourOuvre(
                    $current->copy()->startOfDay()->addDay(),
                    $horaires
                );
                if (!$current) break;
                continue;
            }

            $dateStr  = $current->format('Y-m-d');
            $dayStart = Carbon::parse($dateStr . ' ' . $horaire->heure_debut);
            $dayEnd   = Carbon::parse($dateStr . ' ' . $horaire->heure_fin);

            // Fenêtre effective = intersection de [current, fin] et [dayStart, dayEnd]
            $effectiveStart = $current->gt($dayStart) ? $current->copy() : $dayStart->copy();
            $effectiveEnd   = $fin->lt($dayEnd)       ? $fin->copy()     : $dayEnd->copy();

            if ($effectiveStart < $effectiveEnd) {
                $totalMinutes += self::minutesNettesIntervalle(
                    $effectiveStart,
                    $effectiveEnd,
                    $pauses->get($jourNum, collect())
                );
            }

            // Si fin des travaux est dans cette journée, on a terminé
            if ($fin <= $dayEnd) {
                break;
            }

            // Sinon passer au prochain jour ouvré
            $current = self::debutProchainJourOuvre(
                $current->copy()->startOfDay()->addDay(),
                $horaires
            );
            if (!$current) break;
        }

        return round($totalMinutes / 60, 2);
    }

    /**
     * Calcule les minutes nettes dans un intervalle [start, end] en soustrayant les pauses.
     * Toutes les heures appartiennent au même jour calendaire.
     */
    private static function minutesNettesIntervalle(
        Carbon $start,
        Carbon $end,
        Collection $pauses
    ): float {
        $totalMinutes = 0;
        $current      = $start->copy();
        $dateBase     = $start->format('Y-m-d');

        foreach ($pauses as $pause) {
            $pauseDebut = Carbon::parse($dateBase . ' ' . $pause->heure_debut);
            $pauseFin   = Carbon::parse($dateBase . ' ' . $pause->heure_fin);

            // Pause entièrement après la fin → arrêt
            if ($pauseDebut >= $end) break;

            // Pause entièrement avant le curseur → ignore
            if ($pauseFin <= $current) continue;

            // Ajouter le temps travaillé avant la pause
            $travailJusquA = $pauseDebut->lt($end) ? $pauseDebut->copy() : $end->copy();
            if ($travailJusquA > $current) {
                $totalMinutes += $current->diffInMinutes($travailJusquA);
            }

            // Avancer le curseur après la pause
            if ($pauseFin > $current) {
                $current = $pauseFin->copy();
            }
        }

        // Temps restant après la dernière pause
        if ($current < $end) {
            $totalMinutes += $current->diffInMinutes($end);
        }

        return $totalMinutes;
    }

    /**
     * Retourne un Carbon positionné à heure_debut du prochain jour ouvré.
     * Cherche dans les 7 jours suivants. Retourne null si aucun jour ouvré trouvé.
     */
    private static function debutProchainJourOuvre(Carbon $from, Collection $horaires): ?Carbon
    {
        $date = $from->copy()->startOfDay();

        for ($i = 0; $i < 7; $i++) {
            $horaire = $horaires->get($date->dayOfWeek);

            if ($horaire && $horaire->actif && $horaire->heure_debut) {
                return Carbon::parse($date->format('Y-m-d') . ' ' . $horaire->heure_debut);
            }

            $date->addDay();
        }

        return null;
    }
}
