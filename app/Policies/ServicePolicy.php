<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Service;

class ServicePolicy
{
    /**
     * Détermine si l'utilisateur peut voir n'importe quel service.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isDirecteur() || $user->isChefService();
    }

    /**
     * Détermine si l'utilisateur peut voir le service.
     */
    public function view(User $user, Service $service): bool
    {
        return $user->isAdmin() || $user->isDirecteur() || 
               ($user->isChefService() && $user->service_id === $service->id);
    }

    /**
     * Détermine si l'utilisateur peut créer des services.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isDirecteur();
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour le service.
     */
    public function update(User $user, Service $service): bool
    {
        return $user->isAdmin() || $user->isDirecteur() ||
               ($user->isChefService() && $user->service_id === $service->id);
    }

    /**
     * Détermine si l'utilisateur peut supprimer le service.
     */
    public function delete(User $user, Service $service): bool
    {
        return $user->isAdmin();
    }
}
