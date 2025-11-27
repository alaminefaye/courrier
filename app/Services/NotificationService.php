<?php

namespace App\Services;

use App\Models\CourrierEntrant;
use App\Models\CourrierSortant;
use App\Models\User;
use App\Notifications\CourrierEnRetard;
use App\Notifications\CourrierUrgent;
use App\Notifications\NouveauCourrier;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Notifier un nouveau courrier
     */
    public function notifyNouveauCourrier($courrier, $type = 'entrant')
    {
        // Notifier le service destinataire (pour entrants) ou le service d'origine (pour sortants)
        if ($type === 'entrant' && $courrier instanceof CourrierEntrant) {
            $service = $courrier->destinataireService;
            if ($service && $service->responsable) {
                $service->responsable->notify(new NouveauCourrier($courrier, 'entrant'));
            }
        } elseif ($type === 'sortant' && $courrier instanceof CourrierSortant) {
            $service = $courrier->provenanceService;
            if ($service && $service->responsable) {
                $service->responsable->notify(new NouveauCourrier($courrier, 'sortant'));
            }
        }
    }

    /**
     * Notifier un courrier urgent
     */
    public function notifyUrgent($courrier, $type = 'entrant')
    {
        if ($type === 'entrant' && $courrier instanceof CourrierEntrant) {
            $service = $courrier->destinataireService;
            if ($service) {
                // Notifier le responsable et tous les agents du service
                $users = $service->users;
                Notification::send($users, new CourrierUrgent($courrier, 'entrant'));
            }
        } elseif ($type === 'sortant' && $courrier instanceof CourrierSortant) {
            $service = $courrier->provenanceService;
            if ($service) {
                $users = $service->users;
                Notification::send($users, new CourrierUrgent($courrier, 'sortant'));
            }
        }
    }

    /**
     * Notifier les courriers en retard
     */
    public function notifyEnRetard(CourrierEntrant $courrier)
    {
        $service = $courrier->destinataireService;
        if ($service && $service->responsable) {
            $service->responsable->notify(new CourrierEnRetard($courrier));
        }
    }

    /**
     * Vérifier et notifier les courriers en retard (à appeler via cron)
     */
    public function checkAndNotifyRetards()
    {
        // Courriers transmis depuis plus de 3 jours sans réception
        $courriersEnRetard = CourrierEntrant::where('statut', 'transmis')
            ->where('date_arrivee', '<=', now()->subDays(3))
            ->get();

        foreach ($courriersEnRetard as $courrier) {
            // Mettre à jour le statut
            $courrier->update(['statut' => 'en_retard']);
            // Notifier
            $this->notifyEnRetard($courrier);
        }

        return $courriersEnRetard->count();
    }
}

