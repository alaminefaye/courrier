<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    /**
     * Afficher la liste des utilisateurs avec leurs rôles et permissions
     */
    public function index()
    {
        // Seul l'admin peut accéder
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        $users = User::with(['service', 'direction'])->get();
        
        // Permissions disponibles
        $allPermissions = [
            'courriers.entrants.view',
            'courriers.entrants.create',
            'courriers.entrants.edit',
            'courriers.entrants.delete',
            'courriers.entrants.transmettre',
            'courriers.entrants.confirmer',
            'courriers.entrants.export',
            'courriers.sortants.view',
            'courriers.sortants.create',
            'courriers.sortants.edit',
            'courriers.sortants.delete',
            'courriers.sortants.transmettre',
            'courriers.sortants.confirmer',
            'courriers.sortants.export',
            'services.view',
            'services.create',
            'services.edit',
            'services.delete',
            'directions.view',
            'directions.create',
            'directions.edit',
            'directions.delete',
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'dashboard.view',
            'recherche.view',
            'exports.pdf',
            'exports.excel',
        ];

        return view('roles.index', compact('users', 'allPermissions'));
    }

    /**
     * Mettre à jour les permissions d'un utilisateur
     */
    public function updatePermissions(Request $request, $userId)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        $user = User::findOrFail($userId);
        
        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $user->update([
            'permissions' => $request->permissions ?? []
        ]);

        return back()->with('success', 'Permissions mises à jour avec succès pour ' . $user->name);
    }
}
