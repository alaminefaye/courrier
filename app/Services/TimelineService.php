<?php

namespace App\Services;

use App\Models\CourrierTimeline;
use App\Models\CourrierEntrant;
use App\Models\CourrierSortant;
use Illuminate\Support\Facades\Request;

class TimelineService
{
    /**
     * Ajoute un événement à la timeline d'un courrier
     */
    public function addEvent($courrier, string $action, array $details = [], $userId = null): CourrierTimeline
    {
        $timeline = new CourrierTimeline();
        $timeline->courrier_id = $courrier->id;
        $timeline->courrier_type = $courrier instanceof CourrierEntrant 
            ? CourrierEntrant::class 
            : CourrierSortant::class;
        $timeline->action = $action;
        $timeline->user_id = $userId ?? auth()->id();
        $timeline->details = !empty($details) ? json_encode($details) : null;
        $timeline->ip_address = Request::ip();
        $timeline->created_at = now();
        $timeline->save();
        
        return $timeline;
    }

    /**
     * Récupère la timeline d'un courrier
     */
    public function getTimeline($courrier)
    {
        $type = $courrier instanceof CourrierEntrant 
            ? CourrierEntrant::class 
            : CourrierSortant::class;
        
        return CourrierTimeline::where('courrier_type', $type)
            ->where('courrier_id', $courrier->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Actions possibles
     */
    public static function getActions(): array
    {
        return [
            'enregistre' => 'Enregistré',
            'transmis' => 'Transmis',
            'recu' => 'Reçu',
            'livre' => 'Livré',
            'confirme' => 'Confirmé',
            'en_retard' => 'En retard',
            'non_retire' => 'Non retiré',
            'modifie' => 'Modifié',
            'supprime' => 'Supprimé',
        ];
    }
}

