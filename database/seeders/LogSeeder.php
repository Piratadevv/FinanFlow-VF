<?php

namespace Database\Seeders;

use App\Models\Log;
use Illuminate\Database\Seeder;

class LogSeeder extends Seeder
{
    public function run(): void
    {
        Log::create([
            'action' => 'CREATE',
            'category' => 'escompte',
            'severity' => 'info',
            'message' => 'Création d\'un nouvel escompte',
            'description' => 'Escompte EFF001 créé avec un montant de 45 000,00 DH',
            'entity_type' => 'escompte',
            'entity_id' => 'EFF001',
            'user_id' => 'USERtest',
            'changes' => ['after' => ['libelle' => 'Escompte EFF001', 'montant' => 45000]],
            'metadata' => ['ip' => '127.0.0.1', 'userAgent' => 'Mozilla/5.0'],
        ]);

        Log::create([
            'action' => 'UPDATE',
            'category' => 'escompte',
            'severity' => 'info',
            'message' => 'Modification d\'un escompte',
            'description' => 'Escompte EFF001 mis à jour',
            'entity_type' => 'escompte',
            'entity_id' => 'EFF001',
            'user_id' => 'USERtest',
            'changes' => [
                'before' => ['montant' => 40000],
                'after' => ['montant' => 45000],
            ],
            'metadata' => ['ip' => '127.0.0.1', 'userAgent' => 'Mozilla/5.0'],
        ]);

        Log::create([
            'action' => 'DELETE',
            'category' => 'escompte',
            'severity' => 'MEDIUM',
            'message' => 'Suppression d\'un escompte',
            'description' => 'Escompte supprimé',
            'entity_type' => 'escompte',
            'entity_id' => 'EFF003',
            'user_id' => 'USERtest',
            'changes' => ['before' => ['libelle' => 'Escompte Test', 'montant' => 10000]],
            'metadata' => ['ip' => '127.0.0.1'],
        ]);

        Log::create([
            'action' => 'CREATE',
            'category' => 'refinancement',
            'severity' => 'info',
            'message' => 'Création d\'un refinancement',
            'description' => 'Refinancement Crédit Immobilier créé',
            'entity_type' => 'refinancement',
            'entity_id' => 'REF001',
            'user_id' => 'abderrahmane',
            'changes' => ['after' => ['libelle' => 'Refinancement Crédit Immobilier', 'montant_refinance' => 60000]],
            'metadata' => ['ip' => '127.0.0.1'],
        ]);

        Log::create([
            'action' => 'LOGIN',
            'category' => 'auth',
            'severity' => 'info',
            'message' => 'Connexion utilisateur',
            'description' => 'USERtest s\'est connecté avec succès',
            'entity_type' => 'user',
            'user_id' => 'USERtest',
            'metadata' => ['ip' => '127.0.0.1', 'userAgent' => 'Mozilla/5.0'],
        ]);

        Log::create([
            'action' => 'EXPORT',
            'category' => 'data',
            'severity' => 'info',
            'message' => 'Export des escomptes',
            'description' => 'Export CSV des escomptes effectué',
            'entity_type' => 'escompte',
            'user_id' => 'USERtest',
            'metadata' => ['ip' => '127.0.0.1', 'format' => 'csv'],
        ]);
    }
}
