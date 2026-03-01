<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255|unique:users,email,' . Auth::id(),
        ], [
            'full_name.max' => 'Le nom complet ne peut pas dépasser 100 caractères.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
        ]);

        $user = Auth::user();
        $user->update($validated);

        AuditLogger::log(
            'UPDATE',
            'auth',
            'LOW',
            'Mise à jour du profil',
            $user->username . ' a mis à jour son profil',
            'user',
            (string)$user->id
        );

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'errors' => ['current_password' => ['Le mot de passe actuel est incorrect.']]
            ], 422);
        }

        $user->update(['password' => $validated['password']]);

        AuditLogger::log(
            'UPDATE',
            'auth',
            'MEDIUM',
            'Changement de mot de passe',
            $user->username . ' a changé son mot de passe',
            'user',
            (string)$user->id
        );

        return response()->json(['success' => true]);
    }
}
