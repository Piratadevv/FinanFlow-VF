<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::select('id', 'username', 'full_name', 'email', 'last_login_at', 'last_login_ip', 'created_at')
            ->orderBy('created_at')
            ->get();

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|min:3|max:50|unique:users,username',
            'password' => 'required|string|min:8|confirmed',
            'full_name' => 'nullable|string|max:100',
        ], [
            'username.required' => 'Le nom d\'utilisateur est obligatoire.',
            'username.min' => 'Le nom d\'utilisateur doit contenir au moins 3 caractères.',
            'username.max' => 'Le nom d\'utilisateur ne peut pas dépasser 50 caractères.',
            'username.unique' => 'Ce nom d\'utilisateur existe déjà.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'full_name.max' => 'Le nom complet ne peut pas dépasser 100 caractères.',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'password' => $validated['password'],
            'full_name' => $validated['full_name'] ?? null,
        ]);

        AuditLogger::log(
            'CREATE',
            'auth',
            'MEDIUM',
            'Création d\'un utilisateur',
            'Utilisateur "' . $user->username . '" créé par ' . Auth::user()->username,
            'user',
            (string)$user->id
        );

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'last_login_at' => $user->last_login_at,
            'created_at' => $user->created_at,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
        ]);

        if (isset($validated['password'])) {
            $user->password = $validated['password'];
        }
        if (array_key_exists('full_name', $validated)) {
            $user->full_name = $validated['full_name'];
        }
        if (array_key_exists('email', $validated)) {
            $user->email = $validated['email'];
        }
        $user->save();

        AuditLogger::log(
            'UPDATE',
            'auth',
            'MEDIUM',
            'Modification d\'un utilisateur',
            'Utilisateur "' . $user->username . '" mis à jour',
            'user',
            (string)$user->id
        );

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'last_login_at' => $user->last_login_at,
            'created_at' => $user->created_at,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        if ((int)Auth::id() === $id) {
            return response()->json([
                'error' => 'Vous ne pouvez pas supprimer votre propre compte.'
            ], 403);
        }

        $user = User::findOrFail($id);
        $username = $user->username;
        $user->delete();

        AuditLogger::log(
            'DELETE',
            'auth',
            'HIGH',
            'Suppression d\'un utilisateur',
            'Utilisateur "' . $username . '" supprimé par ' . Auth::user()->username,
            'user',
            (string)$id
        );

        return response()->json(['success' => true]);
    }
}
