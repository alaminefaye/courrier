<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CourrierEntrant;
use App\Services\PermissionService;

class CourrierEntrantPolicy
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Détermine si l'utilisateur peut voir n'importe quel courrier entrant.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isDirecteur() || $user->isChefService() || $user->isAgentCourrier();
    }

    /**
     * Détermine si l'utilisateur peut voir le courrier entrant.
     */
    public function view(User $user, CourrierEntrant $courrierEntrant): bool
    {
        return $this->permissionService->canView($user, $courrierEntrant);
    }

    /**
     * Détermine si l'utilisateur peut créer des courriers entrants.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isAgentCourrier();
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour le courrier entrant.
     */
    public function update(User $user, CourrierEntrant $courrierEntrant): bool
    {
        // Admin et Directeur peuvent toujours modifier
        if ($user->isAdmin() || $user->isDirecteur()) {
            return true;
        }
        
        return $this->permissionService->canEdit($user, $courrierEntrant);
    }

    /**
     * Détermine si l'utilisateur peut supprimer le courrier entrant.
     */
    public function delete(User $user, CourrierEntrant $courrierEntrant): bool
    {
        return $this->permissionService->canDelete($user, $courrierEntrant);
    }

    /**
     * Détermine si l'utilisateur peut transmettre le courrier entrant.
     */
    public function transmettre(User $user, CourrierEntrant $courrierEntrant): bool
    {
        return $this->permissionService->canTransmit($user, $courrierEntrant);
    }

    /**
     * Détermine si l'utilisateur peut confirmer la réception du courrier entrant.
     */
    public function confirmerReception(User $user, CourrierEntrant $courrierEntrant): bool
    {
        // Admin et Directeur peuvent toujours confirmer
        if ($user->isAdmin() || $user->isDirecteur()) {
            return true;
        }
        
        return $this->permissionService->canConfirmReception($user, $courrierEntrant);
    }

    /**
     * Détermine si l'utilisateur peut imprimer le QR Code.
     */
    public function imprimerQr(User $user, CourrierEntrant $courrierEntrant): bool
    {
        return $this->permissionService->canView($user, $courrierEntrant);
    }
}
