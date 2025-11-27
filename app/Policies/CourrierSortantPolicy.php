<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CourrierSortant;
use App\Services\PermissionService;

class CourrierSortantPolicy
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Détermine si l'utilisateur peut voir n'importe quel courrier sortant.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isDirecteur() || $user->isChefService() || $user->isAgentCourrier();
    }

    /**
     * Détermine si l'utilisateur peut voir le courrier sortant.
     */
    public function view(User $user, CourrierSortant $courrierSortant): bool
    {
        return $this->permissionService->canView($user, $courrierSortant);
    }

    /**
     * Détermine si l'utilisateur peut créer des courriers sortants.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isAgentCourrier();
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour le courrier sortant.
     */
    public function update(User $user, CourrierSortant $courrierSortant): bool
    {
        // Admin et Directeur peuvent toujours modifier
        if ($user->isAdmin() || $user->isDirecteur()) {
            return true;
        }
        
        return $this->permissionService->canEdit($user, $courrierSortant);
    }

    /**
     * Détermine si l'utilisateur peut supprimer le courrier sortant.
     */
    public function delete(User $user, CourrierSortant $courrierSortant): bool
    {
        return $this->permissionService->canDelete($user, $courrierSortant);
    }

    /**
     * Détermine si l'utilisateur peut transmettre le courrier sortant.
     */
    public function transmettre(User $user, CourrierSortant $courrierSortant): bool
    {
        // Admin et Directeur peuvent toujours transmettre
        if ($user->isAdmin() || $user->isDirecteur()) {
            return true;
        }
        
        return $this->permissionService->canTransmit($user, $courrierSortant);
    }

    /**
     * Détermine si l'utilisateur peut confirmer la livraison du courrier sortant.
     */
    public function confirmerLivraison(User $user, CourrierSortant $courrierSortant): bool
    {
        // Admin et Directeur peuvent toujours confirmer
        if ($user->isAdmin() || $user->isDirecteur()) {
            return true;
        }
        
        return $this->permissionService->canConfirmReception($user, $courrierSortant);
    }

    /**
     * Détermine si l'utilisateur peut imprimer le QR Code.
     */
    public function imprimerQr(User $user, CourrierSortant $courrierSortant): bool
    {
        return $this->permissionService->canView($user, $courrierSortant);
    }
}
