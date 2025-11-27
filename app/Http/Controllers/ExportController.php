<?php

namespace App\Http\Controllers;

use App\Models\CourrierEntrant;
use App\Models\CourrierSortant;
use App\Services\PermissionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExportController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Export PDF d'un courrier entrant
     */
    public function exportPdfEntrant($id)
    {
        $courrier = CourrierEntrant::with(['destinataireService', 'destinataireUser', 'createdBy'])->findOrFail($id);

        if (!$this->permissionService->canView(auth()->user(), $courrier)) {
            abort(403, 'Vous n\'avez pas accès à ce courrier.');
        }

        $pdf = Pdf::loadView('exports.courrier-entrant-pdf', compact('courrier'));
        return $pdf->download('courrier-' . $courrier->nim . '.pdf');
    }

    /**
     * Export PDF d'un courrier sortant
     */
    public function exportPdfSortant($id)
    {
        $courrier = CourrierSortant::with(['provenanceService', 'provenanceUser', 'createdBy'])->findOrFail($id);

        if (!$this->permissionService->canView(auth()->user(), $courrier)) {
            abort(403, 'Vous n\'avez pas accès à ce courrier.');
        }

        $pdf = Pdf::loadView('exports.courrier-sortant-pdf', compact('courrier'));
        return $pdf->download('courrier-' . $courrier->nim . '.pdf');
    }

    /**
     * Export Excel des courriers entrants
     */
    public function exportExcelEntrants(Request $request)
    {
        $query = $this->permissionService->getAccessibleCourriers(auth()->user());

        // Appliquer les mêmes filtres que dans index
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

        $courriers = $query->with(['destinataireService', 'createdBy'])->get();

        return Excel::download(new class($courriers) implements FromCollection, WithHeadings, WithMapping {
            protected $courriers;

            public function __construct($courriers)
            {
                $this->courriers = $courriers;
            }

            public function collection()
            {
                return $this->courriers;
            }

            public function headings(): array
            {
                return [
                    'NIM',
                    'Provenance',
                    'Service Destinataire',
                    'Type',
                    'Niveau Confidentialité',
                    'Statut',
                    'Date Arrivée',
                    'Personne Apporteur',
                    'Enregistré par',
                ];
            }

            public function map($courrier): array
            {
                return [
                    $courrier->nim,
                    $courrier->provenance,
                    $courrier->destinataireService->nom ?? '-',
                    config('courrier.types_courrier')[$courrier->type_courrier] ?? $courrier->type_courrier,
                    config('courrier.niveaux_confidentialite')[$courrier->niveau_confidentialite] ?? $courrier->niveau_confidentialite,
                    config('courrier.statuts_entrant')[$courrier->statut] ?? $courrier->statut,
                    $courrier->date_arrivee->format('d/m/Y H:i'),
                    $courrier->personne_apporteur,
                    $courrier->createdBy->name ?? '-',
                ];
            }
        }, 'courriers-entrants-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Export Excel des courriers sortants
     */
    public function exportExcelSortants(Request $request)
    {
        $query = CourrierSortant::query();

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

        $courriers = $query->with(['provenanceService', 'createdBy'])->get();

        return Excel::download(new class($courriers) implements FromCollection, WithHeadings, WithMapping {
            protected $courriers;

            public function __construct($courriers)
            {
                $this->courriers = $courriers;
            }

            public function collection()
            {
                return $this->courriers;
            }

            public function headings(): array
            {
                return [
                    'NIM',
                    'Destinataire Externe',
                    'Service d\'Origine',
                    'Type',
                    'Niveau Confidentialité',
                    'Statut',
                    'Date Départ',
                    'Personne Livreur',
                    'Enregistré par',
                ];
            }

            public function map($courrier): array
            {
                return [
                    $courrier->nim,
                    $courrier->destinataire_externe,
                    $courrier->provenanceService->nom ?? '-',
                    config('courrier.types_courrier')[$courrier->type_courrier] ?? $courrier->type_courrier,
                    config('courrier.niveaux_confidentialite')[$courrier->niveau_confidentialite] ?? $courrier->niveau_confidentialite,
                    config('courrier.statuts_sortant')[$courrier->statut] ?? $courrier->statut,
                    $courrier->date_depart->format('d/m/Y H:i'),
                    $courrier->personne_livreur,
                    $courrier->createdBy->name ?? '-',
                ];
            }
        }, 'courriers-sortants-' . now()->format('Y-m-d') . '.xlsx');
    }
}
