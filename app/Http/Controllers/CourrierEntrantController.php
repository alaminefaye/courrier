<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourrierEntrantRequest;
use App\Http\Requests\UpdateCourrierEntrantRequest;
use App\Models\CourrierEntrant;
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

class CourrierEntrantController extends Controller
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
        $query = $this->permissionService->getAccessibleCourriers(auth()->user());

        // Filtres
        if ($request->filled('nim')) {
            $query->where('nim', 'like', '%' . $request->nim . '%');
        }

        if ($request->filled('provenance')) {
            $query->where('provenance', 'like', '%' . $request->provenance . '%');
        }

        if ($request->filled('type_courrier')) {
            $query->where('type_courrier', $request->type_courrier);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_arrivee', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_arrivee', '<=', $request->date_fin);
        }

        if ($request->filled('niveau_confidentialite')) {
            $query->where('niveau_confidentialite', $request->niveau_confidentialite);
        }

        $courriers = $query->with(['destinataireService', 'destinataireUser', 'createdBy'])
            ->orderBy('date_arrivee', 'desc')
            ->paginate(20);

        $services = Service::all();

        return view('courriers.entrants.index', compact('courriers', 'services'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', CourrierEntrant::class);
        
        $services = Service::with('direction')->get();
        return view('courriers.entrants.create', compact('services'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourrierEntrantRequest $request)
    {
        $this->authorize('create', CourrierEntrant::class);
        
        DB::beginTransaction();
        try {
            // Générer NIM
            $nim = $this->nimGenerator->generateEntrant();

            // Upload fichier si présent
            $fichierJoint = null;
            if ($request->hasFile('fichier_joint')) {
                $encrypt = in_array($request->niveau_confidentialite, ['confidentiel', 'secret_defense']);
                $fichierJoint = $this->fileService->uploadFile(
                    $request->file('fichier_joint'),
                    new CourrierEntrant(),
                    $encrypt
                );
            }

            // Créer le courrier
            $courrier = CourrierEntrant::create([
                'nim' => $nim,
                'provenance' => $request->provenance,
                'destinataire_service_id' => $request->destinataire_service_id,
                'destinataire_user_id' => $request->destinataire_user_id,
                'type_courrier' => $request->type_courrier,
                'personne_apporteur' => $request->personne_apporteur,
                'date_arrivee' => now(),
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
                'provenance' => $request->provenance,
            ]);

            // Notifications
            $this->notificationService->notifyNouveauCourrier($courrier, 'entrant');
            if ($request->niveau_confidentialite === 'urgent' || $request->type_courrier === 'urgent') {
                $this->notificationService->notifyUrgent($courrier, 'entrant');
            }

            DB::commit();

            return redirect()->route('courriers.entrants.show', $courrier->id)
                ->with('success', 'Courrier entrant enregistré avec succès. NIM: ' . $nim);

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
        $courrier = CourrierEntrant::with([
            'destinataireService.direction',
            'destinataireUser',
            'createdBy',
            'timeline.user'
        ])->findOrFail($id);

        // Vérifier les permissions
        if (!$this->permissionService->canView(auth()->user(), $courrier)) {
            abort(403, 'Vous n\'avez pas accès à ce courrier.');
        }

        // Log audit
        $this->auditService->log('view', $courrier, auth()->user());

        $timeline = $this->timelineService->getTimeline($courrier);

        return view('courriers.entrants.show', compact('courrier', 'timeline'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $courrier = CourrierEntrant::findOrFail($id);

        $this->authorize('update', $courrier);

        $services = Service::with('direction')->get();

        return view('courriers.entrants.edit', compact('courrier', 'services'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCourrierEntrantRequest $request, string $id)
    {
        $courrier = CourrierEntrant::findOrFail($id);

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
            if ($request->hasAny(['provenance', 'destinataire_service_id', 'niveau_confidentialite'])) {
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

            return redirect()->route('courriers.entrants.show', $courrier->id)
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
        $courrier = CourrierEntrant::findOrFail($id);

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

            return redirect()->route('courriers.entrants.index')
                ->with('success', 'Courrier supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Transmettre le courrier au service destinataire
     */
    public function transmettre(string $id)
    {
        $courrier = CourrierEntrant::findOrFail($id);

        $this->authorize('transmettre', $courrier);

        if ($courrier->statut !== 'enregistre') {
            return back()->with('error', 'Ce courrier ne peut pas être transmis dans son état actuel.');
        }

        DB::beginTransaction();
        try {
            $courrier->update(['statut' => 'transmis']);

            // Ajouter événement timeline
            $this->timelineService->addEvent($courrier, 'transmis', [
                'message' => 'Courrier transmis au service ' . $courrier->destinataireService->nom . ' par ' . auth()->user()->name
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
     * Confirmer la réception du courrier
     */
    public function confirmerReception(Request $request, string $id)
    {
        $request->validate([
            'signature_type' => 'required|in:qr_scan,signature_numerique',
            'commentaire' => 'nullable|string',
        ]);

        $courrier = CourrierEntrant::findOrFail($id);

        $this->authorize('confirmerReception', $courrier);

        if ($courrier->statut !== 'transmis') {
            return back()->with('error', 'Ce courrier doit être transmis avant d\'être confirmé.');
        }

        DB::beginTransaction();
        try {
            $courrier->update(['statut' => 'recu']);

            // Créer réception
            $courrier->receptions()->create([
                'user_id' => auth()->id(),
                'signature_type' => $request->signature_type,
                'date_reception' => now(),
                'ip_address' => $request->ip(),
                'commentaire' => $request->commentaire,
            ]);

            // Ajouter événement timeline
            $this->timelineService->addEvent($courrier, 'recu', [
                'message' => 'Courrier reçu par ' . auth()->user()->name,
                'signature_type' => $request->signature_type,
            ]);

            // Log audit
            $this->auditService->log('update', $courrier, auth()->user(), ['action' => 'recu']);

            DB::commit();

            return back()->with('success', 'Réception confirmée avec succès.');

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
        $courrier = CourrierEntrant::findOrFail($id);

        $this->authorize('imprimerQr', $courrier);

        // Log audit
        $this->auditService->log('print', $courrier, auth()->user());

        // Charger les relations nécessaires
        $courrier->load(['destinataireService', 'createdBy']);

        // Retourner la vue pour impression
        return view('qrcode.pdf', compact('courrier'));
    }
}
