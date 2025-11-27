<?php

namespace App\Services;

use App\Models\CourrierEntrant;
use App\Models\CourrierSortant;
use Carbon\Carbon;

class NimGeneratorService
{
    /**
     * Génère un NIM pour un courrier entrant
     * Format: ARR-YYYY-XXXXX
     */
    public function generateEntrant(): string
    {
        $year = Carbon::now()->year;
        $prefix = "ARR-{$year}-";
        
        // Récupérer le dernier NIM de l'année
        $lastNim = CourrierEntrant::where('nim', 'like', $prefix . '%')
            ->orderBy('nim', 'desc')
            ->value('nim');
        
        if ($lastNim) {
            // Extraire le numéro séquentiel
            $lastNumber = (int) substr($lastNim, -5);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Génère un NIM pour un courrier sortant
     * Format: DEP-YYYY-XXXXX
     */
    public function generateSortant(): string
    {
        $year = Carbon::now()->year;
        $prefix = "DEP-{$year}-";
        
        // Récupérer le dernier NIM de l'année
        $lastNim = CourrierSortant::where('nim', 'like', $prefix . '%')
            ->orderBy('nim', 'desc')
            ->value('nim');
        
        if ($lastNim) {
            // Extraire le numéro séquentiel
            $lastNumber = (int) substr($lastNim, -5);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}

