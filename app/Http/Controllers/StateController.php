<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Escompte;
use App\Models\Refinancement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StateController extends Controller
{
    public function saveState(Request $request): JsonResponse
    {
        $data = $request->validate([
            'escomptes' => 'sometimes|array',
            'refinancements' => 'sometimes|array',
            'configuration' => 'sometimes|array',
        ]);

        if (isset($data['configuration'])) {
            $config = Configuration::current();
            if (isset($data['configuration']['autorisation_bancaire']) || isset($data['configuration']['autorisationBancaire'])) {
                $config->update([
                    'autorisation_bancaire' => $data['configuration']['autorisation_bancaire']
                        ?? $data['configuration']['autorisationBancaire']
                ]);
            }
        }

        if (isset($data['escomptes'])) {
            Escompte::truncate();
            foreach ($data['escomptes'] as $escompteData) {
                Escompte::create($escompteData);
            }
        }

        if (isset($data['refinancements'])) {
            Refinancement::truncate();
            foreach ($data['refinancements'] as $refinancementData) {
                Refinancement::create($refinancementData);
            }
        }

        return response()->json(['success' => true]);
    }

    public function currentState(): JsonResponse
    {
        return response()->json([
            'escomptes' => Escompte::orderBy('ordre_saisie')->get(),
            'refinancements' => Refinancement::orderBy('ordre_saisie')->get(),
            'configuration' => Configuration::current(),
            'summary' => [
                'total_escomptes' => Escompte::count(),
                'total_refinancements' => Refinancement::count(),
                'autorisation_bancaire' => (float) Configuration::current()->autorisation_bancaire,
            ],
        ]);
    }
}
