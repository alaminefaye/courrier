<?php

namespace Database\Seeders;

use App\Models\Direction;
use Illuminate\Database\Seeder;

class DirectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $directions = [
            [
                'nom' => 'Direction Générale',
                'code' => 'DG',
                'description' => 'Direction Générale de l\'institution',
            ],
            [
                'nom' => 'Direction des Ressources Humaines',
                'code' => 'DRH',
                'description' => 'Direction chargée de la gestion du personnel',
            ],
            [
                'nom' => 'Direction Financière',
                'code' => 'DF',
                'description' => 'Direction chargée de la gestion financière',
            ],
            [
                'nom' => 'Direction Technique',
                'code' => 'DT',
                'description' => 'Direction chargée des aspects techniques',
            ],
            [
                'nom' => 'Direction Administrative',
                'code' => 'DA',
                'description' => 'Direction chargée de l\'administration',
            ],
        ];

        foreach ($directions as $direction) {
            Direction::updateOrCreate(
                ['code' => $direction['code']],
                $direction
            );
        }
    }
}
