<?php

namespace App\Http\Controllers;

use App\Models\CourrierEntrant;
use App\Models\CourrierSortant;
use App\Models\Service;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        $user = auth()->user();
        
        // Statistiques courriers entrants
        $queryEntrants = $this->permissionService->getAccessibleCourriers($user);
        $entrantsAujourdhui = (clone $queryEntrants)->whereDate('date_arrivee', today())->count();
        $entrantsEnRetard = (clone $queryEntrants)->where('statut', 'en_retard')->count();
        $entrantsUrgents = (clone $queryEntrants)->where('niveau_confidentialite', 'urgent')
            ->whereIn('statut', ['enregistre', 'transmis'])->count();
        
        // Statistiques courriers sortants
        $querySortants = CourrierSortant::query();
        if (!$user->isAdmin() && !$user->isDirecteur()) {
            if ($user->isChefService() && $user->service_id) {
                $querySortants->where('provenance_service_id', $user->service_id);
            }
        }
        $sortantsAujourdhui = (clone $querySortants)->whereDate('date_depart', today())->count();
        $sortantsEnAttente = (clone $querySortants)->where('statut', 'enregistre')->count();
        
        // Total courriers
        $totalEntrants = (clone $queryEntrants)->count();
        $totalSortants = (clone $querySortants)->count();
        
        // Évolution mensuelle (6 derniers mois)
        $evolutionMensuelle = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $mois = $date->format('M Y');
            $entrants = (clone $queryEntrants)
                ->whereYear('date_arrivee', $date->year)
                ->whereMonth('date_arrivee', $date->month)
                ->count();
            $sortants = (clone $querySortants)
                ->whereYear('date_depart', $date->year)
                ->whereMonth('date_depart', $date->month)
                ->count();
            $evolutionMensuelle[] = [
                'mois' => $mois,
                'entrants' => $entrants,
                'sortants' => $sortants,
            ];
        }
        
        // Répartition par type
        $repartitionType = [
            'ordinaire' => (clone $queryEntrants)->where('type_courrier', 'ordinaire')->count() + 
                          (clone $querySortants)->where('type_courrier', 'ordinaire')->count(),
            'urgent' => (clone $queryEntrants)->where('type_courrier', 'urgent')->count() + 
                       (clone $querySortants)->where('type_courrier', 'urgent')->count(),
            'confidentiel' => (clone $queryEntrants)->where('type_courrier', 'confidentiel')->count() + 
                            (clone $querySortants)->where('type_courrier', 'confidentiel')->count(),
            'secret_defense' => (clone $queryEntrants)->where('type_courrier', 'secret_defense')->count() + 
                              (clone $querySortants)->where('type_courrier', 'secret_defense')->count(),
        ];
        
        // Top 5 services qui reçoivent le plus de courriers
        $topServices = Service::select('services.*')
            ->selectRaw('COUNT(courriers_entrants.id) as total_entrants')
            ->leftJoin('courriers_entrants', 'services.id', '=', 'courriers_entrants.destinataire_service_id')
            ->groupBy('services.id', 'services.nom', 'services.code', 'services.description', 'services.direction_id', 'services.responsable_id', 'services.created_at', 'services.updated_at')
            ->orderBy('total_entrants', 'desc')
            ->limit(5)
            ->get();
        
        return view('dashboard', compact(
            'entrantsAujourdhui',
            'sortantsAujourdhui',
            'entrantsEnRetard',
            'entrantsUrgents',
            'sortantsEnAttente',
            'totalEntrants',
            'totalSortants',
            'evolutionMensuelle',
            'repartitionType',
            'topServices'
        ));
    }
}

