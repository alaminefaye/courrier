<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Détermine si l'utilisateur peut voir n'importe quel utilisateur.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isDirecteur();
    }

    /**
     * Détermine si l'utilisateur peut voir l'utilisateur.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->isDirecteur() || $user->id === $model->id;
    }

    /**
     * Détermine si l'utilisateur peut créer des utilisateurs.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isDirecteur();
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour l'utilisateur.
     */
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->isDirecteur() || $user->id === $model->id;
    }

    /**
     * Détermine si l'utilisateur peut supprimer l'utilisateur.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }
}
