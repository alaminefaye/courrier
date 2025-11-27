<?php

namespace Database\Seeders;

use App\Models\CourrierEntrant;
use App\Models\CourrierSortant;
use App\Models\Service;
use App\Models\User;
use App\Services\NimGeneratorService;
use App\Services\QrCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourrierSeeder extends Seeder
{
    protected $nimGenerator;
    protected $qrCodeService;

    public function __construct()
    {
        $this->nimGenerator = app(NimGeneratorService::class);
        $this->qrCodeService = app(QrCodeService::class);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceCourrier = Service::where('code', 'SCE')->first();
        $serviceRH = Service::where('code', 'SR')->first();
        $serviceCompta = Service::where('code', 'SC')->first();
        
        $admin = User::where('email', 'admin@admin.com')->first();
        $agent1 = User::where('email', 'agent1@admin.com')->first();

        // Courriers Entrants
        $entrants = [
            [
                'nim' => $this->nimGenerator->generateEntrant(),
                'provenance' => 'Ministère de l\'Intérieur',
                'destinataire_service_id' => $serviceRH->id,
                'type_courrier' => 'urgent',
                'personne_apporteur' => 'M. Diallo',
                'date_arrivee' => now()->subDays(2),
                'statut' => 'recu',
                'niveau_confidentialite' => 'urgent',
                'observations' => 'Courrier urgent concernant le recrutement',
                'created_by' => $admin->id,
            ],
            [
                'nim' => $this->nimGenerator->generateEntrant(),
                'provenance' => 'Banque Centrale',
                'destinataire_service_id' => $serviceCompta->id,
                'type_courrier' => 'confidentiel',
                'personne_apporteur' => 'Mme Sow',
                'date_arrivee' => now()->subDays(1),
                'statut' => 'transmis',
                'niveau_confidentialite' => 'confidentiel',
                'observations' => 'Document financier confidentiel',
                'created_by' => $agent1->id,
            ],
            [
                'nim' => $this->nimGenerator->generateEntrant(),
                'provenance' => 'Ministère des Finances',
                'destinataire_service_id' => $serviceCourrier->id,
                'type_courrier' => 'ordinaire',
                'personne_apporteur' => 'M. Ba',
                'date_arrivee' => now(),
                'statut' => 'enregistre',
                'niveau_confidentialite' => 'ordinaire',
                'created_by' => $agent1->id,
            ],
        ];

        foreach ($entrants as $entrant) {
            $courrier = CourrierEntrant::firstOrCreate(
                ['nim' => $entrant['nim']],
                $entrant
            );
            if (!$courrier->qr_code) {
                $qrData = $this->qrCodeService->generateForCourrier($courrier);
                $courrier->update($qrData);
            }
        }

        // Courriers Sortants
        $sortants = [
            [
                'nim' => $this->nimGenerator->generateSortant(),
                'destinataire_externe' => 'Ministère de la Justice',
                'provenance_service_id' => $serviceRH->id,
                'type_courrier' => 'ordinaire',
                'personne_livreur' => 'M. Ndiaye',
                'date_depart' => now()->subDays(3),
                'statut' => 'confirme',
                'niveau_confidentialite' => 'ordinaire',
                'observations' => 'Réponse à une demande d\'information',
                'created_by' => $admin->id,
            ],
            [
                'nim' => $this->nimGenerator->generateSortant(),
                'destinataire_externe' => 'Office National de la Statistique',
                'provenance_service_id' => $serviceCompta->id,
                'type_courrier' => 'urgent',
                'personne_livreur' => 'Mme Diop',
                'date_depart' => now()->subDays(1),
                'statut' => 'transmis',
                'niveau_confidentialite' => 'urgent',
                'created_by' => $agent1->id,
            ],
            [
                'nim' => $this->nimGenerator->generateSortant(),
                'destinataire_externe' => 'Préfecture de Dakar',
                'provenance_service_id' => $serviceCourrier->id,
                'type_courrier' => 'ordinaire',
                'personne_livreur' => 'M. Fall',
                'date_depart' => now(),
                'statut' => 'enregistre',
                'niveau_confidentialite' => 'ordinaire',
                'created_by' => $agent1->id,
            ],
        ];

        foreach ($sortants as $sortant) {
            $courrier = CourrierSortant::firstOrCreate(
                ['nim' => $sortant['nim']],
                $sortant
            );
            if (!$courrier->qr_code) {
                $qrData = $this->qrCodeService->generateForCourrier($courrier);
                $courrier->update($qrData);
            }
        }
    }
}
