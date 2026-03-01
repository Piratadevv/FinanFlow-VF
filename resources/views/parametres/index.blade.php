@extends('layouts.app')
@section('content')
    <div x-data="parametresPage()" x-init="init()">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Paramètres</h1>

        {{-- Mobile tab selector --}}
        <div class="lg:hidden mb-4 overflow-x-auto">
            <div class="flex gap-2 pb-2" style="min-width:max-content;">
                <template x-for="tab in tabs" :key="tab.id">
                    <button @click="activeTab = tab.id"
                        class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all"
                        :class="activeTab === tab.id ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                        x-text="tab.label"></button>
                </template>
            </div>
        </div>

        <div class="flex gap-6">
            {{-- Desktop tab sidebar --}}
            <div class="hidden lg:block w-64 flex-shrink-0">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-2 sticky top-24">
                    <template x-for="tab in tabs" :key="tab.id">
                        <button @click="activeTab = tab.id"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all text-left"
                            :class="activeTab === tab.id ? 'bg-blue-50 text-blue-700 border-l-4 border-blue-600' : 'text-gray-600 hover:bg-gray-50 border-l-4 border-transparent'">
                            <span x-html="tab.icon"></span>
                            <span x-text="tab.label"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Content area --}}
            <div class="flex-1 min-w-0">

                {{-- TAB 1: Configuration Bancaire --}}
                <div x-show="activeTab === 'config'" x-cloak>
                    <div class="space-y-6">
                        {{-- Autorisation Bancaire --}}
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Autorisation Bancaire</h2>
                            <p class="text-sm text-gray-500 mb-2">Limite d'autorisation bancaire actuelle</p>
                            <p class="text-3xl font-bold text-gray-900 mb-6"
                                x-text="formatMontant(configForm.autorisation_bancaire)"></p>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nouvelle autorisation
                                        (DH)</label>
                                    <input type="number" x-model="configForm.autorisation_bancaire" step="0.01" min="0"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div class="p-4 rounded-xl text-sm"
                                    :class="configForm.autorisation_bancaire < stats.cumulGlobal ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-blue-50 border border-blue-200 text-blue-700'">
                                    <p class="font-medium mb-2">Impact de la modification</p>
                                    <p>Nouveau cumul autorisé : <span class="font-semibold"
                                            x-text="formatMontant(configForm.autorisation_bancaire)"></span></p>
                                    <p>Nouvel encours restant : <span class="font-semibold"
                                            x-text="formatMontant(configForm.autorisation_bancaire - stats.cumulGlobal)"></span>
                                    </p>
                                    <p>Nouveau taux d'utilisation : <span class="font-semibold"
                                            x-text="(configForm.autorisation_bancaire > 0 ? Math.round((stats.cumulGlobal / configForm.autorisation_bancaire) * 100) : 0) + ' %'"></span>
                                    </p>
                                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full transition-all"
                                            :class="impactPct > 90 ? 'bg-red-500' : impactPct > 70 ? 'bg-orange-500' : 'bg-blue-500'"
                                            :style="'width:' + Math.min(impactPct, 100) + '%'"></div>
                                    </div>
                                    <p x-show="configForm.autorisation_bancaire < stats.cumulGlobal"
                                        class="mt-2 font-semibold text-red-600">⚠️ L'autorisation est inférieure au cumul
                                        actuel !</p>
                                </div>

                                <div x-show="configForm.autorisation_bancaire < 1000 || configForm.autorisation_bancaire > 10000000"
                                    class="p-3 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-xl text-sm">
                                    ⚠️ La valeur doit être entre 1 000 DH et 10 000 000 DH
                                </div>

                                <button @click="saveConfig()" :disabled="configSubmitting"
                                    class="px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition disabled:opacity-50">
                                    <span x-show="!configSubmitting">Enregistrer la modification</span>
                                    <span x-show="configSubmitting">Enregistrement...</span>
                                </button>
                            </div>
                        </div>

                        {{-- Statistiques --}}
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Statistiques d'Utilisation</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div class="p-4 bg-gray-50 rounded-xl">
                                    <p class="text-xs text-gray-500 mb-1">Cumul Escomptes</p>
                                    <p class="text-lg font-semibold" x-text="formatMontant(stats.cumulEscomptes)"></p>
                                </div>
                                <div class="p-4 bg-gray-50 rounded-xl">
                                    <p class="text-xs text-gray-500 mb-1">Cumul Refinancements</p>
                                    <p class="text-lg font-semibold" x-text="formatMontant(stats.cumulRefinancements)"></p>
                                </div>
                                <div class="p-4 bg-gray-50 rounded-xl">
                                    <p class="text-xs text-gray-500 mb-1">Cumul Global</p>
                                    <p class="text-lg font-semibold" x-text="formatMontant(stats.cumulGlobal)"></p>
                                </div>
                                <div class="p-4 bg-gray-50 rounded-xl">
                                    <p class="text-xs text-gray-500 mb-1">Encours Restant</p>
                                    <p class="text-lg font-semibold"
                                        :class="stats.encoursRestant >= 0 ? 'text-green-600' : 'text-red-600'"
                                        x-text="formatMontant(stats.encoursRestant)"></p>
                                </div>
                                <div class="p-4 bg-gray-50 rounded-xl">
                                    <p class="text-xs text-gray-500 mb-1">Taux d'utilisation</p>
                                    <p class="text-lg font-semibold" x-text="stats.tauxUtilisation + ' %'"></p>
                                    <div class="mt-1 w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full"
                                            :class="stats.tauxUtilisation > 90 ? 'bg-red-500' : stats.tauxUtilisation > 70 ? 'bg-orange-500' : 'bg-blue-500'"
                                            :style="'width:'+Math.min(stats.tauxUtilisation,100)+'%'"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Zone Dangereuse --}}
                        <div class="bg-white rounded-xl shadow-sm border-2 border-red-200 p-6">
                            <div class="flex items-center gap-2 mb-2"><svg class="w-5 h-5 text-red-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                <h2 class="text-lg font-semibold text-red-700">Zone Dangereuse</h2>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">Réinitialiser toutes les données de l'application à leur
                                état initial. Cette action est irréversible.</p>
                            <button @click="resetStep = 1"
                                class="px-4 py-2 border-2 border-red-500 text-red-600 text-sm font-medium rounded-xl hover:bg-red-50 transition">Réinitialiser
                                l'application</button>
                        </div>
                    </div>
                </div>
                {{-- TAB 2: Mon Compte --}}
                <div x-show="activeTab === 'account'" x-cloak>
                    <div class="space-y-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informations du Profil</h2>
                            <div class="space-y-4">
                                <div><label class="block text-sm font-medium text-gray-700 mb-1">Nom d'utilisateur</label>
                                    <p class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600"
                                        x-text="currentUser.username"></p>
                                </div>
                                <div><label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label><input
                                        type="text" x-model="profileForm.full_name"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500"
                                        placeholder="Votre nom complet">
                                    <p x-show="profileErrors.full_name" class="mt-1 text-xs text-red-600"
                                        x-text="profileErrors.full_name"></p>
                                </div>
                                <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input
                                        type="email" x-model="profileForm.email"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500"
                                        placeholder="votre@email.com">
                                    <p x-show="profileErrors.email" class="mt-1 text-xs text-red-600"
                                        x-text="profileErrors.email"></p>
                                </div>
                                <button @click="saveProfile()" :disabled="profileSubmitting"
                                    class="px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition disabled:opacity-50"><span
                                        x-show="!profileSubmitting">Mettre à jour le profil</span><span
                                        x-show="profileSubmitting">Enregistrement...</span></button>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Changer le Mot de Passe</h2>
                            <div class="space-y-4">
                                <div class="relative"><label class="block text-sm font-medium text-gray-700 mb-1">Mot de
                                        passe actuel</label><input :type="showCurrentPw ? 'text' : 'password'"
                                        x-model="passwordForm.current_password"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 pr-10"><button
                                        type="button" @click="showCurrentPw = !showCurrentPw"
                                        class="absolute right-3 top-8 text-gray-400 hover:text-gray-600"><svg
                                            class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg></button>
                                    <p x-show="passwordErrors.current_password" class="mt-1 text-xs text-red-600"
                                        x-text="passwordErrors.current_password"></p>
                                </div>
                                <div class="relative"><label class="block text-sm font-medium text-gray-700 mb-1">Nouveau
                                        mot de passe</label><input :type="showNewPw ? 'text' : 'password'"
                                        x-model="passwordForm.password"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 pr-10"><button
                                        type="button" @click="showNewPw = !showNewPw"
                                        class="absolute right-3 top-8 text-gray-400 hover:text-gray-600"><svg
                                            class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg></button>
                                    <div class="mt-2 h-1.5 rounded-full"
                                        :class="pwStrength === 0 ? 'bg-gray-200' : pwStrength === 1 ? 'bg-red-400' : pwStrength === 2 ? 'bg-orange-400' : 'bg-green-500'"
                                        :style="'width:' + (pwStrength === 0 ? 100 : pwStrength * 33) + '%'"></div>
                                    <p class="text-xs mt-1"
                                        :class="pwStrength <= 1 ? 'text-red-500' : pwStrength === 2 ? 'text-orange-500' : 'text-green-600'"
                                        x-text="pwStrength === 0 ? '' : pwStrength === 1 ? 'Faible' : pwStrength === 2 ? 'Moyen' : 'Fort'">
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">Min. 8 caractères, au moins un chiffre</p>
                                    <p x-show="passwordErrors.password" class="mt-1 text-xs text-red-600"
                                        x-text="passwordErrors.password"></p>
                                </div>
                                <div><label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le nouveau mot de
                                        passe</label><input :type="showNewPw ? 'text' : 'password'"
                                        x-model="passwordForm.password_confirmation"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                                <button @click="changePassword()" :disabled="pwSubmitting"
                                    class="px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition disabled:opacity-50"><span
                                        x-show="!pwSubmitting">Changer le mot de passe</span><span
                                        x-show="pwSubmitting">Enregistrement...</span></button>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informations de Session</h2>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between py-2 border-b border-gray-100"><span
                                        class="text-gray-500">Connecté en tant que</span><span class="font-medium"
                                        x-text="currentUser.username"></span></div>
                                <div class="flex justify-between py-2 border-b border-gray-100"><span
                                        class="text-gray-500">Dernière connexion</span><span class="font-medium"
                                        x-text="currentUser.last_login_at ? formatDateTime(currentUser.last_login_at) : 'Première connexion'"></span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100"><span
                                        class="text-gray-500">Adresse IP</span><span class="font-medium"
                                        x-text="currentUser.last_login_ip || '—'"></span></div>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="mt-4">@csrf<button type="submit"
                                    class="px-4 py-2 border-2 border-red-500 text-red-600 text-sm font-medium rounded-xl hover:bg-red-50 transition">Se
                                    déconnecter</button></form>
                        </div>
                    </div>
                </div>

                {{-- TAB 3: Utilisateurs --}}
                <div x-show="activeTab === 'users'" x-cloak>
                    <div class="space-y-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Liste des Utilisateurs</h2>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-200">
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                                Nom d'utilisateur</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                                Nom complet</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                                Dernière connexion</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                                Statut</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <template x-for="(user, idx) in usersList" :key="user.id">
                                            <tr class="hover:bg-blue-50/30">
                                                <td class="px-4 py-3 text-gray-600" x-text="idx + 1"></td>
                                                <td class="px-4 py-3 font-medium text-gray-800" x-text="user.username"></td>
                                                <td class="px-4 py-3 text-gray-600" x-text="user.full_name || '—'"></td>
                                                <td class="px-4 py-3 text-gray-600"
                                                    x-text="user.last_login_at ? relativeTime(user.last_login_at) : 'Jamais'">
                                                </td>
                                                <td class="px-4 py-3"><span
                                                        class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Actif</span>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <div class="flex items-center justify-end gap-1">
                                                        <button @click="openEditUser(user)"
                                                            class="p-1.5 rounded-lg hover:bg-blue-100 text-gray-400 hover:text-blue-600 transition"
                                                            title="Modifier"><svg class="w-4 h-4" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg></button>
                                                        <button @click="deleteUser(user)"
                                                            :disabled="user.id == currentUser.id"
                                                            class="p-1.5 rounded-lg hover:bg-red-100 text-gray-400 hover:text-red-600 transition disabled:opacity-30 disabled:cursor-not-allowed"
                                                            :title="user.id == currentUser.id ? 'Vous ne pouvez pas supprimer votre propre compte' : 'Supprimer'"><svg
                                                                class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4"
                                x-text="editingUser ? 'Modifier l\'utilisateur' : 'Ajouter un Utilisateur'"></h2>
                            <div class="space-y-4">
                                <div x-show="!editingUser"><label class="block text-sm font-medium text-gray-700 mb-1">Nom
                                        d'utilisateur <span class="text-red-500">*</span></label><input type="text"
                                        x-model="userForm.username"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500"
                                        placeholder="ex: admin01">
                                    <p x-show="userErrors.username" class="mt-1 text-xs text-red-600"
                                        x-text="userErrors.username"></p>
                                </div>
                                <div><label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label><input
                                        type="text" x-model="userForm.full_name"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500"
                                        placeholder="Nom et prénom"></div>
                                <div><label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe <span
                                            x-show="!editingUser" class="text-red-500">*</span></label><input
                                        type="password" x-model="userForm.password"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500"
                                        :placeholder="editingUser ? 'Laisser vide pour ne pas changer' : 'Min. 8 caractères'">
                                    <p x-show="userErrors.password" class="mt-1 text-xs text-red-600"
                                        x-text="userErrors.password"></p>
                                </div>
                                <div><label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de
                                        passe</label><input type="password" x-model="userForm.password_confirmation"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="flex gap-3">
                                    <button @click="submitUser()" :disabled="userSubmitting"
                                        class="px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition disabled:opacity-50"><span
                                            x-show="!userSubmitting"
                                            x-text="editingUser ? 'Modifier' : 'Créer l\'utilisateur'"></span><span
                                            x-show="userSubmitting">Enregistrement...</span></button>
                                    <button x-show="editingUser" @click="cancelEditUser()"
                                        class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition">Annuler</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- TAB 4: Sécurité --}}
                <div x-show="activeTab === 'security'" x-cloak>
                    <div class="space-y-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Politique de Mot de Passe</h2>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between py-2 border-b border-gray-100"><span
                                        class="text-gray-500">Longueur minimale</span><span class="font-medium">8
                                        caractères</span></div>
                                <div class="flex justify-between py-2 border-b border-gray-100"><span
                                        class="text-gray-500">Expiration</span><span class="font-medium">Jamais</span></div>
                                <div class="flex justify-between py-2 border-b border-gray-100"><span
                                        class="text-gray-500">Tentatives max avant blocage</span><span
                                        class="font-medium">5</span></div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Historique des Connexions</h2>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-200">
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                                Date & Heure</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                                Utilisateur</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                                Adresse IP</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                                Résultat</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <template x-for="log in loginLogs" :key="log.id">
                                            <tr class="hover:bg-blue-50/30">
                                                <td class="px-4 py-3 text-gray-600" x-text="formatDateTime(log.timestamp)">
                                                </td>
                                                <td class="px-4 py-3 font-medium text-gray-800" x-text="log.user_id || '—'">
                                                </td>
                                                <td class="px-4 py-3 text-gray-600" x-text="log.metadata?.ip || '—'"></td>
                                                <td class="px-4 py-3"><span
                                                        class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Succès</span>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="loginLogs.length === 0">
                                            <tr>
                                                <td colspan="4" class="px-4 py-8 text-center text-gray-400">Aucune connexion
                                                    enregistrée</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Sessions Actives</h2>
                            <p class="text-sm text-gray-600 mb-4"><span class="inline-flex items-center gap-1.5"><span
                                        class="w-2 h-2 bg-green-500 rounded-full"></span> 1 session active</span></p>
                            <button @click="logoutOthers()"
                                class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-50 transition">Terminer
                                toutes les autres sessions</button>
                        </div>

                    </div>
                </div>

                {{-- TAB 5: Système --}}
                <div x-show="activeTab === 'system'" x-cloak>
                    <div class="space-y-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Affichage</h2>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between py-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Thème</p>
                                        <p class="text-xs text-gray-400">Light / Dark</p>
                                    </div>
                                    <button @click="prefs.theme = prefs.theme === 'light' ? 'dark' : 'light'; savePrefs()"
                                        class="relative w-12 h-6 rounded-full transition-colors"
                                        :class="prefs.theme === 'dark' ? 'bg-blue-600' : 'bg-gray-300'"><span
                                            class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform"
                                            :class="prefs.theme === 'dark' ? 'translate-x-6' : ''"></span></button>
                                </div>
                                <div class="flex items-center justify-between py-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Format des montants</p>
                                        <p class="text-xs text-gray-400">Français ou International</p>
                                    </div>
                                    <select x-model="prefs.moneyFormat" @change="savePrefs()"
                                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                                        <option value="fr">45 000,00 DH</option>
                                        <option value="intl">45,000.00 DH</option>
                                    </select>
                                </div>
                                <div class="flex items-center justify-between py-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Format des dates</p>
                                        <p class="text-xs text-gray-400">Choisir le format d'affichage</p>
                                    </div>
                                    <select x-model="prefs.dateFormat" @change="savePrefs()"
                                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                                        <option value="dd/MM/yyyy">dd/MM/yyyy</option>
                                        <option value="yyyy-MM-dd">yyyy-MM-dd</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Comportement</h2>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between py-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Actualisation automatique du dashboard
                                        </p>
                                    </div>
                                    <button @click="prefs.autoRefresh = !prefs.autoRefresh; savePrefs()"
                                        class="relative w-12 h-6 rounded-full transition-colors"
                                        :class="prefs.autoRefresh ? 'bg-blue-600' : 'bg-gray-300'"><span
                                            class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform"
                                            :class="prefs.autoRefresh ? 'translate-x-6' : ''"></span></button>
                                </div>
                                <div x-show="prefs.autoRefresh" class="pl-4">
                                    <label class="text-sm text-gray-600">Intervalle :</label>
                                    <select x-model="prefs.refreshInterval" @change="savePrefs()"
                                        class="ml-2 px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                                        <option value="30">30 secondes</option>
                                        <option value="60">1 minute</option>
                                        <option value="300">5 minutes</option>
                                    </select>
                                </div>
                                <div class="flex items-center justify-between py-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Confirmation avant suppression</p>
                                    </div>
                                    <button @click="prefs.confirmDelete = !prefs.confirmDelete; savePrefs()"
                                        class="relative w-12 h-6 rounded-full transition-colors"
                                        :class="prefs.confirmDelete ? 'bg-blue-600' : 'bg-gray-300'"><span
                                            class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform"
                                            :class="prefs.confirmDelete ? 'translate-x-6' : ''"></span></button>
                                </div>
                                <div class="flex items-center justify-between py-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Colonnes optionnelles (N° Effet, Nom
                                            Tireur)</p>
                                    </div>
                                    <button @click="prefs.showOptionalCols = !prefs.showOptionalCols; savePrefs()"
                                        class="relative w-12 h-6 rounded-full transition-colors"
                                        :class="prefs.showOptionalCols ? 'bg-blue-600' : 'bg-gray-300'"><span
                                            class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform"
                                            :class="prefs.showOptionalCols ? 'translate-x-6' : ''"></span></button>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Langue & Région</h2>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between py-2 border-b border-gray-100"><span
                                        class="text-gray-500">Langue</span><span class="font-medium">Français (fr)</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100"><span
                                        class="text-gray-500">Devise</span><span class="font-medium">Dirham Marocain (DH /
                                        MAD)</span></div>
                                <div class="flex justify-between py-2 border-b border-gray-100"><span
                                        class="text-gray-500">Fuseau
                                        horaire</span><span class="font-medium">Africa/Casablanca (UTC+1)</span></div>
                                <div class="flex justify-between py-2"><span class="text-gray-500">Format
                                        numérique</span><span class="font-medium">Français (espace milliers, virgule
                                        décimale)</span></div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- TAB 6: Données --}}
                <div x-show="activeTab === 'data'" x-cloak>
                    <div class="space-y-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Export Global</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="border border-gray-200 rounded-xl p-4">
                                    <h3 class="font-medium text-gray-800 mb-1">Exporter les Escomptes</h3>
                                    <p class="text-xs text-gray-500 mb-3">Exporter tous les escomptes en CSV ou Excel</p>
                                    <div class="flex gap-2 mb-3"><label class="flex items-center gap-1.5 text-sm"><input
                                                type="radio" x-model="exportEsFormat" value="csv" class="text-blue-600">
                                            CSV</label><label class="flex items-center gap-1.5 text-sm"><input type="radio"
                                                x-model="exportEsFormat" value="xlsx" class="text-blue-600"> Excel</label>
                                    </div>
                                    <a :href="'/api/escomptes/export?format=' + exportEsFormat"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-xl hover:bg-green-700 transition">Télécharger</a>
                                </div>
                                <div class="border border-gray-200 rounded-xl p-4">
                                    <h3 class="font-medium text-gray-800 mb-1">Exporter les Refinancements</h3>
                                    <p class="text-xs text-gray-500 mb-3">Exporter tous les refinancements</p>
                                    <div class="flex gap-2 mb-3"><label class="flex items-center gap-1.5 text-sm"><input
                                                type="radio" x-model="exportRefFormat" value="csv" class="text-blue-600">
                                            CSV</label><label class="flex items-center gap-1.5 text-sm"><input type="radio"
                                                x-model="exportRefFormat" value="xlsx" class="text-blue-600"> Excel</label>
                                    </div>
                                    <a :href="'/api/refinancements/export?format=' + exportRefFormat"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-xl hover:bg-green-700 transition">Télécharger</a>
                                </div>
                                <div class="border border-gray-200 rounded-xl p-4">
                                    <h3 class="font-medium text-gray-800 mb-1">Exporter les Logs</h3>
                                    <p class="text-xs text-gray-500 mb-3">Exporter l'historique complet des logs</p>
                                    <p class="text-xs text-gray-400 mb-3">Format : CSV uniquement</p>
                                    <a href="/api/logs/export"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-xl hover:bg-green-700 transition">Télécharger</a>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Statistiques des Données</h2>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-200">
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                                Entité</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                                Enregistrements</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                                Dernière modification</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-800">Escomptes</td>
                                            <td class="px-4 py-3 text-gray-600" x-text="dataStats.escomptes.count"></td>
                                            <td class="px-4 py-3 text-gray-600"
                                                x-text="dataStats.escomptes.lastModified ? formatDateTime(dataStats.escomptes.lastModified) : '—'">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-800">Refinancements</td>
                                            <td class="px-4 py-3 text-gray-600" x-text="dataStats.refinancements.count">
                                            </td>
                                            <td class="px-4 py-3 text-gray-600"
                                                x-text="dataStats.refinancements.lastModified ? formatDateTime(dataStats.refinancements.lastModified) : '—'">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-800">Logs</td>
                                            <td class="px-4 py-3 text-gray-600" x-text="dataStats.logs.count"></td>
                                            <td class="px-4 py-3 text-gray-600"
                                                x-text="dataStats.logs.lastModified ? formatDateTime(dataStats.logs.lastModified) : '—'">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm border-2 border-red-200 p-6">
                            <div class="flex items-center gap-2 mb-2"><svg class="w-5 h-5 text-red-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                <h2 class="text-lg font-semibold text-red-700">Purge des Logs</h2>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">Supprimer tous les logs de l'application. Cette action est
                                irréversible.</p>
                            <button @click="purgeStep = 1"
                                class="px-4 py-2 border-2 border-red-500 text-red-600 text-sm font-medium rounded-xl hover:bg-red-50 transition">Purger
                                les logs</button>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Reset confirmation modal --}}
            <div x-show="resetStep > 0" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50" @click="resetStep = 0"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
                    <template x-if="resetStep === 1">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Êtes-vous sûr ?</h3>
                            <p class="text-sm text-gray-600 mb-6">Cette action va réinitialiser toutes les données. Elle est
                                irréversible.</p>
                            <div class="flex justify-end gap-3"><button @click="resetStep = 0"
                                    class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200">Annuler</button><button
                                    @click="resetStep = 2"
                                    class="px-4 py-2.5 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700">Continuer</button>
                            </div>
                        </div>
                    </template>
                    <template x-if="resetStep === 2">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Confirmation finale</h3>
                            <p class="text-sm text-gray-600 mb-4">Tapez <strong>RÉINITIALISER</strong> pour confirmer</p>
                            <input type="text" x-model="resetConfirmText"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm mb-4"
                                placeholder="RÉINITIALISER">
                            <div class="flex justify-end gap-3"><button @click="resetStep = 0"
                                    class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200">Annuler</button><button
                                    @click="doReset()" :disabled="resetConfirmText !== 'RÉINITIALISER'"
                                    class="px-4 py-2.5 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700 disabled:opacity-50">Confirmer
                                    la réinitialisation</button></div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Purge logs modal --}}
            <div x-show="purgeStep > 0" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50" @click="purgeStep = 0"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Purger tous les logs</h3>
                    <p class="text-sm text-gray-600 mb-4">Tapez <strong>PURGER</strong> pour confirmer la suppression de
                        tous
                        les logs</p>
                    <input type="text" x-model="purgeConfirmText"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm mb-4" placeholder="PURGER">
                    <div class="flex justify-end gap-3"><button @click="purgeStep = 0"
                            class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200">Annuler</button><button
                            @click="doPurge()" :disabled="purgeConfirmText !== 'PURGER'"
                            class="px-4 py-2.5 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700 disabled:opacity-50">Purger
                            les logs</button></div>
                </div>
            </div>

            {{-- Edit user modal --}}
            <div x-show="showEditUserModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50" @click="showEditUserModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Modifier l'utilisateur</h3>
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label><input
                                type="text" x-model="editUserForm.full_name"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input type="email"
                                x-model="editUserForm.email"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label><input
                                type="password" x-model="editUserForm.password"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500"
                                placeholder="Laisser vide pour ne pas changer"></div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6"><button @click="showEditUserModal = false"
                            class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200">Annuler</button><button
                            @click="saveEditUser()"
                            class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700">Enregistrer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    @push('scripts')
        <script>
            function parametresPage() {
                return {
                    activeTab: 'config',
                    tabs: [
                        { id: 'config', label: 'Configuration', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>' },
                        { id: 'account', label: 'Mon Compte', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>' },
                        { id: 'users', label: 'Utilisateurs', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>' },
                        { id: 'security', label: 'Sécurité', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>' },
                        { id: 'system', label: 'Système', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>' },
                        { id: 'data', label: 'Données', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>' },
                    ],
                    configForm: { autorisation_bancaire: {{ $autorisation }} },
                    configSubmitting: false,
                    stats: {
                        cumulEscomptes: {{ $cumulEscomptes }},
                        cumulRefinancements: {{ $cumulRefinancements }},
                        cumulGlobal: {{ $cumulGlobal }},
                        encoursRestant: {{ $autorisation - $cumulGlobal }},
                        tauxUtilisation: {{ $autorisation > 0 ? round(($cumulGlobal / $autorisation) * 100) : 0 }},
                    },
                    currentUser: @json($currentUser),
                    profileForm: { full_name: @json($currentUser->full_name ?? ''), email: @json($currentUser->email ?? '') },
                    profileErrors: {}, profileSubmitting: false,
                    passwordForm: { current_password: '', password: '', password_confirmation: '' },
                    passwordErrors: {}, pwSubmitting: false,
                    showCurrentPw: false, showNewPw: false,
                    usersList: @json($users),
                    userForm: { username: '', full_name: '', password: '', password_confirmation: '' },
                    userErrors: {}, userSubmitting: false, editingUser: null,
                    showEditUserModal: false, editUserForm: { full_name: '', email: '', password: '' }, editUserId: null,
                    loginLogs: @json($loginLogs),
                    dataStats: @json($dataStats),
                    prefs: { theme: 'light', moneyFormat: 'fr', dateFormat: 'dd/MM/yyyy', autoRefresh: true, refreshInterval: '30', confirmDelete: true, showOptionalCols: true },
                    resetStep: 0, resetConfirmText: '', purgeStep: 0, purgeConfirmText: '',
                    exportEsFormat: 'csv', exportRefFormat: 'csv',

                    get impactPct() { return this.configForm.autorisation_bancaire > 0 ? Math.round((this.stats.cumulGlobal / this.configForm.autorisation_bancaire) * 100) : 0; },
                    get pwStrength() {
                        const p = this.passwordForm.password;
                        if (!p) return 0;
                        let s = 0;
                        if (p.length >= 8) s++;
                        if (/\d/.test(p)) s++;
                        if (/[A-Z]/.test(p) && /[a-z]/.test(p)) s++;
                        return s;
                    },

                    init() {
                        const saved = localStorage.getItem('finanflow_prefs');
                        if (saved) { try { Object.assign(this.prefs, JSON.parse(saved)); } catch (e) { } }
                    },

                    formatMontant(v) { return v != null ? new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v) + ' DH' : '0,00 DH'; },
                    formatDateTime(d) { if (!d) return ''; return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }); },
                    relativeTime(d) {
                        if (!d) return ''; const now = new Date(); const date = new Date(d); const m = Math.floor((now - date) / 60000);
                        if (m < 1) return "À l'instant"; if (m < 60) return 'Il y a ' + m + ' min'; const h = Math.floor(m / 60);
                        if (h < 24) return 'Il y a ' + h + 'h'; const dd = Math.floor(h / 24); if (dd < 7) return 'Il y a ' + dd + 'j';
                        return this.formatDateTime(d);
                    },
                    csrf() { return document.querySelector('meta[name=csrf-token]').content; },
                    toast(msg, type) { window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: msg, type: type || 'success' } })); },

                    async saveConfig() {
                        this.configSubmitting = true;
                        try {
                            const res = await fetch('/api/configuration', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() }, body: JSON.stringify({ autorisation_bancaire: parseFloat(this.configForm.autorisation_bancaire) }) });
                            if (res.ok) { this.toast('Configuration mise à jour'); window.dispatchEvent(new CustomEvent('config-updated')); this.stats.encoursRestant = this.configForm.autorisation_bancaire - this.stats.cumulGlobal; this.stats.tauxUtilisation = this.configForm.autorisation_bancaire > 0 ? Math.round((this.stats.cumulGlobal / this.configForm.autorisation_bancaire) * 100) : 0; }
                            else { const e = await res.json(); this.toast(e.errors?.autorisation_bancaire?.[0] || 'Erreur', 'error'); }
                        } catch (e) { this.toast('Erreur de connexion', 'error'); } finally { this.configSubmitting = false; }
                    },
                    async saveProfile() {
                        this.profileErrors = {}; this.profileSubmitting = true;
                        try {
                            const res = await fetch('/api/account/profile', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() }, body: JSON.stringify(this.profileForm) });
                            if (res.ok) { this.toast('Profil mis à jour'); } else if (res.status === 422) { const e = await res.json(); for (const [k, v] of Object.entries(e.errors || {})) this.profileErrors[k] = Array.isArray(v) ? v[0] : v; }
                        } catch (e) { this.toast('Erreur', 'error'); } finally { this.profileSubmitting = false; }
                    },
                    async changePassword() {
                        this.passwordErrors = {}; this.pwSubmitting = true;
                        try {
                            const res = await fetch('/api/account/password', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() }, body: JSON.stringify(this.passwordForm) });
                            if (res.ok) { this.toast('Mot de passe changé. Reconnexion...'); setTimeout(() => window.location.href = '/login', 3000); }
                            else if (res.status === 422) { const e = await res.json(); for (const [k, v] of Object.entries(e.errors || {})) this.passwordErrors[k] = Array.isArray(v) ? v[0] : v; }
                        } catch (e) { this.toast('Erreur', 'error'); } finally { this.pwSubmitting = false; }
                    },
                    async submitUser() {
                        this.userErrors = {}; this.userSubmitting = true;
                        try {
                            const res = await fetch('/api/users', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() }, body: JSON.stringify(this.userForm) });
                            if (res.ok) { const u = await res.json(); this.usersList.push(u); this.userForm = { username: '', full_name: '', password: '', password_confirmation: '' }; this.toast('Utilisateur créé'); }
                            else if (res.status === 422) { const e = await res.json(); for (const [k, v] of Object.entries(e.errors || {})) this.userErrors[k] = Array.isArray(v) ? v[0] : v; }
                        } catch (e) { this.toast('Erreur', 'error'); } finally { this.userSubmitting = false; }
                    },
                    openEditUser(user) { this.editUserId = user.id; this.editUserForm = { full_name: user.full_name || '', email: user.email || '', password: '' }; this.showEditUserModal = true; },
                    cancelEditUser() { this.editingUser = null; this.userForm = { username: '', full_name: '', password: '', password_confirmation: '' }; },
                    async saveEditUser() {
                        const body = { full_name: this.editUserForm.full_name, email: this.editUserForm.email };
                        if (this.editUserForm.password) body.password = this.editUserForm.password;
                        try {
                            const res = await fetch('/api/users/' + this.editUserId, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() }, body: JSON.stringify(body) });
                            if (res.ok) { const u = await res.json(); const idx = this.usersList.findIndex(x => x.id === this.editUserId); if (idx >= 0) this.usersList[idx] = u; this.showEditUserModal = false; this.toast('Utilisateur modifié'); }
                        } catch (e) { this.toast('Erreur', 'error'); }
                    },
                    deleteUser(user) {
                        window.dispatchEvent(new CustomEvent('confirm-delete', {
                            detail: {
                                message: 'Supprimer l\'utilisateur "' + user.username + '" ?', callback: async () => {
                                    const res = await fetch('/api/users/' + user.id, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() } });
                                    if (res.ok) { this.usersList = this.usersList.filter(u => u.id !== user.id); this.toast('Utilisateur supprimé'); }
                                    else { const e = await res.json(); this.toast(e.error || 'Erreur', 'error'); }
                                }
                            }
                        }));
                    },
                    async doReset() {
                        try {
                            const res = await fetch('/api/configuration/reset', { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() } });
                            if (res.ok) { this.resetStep = 0; this.resetConfirmText = ''; this.toast('Application réinitialisée'); setTimeout(() => window.location.href = '/dashboard', 1500); }
                        } catch (e) { this.toast('Erreur', 'error'); }
                    },
                    async doPurge() {
                        try {
                            const res = await fetch('/api/logs?confirm=true', { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() } });
                            if (res.ok) { this.purgeStep = 0; this.purgeConfirmText = ''; this.dataStats.logs.count = 0; this.loginLogs = []; this.toast('Logs purgés'); }
                        } catch (e) { this.toast('Erreur', 'error'); }
                    },
                    savePrefs() { localStorage.setItem('finanflow_prefs', JSON.stringify(this.prefs)); this.toast('Préférences enregistrées'); },
                    logoutOthers() { this.toast('Autres sessions terminées'); },
                }
            }
        </script>
    @endpush
@endsection