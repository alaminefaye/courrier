<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourrierEntrant;
use App\Models\CourrierSortant;
use App\Services\PermissionService;
use App\Services\TimelineService;
use Illuminate\Http\Request;

class CourrierApiController extends Controller
{
    protected $permissionService;
    protected $timelineService;

    public function __construct(PermissionService $permissionService, TimelineService $timelineService)
    {
        $this->permissionService = $permissionService;
        $this->timelineService = $timelineService;
    }

    /**
     * Récupérer un courrier par NIM
     */
    public function getCourrier($nim)
    {
        // Chercher dans entrants
        $entrant = CourrierEntrant::where('nim', $nim)->first();
        if ($entrant) {
            if (!$this->permissionService->canView(auth()->user(), $entrant)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'type' => 'entrant',
                'courrier' => [
                    'id' => $entrant->id,
                    'nim' => $entrant->nim,
                    'provenance' => $entrant->provenance,
                    'destinataire_service' => $entrant->destinataireService->nom ?? null,
                    'type_courrier' => $entrant->type_courrier,
                    'niveau_confidentialite' => $entrant->niveau_confidentialite,
                    'statut' => $entrant->statut,
                    'date_arrivee' => $entrant->date_arrivee->format('Y-m-d H:i:s'),
                    'personne_apporteur' => $entrant->personne_apporteur,
                    'observations' => $entrant->observations,
                ]
            ]);
        }

        // Chercher dans sortants
        $sortant = CourrierSortant::where('nim', $nim)->first();
        if ($sortant) {
            if (!$this->permissionService->canView(auth()->user(), $sortant)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'type' => 'sortant',
                'courrier' => [
                    'id' => $sortant->id,
                    'nim' => $sortant->nim,
                    'destinataire_externe' => $sortant->destinataire_externe,
                    'provenance_service' => $sortant->provenanceService->nom ?? null,
                    'type_courrier' => $sortant->type_courrier,
                    'niveau_confidentialite' => $sortant->niveau_confidentialite,
                    'statut' => $sortant->statut,
                    'date_depart' => $sortant->date_depart->format('Y-m-d H:i:s'),
                    'personne_livreur' => $sortant->personne_livreur,
                    'observations' => $sortant->observations,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Courrier non trouvé'
        ], 404);
    }

    /**
     * Confirmer réception d'un courrier entrant
     */
    public function confirmerReceptionEntrant(Request $request, $nim)
    {
        $request->validate([
            'signature_type' => 'required|in:qr_scan,signature_numerique',
            'commentaire' => 'nullable|string',
        ]);

        $courrier = CourrierEntrant::where('nim', $nim)->firstOrFail();

        if ($courrier->statut !== 'transmis') {
            return response()->json([
                'success' => false,
                'message' => 'Ce courrier doit être transmis avant d\'être confirmé.'
            ], 400);
        }

        $courrier->update(['statut' => 'recu']);

        $courrier->receptions()->create([
            'user_id' => auth()->id(),
            'signature_type' => $request->signature_type,
            'date_reception' => now(),
            'ip_address' => $request->ip(),
            'commentaire' => $request->commentaire,
        ]);

        // Ajouter événement timeline
        $this->timelineService->addEvent($courrier, 'recu', [
            'message' => 'Courrier reçu via API mobile par ' . auth()->user()->name,
            'signature_type' => $request->signature_type,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Réception confirmée avec succès',
            'date_reception' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Confirmer livraison d'un courrier sortant
     */
    public function confirmerLivraisonSortant(Request $request, $nim)
    {
        $request->validate([
            'signature_type' => 'required|in:qr_scan,signature_numerique',
            'commentaire' => 'nullable|string',
        ]);

        $courrier = CourrierSortant::where('nim', $nim)->firstOrFail();

        if ($courrier->statut !== 'transmis') {
            return response()->json([
                'success' => false,
                'message' => 'Ce courrier doit être transmis avant d\'être confirmé.'
            ], 400);
        }

        $courrier->update(['statut' => 'livre']);

        $courrier->receptions()->create([
            'user_id' => auth()->id(),
            'signature_type' => $request->signature_type,
            'date_reception' => now(),
            'ip_address' => $request->ip(),
            'commentaire' => $request->commentaire,
        ]);

        // Ajouter événement timeline
        $this->timelineService->addEvent($courrier, 'livre', [
            'message' => 'Courrier livré via API mobile par ' . auth()->user()->name,
            'signature_type' => $request->signature_type,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Livraison confirmée avec succès',
            'date_reception' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Liste des courriers (pour mobile)
     */
    public function liste(Request $request)
    {
        $type = $request->get('type', 'entrants'); // 'entrants', 'sortants', 'tous'
        $limit = $request->get('limit', 20);

        $resultats = [];

        if ($type === 'entrants' || $type === 'tous') {
            $query = $this->permissionService->getAccessibleCourriers(auth()->user());
            $entrants = $query->with('destinataireService')
                ->orderBy('date_arrivee', 'desc')
                ->limit($limit)
                ->get();

            foreach ($entrants as $courrier) {
                $resultats[] = [
                    'id' => $courrier->id,
                    'nim' => $courrier->nim,
                    'type' => 'entrant',
                    'provenance' => $courrier->provenance,
                    'destinataire' => $courrier->destinataireService->nom ?? null,
                    'statut' => $courrier->statut,
                    'date' => $courrier->date_arrivee->format('Y-m-d H:i:s'),
                    'niveau_confidentialite' => $courrier->niveau_confidentialite,
                ];
            }
        }

        if ($type === 'sortants' || $type === 'tous') {
            $query = CourrierSortant::query();
            if (!auth()->user()->isAdmin() && !auth()->user()->isDirecteur()) {
                if (auth()->user()->isChefService() && auth()->user()->service_id) {
                    $query->where('provenance_service_id', auth()->user()->service_id);
                }
            }

            $sortants = $query->with('provenanceService')
                ->orderBy('date_depart', 'desc')
                ->limit($limit)
                ->get();

            foreach ($sortants as $courrier) {
                $resultats[] = [
                    'id' => $courrier->id,
                    'nim' => $courrier->nim,
                    'type' => 'sortant',
                    'destinataire' => $courrier->destinataire_externe,
                    'provenance' => $courrier->provenanceService->nom ?? null,
                    'statut' => $courrier->statut,
                    'date' => $courrier->date_depart->format('Y-m-d H:i:s'),
                    'niveau_confidentialite' => $courrier->niveau_confidentialite,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'count' => count($resultats),
            'courriers' => $resultats
        ]);
    }
}
