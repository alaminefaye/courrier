<?php

namespace Database\Seeders;

use App\Models\Direction;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
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

        $serviceCourrier = Service::where('code', 'SCE')->first();
        $serviceRH = Service::where('code', 'SR')->first();
        $serviceCompta = Service::where('code', 'SC')->first();
        $serviceInfo = Service::where('code', 'SI')->first();

        $users = [
            // Admin
            [
                'name' => 'Administrateur',
                'email' => 'admin@admin.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'direction_id' => $dg->id,
            ],
            
            // Directeur
            [
                'name' => 'Directeur Général',
                'email' => 'directeur@admin.com',
                'password' => Hash::make('password'),
                'role' => 'directeur',
                'direction_id' => $dg->id,
            ],
            
            // Chefs de Service
            [
                'name' => 'Chef Service Courrier',
                'email' => 'chef.courrier@admin.com',
                'password' => Hash::make('password'),
                'role' => 'chef_service',
                'direction_id' => $da->id,
                'service_id' => $serviceCourrier->id,
            ],
            [
                'name' => 'Chef Service RH',
                'email' => 'chef.rh@admin.com',
                'password' => Hash::make('password'),
                'role' => 'chef_service',
                'direction_id' => $drh->id,
                'service_id' => $serviceRH->id,
            ],
            [
                'name' => 'Chef Service Comptabilité',
                'email' => 'chef.compta@admin.com',
                'password' => Hash::make('password'),
                'role' => 'chef_service',
                'direction_id' => $df->id,
                'service_id' => $serviceCompta->id,
            ],
            
            // Agents Courrier
            [
                'name' => 'Agent Courrier 1',
                'email' => 'agent1@admin.com',
                'password' => Hash::make('password'),
                'role' => 'agent_courrier',
                'direction_id' => $da->id,
                'service_id' => $serviceCourrier->id,
            ],
            [
                'name' => 'Agent Courrier 2',
                'email' => 'agent2@admin.com',
                'password' => Hash::make('password'),
                'role' => 'agent_courrier',
                'direction_id' => $da->id,
                'service_id' => $serviceCourrier->id,
            ],
            [
                'name' => 'Agent RH',
                'email' => 'agent.rh@admin.com',
                'password' => Hash::make('password'),
                'role' => 'agent_courrier',
                'direction_id' => $drh->id,
                'service_id' => $serviceRH->id,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }

        // Mettre à jour les responsables des services
        $serviceCourrier->update(['responsable_id' => User::where('email', 'chef.courrier@admin.com')->first()->id]);
        $serviceRH->update(['responsable_id' => User::where('email', 'chef.rh@admin.com')->first()->id]);
        $serviceCompta->update(['responsable_id' => User::where('email', 'chef.compta@admin.com')->first()->id]);
    }
}
