<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Escompte;
use App\Models\Refinancement;
use App\Models\Log;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ParametresController extends Controller
{
    public function index()
    {
        $config = Configuration::current();
        $cumulEscomptes = (float) Escompte::sum('montant');
        $cumulRefinancements = (float) Refinancement::sum('montant_refinance');
        $cumulGlobal = $cumulEscomptes + $cumulRefinancements;
        $autorisation = (float) $config->autorisation_bancaire;

        $users = User::select('id', 'username', 'full_name', 'email', 'last_login_at', 'last_login_ip', 'created_at')
            ->orderBy('created_at')
            ->get();

        $loginLogs = Log::where('action', 'LOGIN')
            ->orderByDesc('timestamp')
            ->limit(10)
            ->get();

        $dataStats = [
            'escomptes' => [
                'count' => Escompte::count(),
                'lastModified' => Escompte::max('updated_at'),
            ],
            'refinancements' => [
                'count' => Refinancement::count(),
                'lastModified' => Refinancement::max('updated_at'),
            ],
            'logs' => [
                'count' => Log::count(),
                'lastModified' => Log::max('timestamp'),
            ],
        ];

        return view('parametres.index', [
            'config' => $config,
            'cumulEscomptes' => $cumulEscomptes,
            'cumulRefinancements' => $cumulRefinancements,
            'cumulGlobal' => $cumulGlobal,
            'autorisation' => $autorisation,
            'users' => $users,
            'loginLogs' => $loginLogs,
            'dataStats' => $dataStats,
            'currentUser' => Auth::user(),
        ]);
    }
}