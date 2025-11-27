<?php

namespace App\Http\Controllers;

use App\Models\CourrierEntrant;
use App\Models\CourrierSortant;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class RechercheController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Recherche avancée
     */
    public function index(Request $request)
    {
        $resultatsEntrants = collect();
        $resultatsSortants = collect();

        if ($request->filled('recherche')) {
            $recherche = $request->recherche;
            $type = $request->get('type', 'tous'); // 'entrants', 'sortants', 'tous'

            // Recherche dans courriers entrants
            if ($type === 'tous' || $type === 'entrants') {
                $queryEntrants = $this->permissionService->getAccessibleCourriers(auth()->user());
                
                $queryEntrants->where(function($q) use ($recherche) {
                    $q->where('nim', 'like', "%{$recherche}%")
                      ->orWhere('provenance', 'like', "%{$recherche}%")
                      ->orWhere('observations', 'like', "%{$recherche}%")
                      ->orWhere('personne_apporteur', 'like', "%{$recherche}%");
                });

                if ($request->filled('date_debut')) {
                    $queryEntrants->whereDate('date_arrivee', '>=', $request->date_debut);
                }
                if ($request->filled('date_fin')) {
                    $queryEntrants->whereDate('date_arrivee', '<=', $request->date_fin);
                }
                if ($request->filled('type_courrier')) {
                    $queryEntrants->where('type_courrier', $request->type_courrier);
                }
                if ($request->filled('niveau_confidentialite')) {
                    $queryEntrants->where('niveau_confidentialite', $request->niveau_confidentialite);
                }

                $resultatsEntrants = $queryEntrants->with(['destinataireService', 'createdBy'])
                    ->orderBy('date_arrivee', 'desc')
                    ->get();
            }

            // Recherche dans courriers sortants
            if ($type === 'tous' || $type === 'sortants') {
                $querySortants = CourrierSortant::query();
                
                if (!auth()->user()->isAdmin() && !auth()->user()->isDirecteur()) {
                    if (auth()->user()->isChefService() && auth()->user()->service_id) {
                        $querySortants->where('provenance_service_id', auth()->user()->service_id);
                    }
                }

                $querySortants->where(function($q) use ($recherche) {
                    $q->where('nim', 'like', "%{$recherche}%")
                      ->orWhere('destinataire_externe', 'like', "%{$recherche}%")
                      ->orWhere('observations', 'like', "%{$recherche}%")
                      ->orWhere('personne_livreur', 'like', "%{$recherche}%");
                });

                if ($request->filled('date_debut')) {
                    $querySortants->whereDate('date_depart', '>=', $request->date_debut);
                }
                if ($request->filled('date_fin')) {
                    $querySortants->whereDate('date_depart', '<=', $request->date_fin);
                }
                if ($request->filled('type_courrier')) {
                    $querySortants->where('type_courrier', $request->type_courrier);
                }
                if ($request->filled('niveau_confidentialite')) {
                    $querySortants->where('niveau_confidentialite', $request->niveau_confidentialite);
                }

                $resultatsSortants = $querySortants->with(['provenanceService', 'createdBy'])
                    ->orderBy('date_depart', 'desc')
                    ->get();
            }
        }

        return view('recherche.index', compact('resultatsEntrants', 'resultatsSortants'));
    }
}
