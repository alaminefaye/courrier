<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Direction;

class DirectionPolicy
{
    /**
     * Détermine si l'utilisateur peut voir n'importe quelle direction.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isDirecteur();
    }

    /**
     * Détermine si l'utilisateur peut voir la direction.
     */
    public function view(User $user, Direction $direction): bool
    {
        return $user->isAdmin() || $user->isDirecteur();
    }

    /**
     * Détermine si l'utilisateur peut créer des directions.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isDirecteur();
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour la direction.
     */
    public function update(User $user, Direction $direction): bool
    {
        return $user->isAdmin() || $user->isDirecteur();
    }

    /**
     * Détermine si l'utilisateur peut supprimer la direction.
     */
    public function delete(User $user, Direction $direction): bool
    {
        return $user->isAdmin();
    }
}
