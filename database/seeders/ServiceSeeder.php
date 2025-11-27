<?php

namespace Database\Seeders;

use App\Models\Direction;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dg = Direction::where('code', 'DG')->first();
        $drh = Direction::where('code', 'DRH')->first();
        $df = Direction::where('code', 'DF')->first();
        $dt = Direction::where('code', 'DT')->first();
        $da = Direction::where('code', 'DA')->first();

        $services = [
            // Direction Générale
            [
                'nom' => 'Secrétariat Général',
                'code' => 'SG',
                'description' => 'Secrétariat Général',
                'direction_id' => $dg->id,
            ],
            [
                'nom' => 'Cabinet du Directeur',
                'code' => 'CAB',
                'description' => 'Cabinet du Directeur Général',
                'direction_id' => $dg->id,
            ],
            
            // Direction des Ressources Humaines
            [
                'nom' => 'Service Recrutement',
                'code' => 'SR',
                'description' => 'Service de recrutement du personnel',
                'direction_id' => $drh->id,
            ],
            [
                'nom' => 'Service Paie',
                'code' => 'SP',
                'description' => 'Service de gestion de la paie',
                'direction_id' => $drh->id,
            ],
            
            // Direction Financière
            [
                'nom' => 'Service Comptabilité',
                'code' => 'SC',
                'description' => 'Service de comptabilité',
                'direction_id' => $df->id,
            ],
            [
                'nom' => 'Service Budget',
                'code' => 'SB',
                'description' => 'Service de gestion budgétaire',
                'direction_id' => $df->id,
            ],
            
            // Direction Technique
            [
                'nom' => 'Service Informatique',
                'code' => 'SI',
                'description' => 'Service informatique',
                'direction_id' => $dt->id,
            ],
            [
                'nom' => 'Service Maintenance',
                'code' => 'SM',
                'description' => 'Service de maintenance',
                'direction_id' => $dt->id,
            ],
            
            // Direction Administrative
            [
                'nom' => 'Service Courrier',
                'code' => 'SCE',
                'description' => 'Service de gestion du courrier',
                'direction_id' => $da->id,
            ],
            [
                'nom' => 'Service Archives',
                'code' => 'SA',
                'description' => 'Service d\'archivage',
                'direction_id' => $da->id,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['code' => $service['code']],
                $service
            );
        }
    }
}
