<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Ce seeder assigne les permissions par défaut selon les rôles.
     * Les permissions sont stockées dans le champ 'permissions' (JSON) du modèle User.
     */
    public function run(): void
    {
        // Permissions disponibles
        $permissions = [
            // Courriers Entrants
            'courriers.entrants.view',
            'courriers.entrants.create',
            'courriers.entrants.edit',
            'courriers.entrants.delete',
            'courriers.entrants.transmettre',
            'courriers.entrants.confirmer',
            'courriers.entrants.export',
            
            // Courriers Sortants
            'courriers.sortants.view',
            'courriers.sortants.create',
            'courriers.sortants.edit',
            'courriers.sortants.delete',
            'courriers.sortants.transmettre',
            'courriers.sortants.confirmer',
            'courriers.sortants.export',
            
            // Services
            'services.view',
            'services.create',
            'services.edit',
            'services.delete',
            
            // Directions
            'directions.view',
            'directions.create',
            'directions.edit',
            'directions.delete',
            
            // Utilisateurs
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            
            // Dashboard
            'dashboard.view',
            
            // Recherche
            'recherche.view',
            
            // Exports
            'exports.pdf',
            'exports.excel',
        ];

        // Permissions par rôle
        $rolePermissions = [
            'admin' => $permissions, // Toutes les permissions
            
            'directeur' => [
                'courriers.entrants.view',
                'courriers.sortants.view',
                'services.view',
                'directions.view',
                'users.view',
                'dashboard.view',
                'recherche.view',
                'exports.pdf',
                'exports.excel',
            ],
            
            'chef_service' => [
                'courriers.entrants.view',
                'courriers.entrants.create',
                'courriers.entrants.edit',
                'courriers.entrants.transmettre',
                'courriers.entrants.confirmer',
                'courriers.entrants.export',
                'courriers.sortants.view',
                'courriers.sortants.create',
                'courriers.sortants.edit',
                'courriers.sortants.transmettre',
                'courriers.sortants.confirmer',
                'courriers.sortants.export',
                'services.view',
                'dashboard.view',
                'recherche.view',
                'exports.pdf',
                'exports.excel',
            ],
            
            'agent_courrier' => [
                'courriers.entrants.view',
                'courriers.entrants.create',
                'courriers.entrants.edit',
                'courriers.entrants.transmettre',
                'courriers.entrants.export',
                'courriers.sortants.view',
                'courriers.sortants.create',
                'courriers.sortants.edit',
                'courriers.sortants.transmettre',
                'courriers.sortants.export',
                'dashboard.view',
                'recherche.view',
                'exports.pdf',
                'exports.excel',
            ],
        ];

        // Assigner les permissions aux utilisateurs selon leur rôle
        foreach ($rolePermissions as $role => $perms) {
            $users = User::where('role', $role)->get();
            foreach ($users as $user) {
                $user->update(['permissions' => $perms]);
            }
        }

        $this->command->info('Permissions assignées avec succès selon les rôles !');
    }
}
