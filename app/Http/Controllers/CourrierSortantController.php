<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourrierSortantRequest;
use App\Http\Requests\UpdateCourrierSortantRequest;
use App\Models\CourrierSortant;
use App\Models\Service;
use App\Services\NimGeneratorService;
use App\Services\QrCodeService;
use App\Services\FileService;
use App\Services\TimelineService;
use App\Services\AuditService;
use App\Services\PermissionService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourrierSortantController extends Controller
{
    protected $nimGenerator;
    protected $qrCodeService;
    protected $fileService;
    protected $timelineService;
    protected $auditService;
    protected $permissionService;
    protected $notificationService;

    public function __construct(
        NimGeneratorService $nimGenerator,
        QrCodeService $qrCodeService,
        FileService $fileService,
        TimelineService $timelineService,
        AuditService $auditService,
        PermissionService $permissionService,
        NotificationService $notificationService
    ) {
        $this->nimGenerator = $nimGenerator;
        $this->qrCodeService = $qrCodeService;
        $this->fileService = $fileService;
        $this->timelineService = $timelineService;
        $this->auditService = $auditService;
        $this->permissionService = $permissionService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', CourrierSortant::class);
        
        $query = CourrierSortant::query();

        // Filtres selon permissions
        if (!auth()->user()->isAdmin() && !auth()->user()->isDirecteur()) {
            if (auth()->user()->isChefService() && auth()->user()->service_id) {
                $query->where('provenance_service_id', auth()->user()->service_id);
            }
        }

        // Filtres
        if ($request->filled('nim')) {
            $query->where('nim', 'like', '%' . $request->nim . '%');
        }

        if ($request->filled('destinataire_externe')) {
            $query->where('destinataire_externe', 'like', '%' . $request->destinataire_externe . '%');
        }

        if ($request->filled('type_courrier')) {
            $query->where('type_courrier', $request->type_courrier);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_depart', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_depart', '<=', $request->date_fin);
        }

        if ($request->filled('niveau_confidentialite')) {
            $query->where('niveau_confidentialite', $request->niveau_confidentialite);
        }

        $courriers = $query->with(['provenanceService', 'provenanceUser', 'createdBy'])
            ->orderBy('date_depart', 'desc')
            ->paginate(20);

        $services = Service::all();

        return view('courriers.sortants.index', compact('courriers', 'services'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', CourrierSortant::class);
        
        $services = Service::with('direction')->get();
        return view('courriers.sortants.create', compact('services'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourrierSortantRequest $request)
    {
        $this->authorize('create', CourrierSortant::class);
        
        DB::beginTransaction();
        try {
            // Générer NIM
            $nim = $this->nimGenerator->generateSortant();

            // Upload fichier si présent
            $fichierJoint = null;
            if ($request->hasFile('fichier_joint')) {
                $encrypt = in_array($request->niveau_confidentialite, ['confidentiel', 'secret_defense']);
                $fichierJoint = $this->fileService->uploadFile(
                    $request->file('fichier_joint'),
                    new CourrierSortant(),
                    $encrypt
                );
            }

            // Créer le courrier
            $courrier = CourrierSortant::create([
                'nim' => $nim,
                'destinataire_externe' => $request->destinataire_externe,
                'provenance_service_id' => $request->provenance_service_id,
                'provenance_user_id' => $request->provenance_user_id,
                'type_courrier' => $request->type_courrier,
                'personne_livreur' => $request->personne_livreur,
                'date_depart' => now(),
                'statut' => 'enregistre',
                'niveau_confidentialite' => $request->niveau_confidentialite,
                'fichier_joint' => $fichierJoint,
                'observations' => $request->observations,
                'created_by' => auth()->id(),
            ]);

            // Générer QR Code
            $qrData = $this->qrCodeService->generateForCourrier($courrier);
            $courrier->update($qrData);

            // Ajouter événement timeline
            $this->timelineService->addEvent($courrier, 'enregistre', [
                'message' => 'Courrier enregistré par ' . auth()->user()->name
            ]);

            // Log audit
            $this->auditService->log('create', $courrier, auth()->user(), [
                'nim' => $nim,
                'destinataire' => $request->destinataire_externe,
            ]);

            // Notifications
            $this->notificationService->notifyNouveauCourrier($courrier, 'sortant');
            if ($request->niveau_confidentialite === 'urgent' || $request->type_courrier === 'urgent') {
                $this->notificationService->notifyUrgent($courrier, 'sortant');
            }

            DB::commit();

            return redirect()->route('courriers.sortants.show', $courrier->id)
                ->with('success', 'Courrier sortant enregistré avec succès. NIM: ' . $nim);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $courrier = CourrierSortant::with([
            'provenanceService.direction',
            'provenanceUser',
            'createdBy',
            'timeline.user'
        ])->findOrFail($id);

        $this->authorize('view', $courrier);

        // Log audit
        $this->auditService->log('view', $courrier, auth()->user());

        $timeline = $this->timelineService->getTimeline($courrier);

        return view('courriers.sortants.show', compact('courrier', 'timeline'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $courrier = CourrierSortant::findOrFail($id);

        $this->authorize('update', $courrier);

        $services = Service::with('direction')->get();

        return view('courriers.sortants.edit', compact('courrier', 'services'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCourrierSortantRequest $request, string $id)
    {
        $courrier = CourrierSortant::findOrFail($id);

        $this->authorize('update', $courrier);

        DB::beginTransaction();
        try {
            // Upload nouveau fichier si présent
            if ($request->hasFile('fichier_joint')) {
                // Supprimer ancien fichier
                if ($courrier->fichier_joint) {
                    $this->fileService->deleteFile($courrier->fichier_joint);
                }

                $encrypt = in_array($request->niveau_confidentialite ?? $courrier->niveau_confidentialite, ['confidentiel', 'secret_defense']);
                $fichierJoint = $this->fileService->uploadFile(
                    $request->file('fichier_joint'),
                    $courrier,
                    $encrypt
                );
                $request->merge(['fichier_joint' => $fichierJoint]);
            }

            $courrier->update($request->validated());

            // Régénérer QR Code si nécessaire
            if ($request->hasAny(['destinataire_externe', 'provenance_service_id', 'niveau_confidentialite'])) {
                $qrData = $this->qrCodeService->generateForCourrier($courrier);
                $courrier->update($qrData);
            }

            // Ajouter événement timeline
            $this->timelineService->addEvent($courrier, 'modifie', [
                'message' => 'Courrier modifié par ' . auth()->user()->name
            ]);

            // Log audit
            $this->auditService->log('update', $courrier, auth()->user());

            DB::commit();

            return redirect()->route('courriers.sortants.show', $courrier->id)
                ->with('success', 'Courrier modifié avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur lors de la modification: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $courrier = CourrierSortant::findOrFail($id);

        $this->authorize('delete', $courrier);

        DB::beginTransaction();
        try {
            // Supprimer fichier joint
            if ($courrier->fichier_joint) {
                $this->fileService->deleteFile($courrier->fichier_joint);
            }

            // Log audit avant suppression
            $this->auditService->log('delete', $courrier, auth()->user());

            $courrier->delete();

            DB::commit();

            return redirect()->route('courriers.sortants.index')
                ->with('success', 'Courrier supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Transmettre le courrier pour livraison
     */
    public function transmettre(string $id)
    {
        $courrier = CourrierSortant::findOrFail($id);

        $this->authorize('transmettre', $courrier);

        if ($courrier->statut !== 'enregistre') {
            return back()->with('error', 'Ce courrier ne peut pas être transmis dans son état actuel.');
        }

        DB::beginTransaction();
        try {
            $courrier->update(['statut' => 'transmis']);

            // Ajouter événement timeline
            $this->timelineService->addEvent($courrier, 'transmis', [
                'message' => 'Courrier transmis pour livraison par ' . auth()->user()->name
            ]);

            // Log audit
            $this->auditService->log('update', $courrier, auth()->user(), ['action' => 'transmis']);

            DB::commit();

            return back()->with('success', 'Courrier transmis avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la transmission: ' . $e->getMessage());
        }
    }

    /**
     * Confirmer la livraison du courrier
     */
    public function confirmerLivraison(Request $request, string $id)
    {
        $request->validate([
            'signature_type' => 'required|in:qr_scan,signature_numerique',
            'commentaire' => 'nullable|string',
        ]);

        $courrier = CourrierSortant::findOrFail($id);

        $this->authorize('confirmerLivraison', $courrier);

        if ($courrier->statut !== 'transmis') {
            return back()->with('error', 'Ce courrier doit être transmis avant d\'être confirmé.');
        }

        DB::beginTransaction();
        try {
            $courrier->update(['statut' => 'livre']);

            // Créer réception
            $courrier->receptions()->create([
                'user_id' => auth()->id(),
                'signature_type' => $request->signature_type,
                'date_reception' => now(),
                'ip_address' => $request->ip(),
                'commentaire' => $request->commentaire,
            ]);

            // Ajouter événement timeline
            $this->timelineService->addEvent($courrier, 'livre', [
                'message' => 'Courrier livré et confirmé par ' . auth()->user()->name,
                'signature_type' => $request->signature_type,
            ]);

            // Log audit
            $this->auditService->log('update', $courrier, auth()->user(), ['action' => 'livre']);

            DB::commit();

            return back()->with('success', 'Livraison confirmée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la confirmation: ' . $e->getMessage());
        }
    }

    /**
     * Imprimer le QR Code en PDF
     */
    public function imprimerQr(string $id)
    {
        $courrier = CourrierSortant::findOrFail($id);

        $this->authorize('imprimerQr', $courrier);

        // Log audit
        $this->auditService->log('print', $courrier, auth()->user());

        // Charger les relations nécessaires
        $courrier->load(['provenanceService', 'createdBy']);

        // Retourner la vue pour impression
        return view('qrcode.pdf', compact('courrier'));
    }
}
