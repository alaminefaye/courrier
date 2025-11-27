<?php

namespace App\Console\Commands;

use App\Models\CourrierEntrant;
use App\Models\CourrierSortant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ArchiveCourriers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courriers:archive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive automatiquement les courriers reçus/confirmés depuis X jours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $joursAvantArchivage = config('courrier.archive.jours_avant_archivage', 90);
        $dateLimite = now()->subDays($joursAvantArchivage);

        $this->info("Archivage des courriers avant le {$dateLimite->format('d/m/Y')}...");

        // Courriers entrants reçus
        $entrants = CourrierEntrant::where('statut', 'recu')
            ->where('date_arrivee', '<=', $dateLimite)
            ->get();

        $countEntrants = 0;
        foreach ($entrants as $courrier) {
            // Marquer comme archivé (on pourrait ajouter un champ archived_at)
            // Pour l'instant, on peut juste déplacer les fichiers
            if ($courrier->fichier_joint) {
                $oldPath = $courrier->fichier_joint;
                $newPath = 'archives/' . basename($oldPath);
                
                if (Storage::disk('private')->exists($oldPath)) {
                    Storage::disk('private')->move($oldPath, $newPath);
                    $courrier->update(['fichier_joint' => $newPath]);
                }
            }
            $countEntrants++;
        }

        // Courriers sortants confirmés
        $sortants = CourrierSortant::where('statut', 'confirme')
            ->where('date_depart', '<=', $dateLimite)
            ->get();

        $countSortants = 0;
        foreach ($sortants as $courrier) {
            if ($courrier->fichier_joint) {
                $oldPath = $courrier->fichier_joint;
                $newPath = 'archives/' . basename($oldPath);
                
                if (Storage::disk('private')->exists($oldPath)) {
                    Storage::disk('private')->move($oldPath, $newPath);
                    $courrier->update(['fichier_joint' => $newPath]);
                }
            }
            $countSortants++;
        }

        $this->info("✓ {$countEntrants} courriers entrants archivés");
        $this->info("✓ {$countSortants} courriers sortants archivés");
        $this->info("Archivage terminé !");
    }
}
