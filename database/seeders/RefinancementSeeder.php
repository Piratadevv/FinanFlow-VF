<?php

namespace Database\Seeders;

use App\Models\Refinancement;
use Illuminate\Database\Seeder;

class RefinancementSeeder extends Seeder
{
    public function run(): void
    {
        Refinancement::create([
            'libelle' => 'Refinancement Crédit Immobilier',
            'montant_refinance' => 60000.00,
            'taux_interet' => 10.00,
            'date_refinancement' => '2025-03-09',
            'duree_en_mois' => 12,
            'encours_refinance' => 60000.00,
            'frais_dossier' => 500.00,
            'conditions' => 'Garantie hypothécaire requise',
            'statut' => 'ACTIF',
            'total_interets' => 6000.00,
            'ordre_saisie' => 1,
        ]);

        Refinancement::create([
            'libelle' => 'Refinancement Crédit Auto',
            'montant_refinance' => 40000.00,
            'taux_interet' => 12.00,
            'date_refinancement' => '2025-03-15',
            'duree_en_mois' => 24,
            'encours_refinance' => 40000.00,
            'frais_dossier' => 300.00,
            'conditions' => 'Véhicule en garantie',
            'statut' => 'ACTIF',
            'total_interets' => 9600.00,
            'ordre_saisie' => 2,
        ]);
    }
}
