<?php

namespace Database\Seeders;

use App\Models\Escompte;
use Illuminate\Database\Seeder;

class EscompteSeeder extends Seeder
{
    public function run(): void
    {
        Escompte::create([
            'numero_effet' => 'EFF001',
            'nom_tireur' => 'Société ABC',
            'date_remise' => '2025-04-15',
            'libelle' => 'Escompte EFF001',
            'montant' => 45000.00,
            'taux_escompte' => 8.5,
            'frais_commission' => 450.00,
            'montant_net' => 44550.00,
            'statut' => 'ACTIF',
            'ordre_saisie' => 1,
        ]);

        Escompte::create([
            'numero_effet' => 'EFF002',
            'nom_tireur' => 'Entreprise XYZ',
            'date_remise' => '2025-05-20',
            'libelle' => 'Escompte EFF002',
            'montant' => 35000.00,
            'taux_escompte' => 7.2,
            'frais_commission' => 350.00,
            'montant_net' => 34650.00,
            'statut' => 'ACTIF',
            'ordre_saisie' => 2,
        ]);
    }
}
