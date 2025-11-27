<?php

namespace App\Services;

use App\Models\User;
use App\Models\CourrierEntrant;
use App\Models\CourrierSortant;
use Illuminate\Database\Eloquent\Builder;

class PermissionService
{
    /**
     * Vérifie si un utilisateur peut voir un courrier
     */
    public function canView(User $user, $courrier): bool
    {
        // Admin peut tout voir
        if ($user->isAdmin()) {
            return true;
        }

        $niveau = $courrier->niveau_confidentialite;

        // Ordinaire et Urgent : visible par tous les agents autorisés
        if (in_array($niveau, ['ordinaire', 'urgent'])) {
            return $user->isAgentCourrier() || $user->isChefService() || $user->isDirecteur();
        }

        // Confidentiel : visible uniquement par le service concerné
        if ($niveau === 'confidentiel') {
            if ($courrier instanceof CourrierEntrant) {
                return $user->service_id === $courrier->destinataire_service_id
                    || $user->isChefService()
                    || $user->isDirecteur();
            } else {
                return $user->service_id === $courrier->provenance_service_id
                    || $user->isChefService()
                    || $user->isDirecteur();
            }
        }

        // Secret Défense : visible seulement par comptes certifiés (admin, directeur)
        if ($niveau === 'secret_defense') {
            return $user->isAdmin() || $user->isDirecteur();
        }

        return false;
    }

    /**
     * Vérifie si un utilisateur peut éditer un courrier
     */
    public function canEdit(User $user, $courrier): bool
    {
        // Admin peut tout éditer
        if ($user->isAdmin()) {
            return true;
        }

        // Seul le créateur ou un chef de service peut éditer
        if ($courrier->created_by === $user->id) {
            return true;
        }

        if ($user->isChefService()) {
            if ($courrier instanceof CourrierEntrant) {
                return $user->service_id === $courrier->destinataire_service_id;
            } else {
                return $user->service_id === $courrier->provenance_service_id;
            }
        }

        return false;
    }

    /**
     * Vérifie si un utilisateur peut supprimer un courrier
     */
    public function canDelete(User $user, $courrier): bool
    {
        // Seul l'admin peut supprimer
        return $user->isAdmin();
    }

    /**
     * Récupère les courriers entrants accessibles par un utilisateur
     */
    public function getAccessibleCourriers(User $user)
    {
        $query = CourrierEntrant::query();

        // Admin voit tout
        if ($user->isAdmin()) {
            return $query;
        }

        // Directeur voit tout
        if ($user->isDirecteur()) {
            return $query;
        }

        // Chef de service voit les courriers de son service
        if ($user->isChefService() && $user->service_id) {
            $query->where(function ($q) use ($user) {
                $q->where('destinataire_service_id', $user->service_id)
                  ->orWhere('niveau_confidentialite', 'ordinaire')
                  ->orWhere('niveau_confidentialite', 'urgent');
            });
        } else {
            // Agent courrier voit seulement ordinaire et urgent
            $query->whereIn('niveau_confidentialite', ['ordinaire', 'urgent']);
        }

        return $query;
    }

    /**
     * Vérifie si un utilisateur peut transmettre un courrier
     */
    public function canTransmit(User $user, $courrier): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Le créateur ou un chef de service peut transmettre
        if ($courrier->created_by === $user->id) {
            return true;
        }

        if ($user->isChefService()) {
            if ($courrier instanceof CourrierEntrant) {
                return $user->service_id === $courrier->destinataire_service_id;
            } elseif ($courrier instanceof CourrierSortant) {
                return $user->service_id === $courrier->provenance_service_id;
            }
        }

        return false;
    }

    /**
     * Vérifie si un utilisateur peut confirmer la réception/livraison d'un courrier
     */
    public function canConfirmReception(User $user, $courrier): bool
    {
        // Admin et Directeur peuvent toujours confirmer
        if ($user->isAdmin() || $user->isDirecteur()) {
            return true;
        }

        if ($courrier instanceof CourrierEntrant) {
            // Le destinataire interne, un chef de service du service destinataire, ou un agent courrier peut confirmer
            return ($user->id === $courrier->destinataire_personne_id || 
                   ($user->isChefService() && $user->service_id === $courrier->destinataire_service_id) ||
                   $user->isAgentCourrier());
        } elseif ($courrier instanceof CourrierSortant) {
            // Pour les courriers sortants, la confirmation est externe, mais un agent courrier ou chef de service peut la loguer
            return $user->isAgentCourrier() || $user->isChefService();
        }

        return false;
    }
}

