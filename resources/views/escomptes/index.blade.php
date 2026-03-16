@extends('layouts.app')
@section('content')
    <div x-data="escomptesPage()" x-init="init()">

        {{-- KPI CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Cumul Total</span>
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-800" x-text="formatMontant(kpi.cumulTotal)"></p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Encours Restant</span>
                    <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold" :class="kpi.encoursRestant >= 0 ? 'text-green-600' : 'text-red-600'"
                    x-text="formatMontant(kpi.encoursRestant)"></p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Utilisation</span>
                    <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-800"><span x-text="kpi.pourcentageUtilisation"></span> %</p>
                <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all duration-500"
                        :class="kpi.pourcentageUtilisation > 90 ? 'bg-red-500' : kpi.pourcentageUtilisation > 70 ?
                            'bg-orange-500' : 'bg-blue-500'"
                        :style="'width:' + Math.min(kpi.pourcentageUtilisation, 100) + '%'"></div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre d'escomptes</span>
                    <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-800" x-text="kpi.nombreEscomptes"></p>
            </div>
        </div>

        {{-- TOOLBAR --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-3 flex-1">
                    {{-- Search --}}
                    <div class="relative flex-1 min-w-[200px] max-w-md">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" x-model.debounce.300ms="filters.recherche" @input="fetchData()"
                            placeholder="Rechercher par libellé..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    {{-- Filters Toggle --}}
                    <button @click="showFilters = !showFilters"
                        class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filtres
                        <span x-show="activeFilterCount > 0"
                            class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded-full"
                            x-text="activeFilterCount"></span>
                    </button>

                    {{-- Reset --}}
                    <button x-show="activeFilterCount > 0" @click="resetFilters()"
                        class="px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-xl transition">
                        Réinitialiser
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="showExportModal = true"
                        class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-xl hover:bg-green-700 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Exporter
                    </button>
                    <button @click="openCreateModal()"
                        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nouveau
                    </button>
                </div>
            </div>

            {{-- Extended Filters --}}
            <div x-show="showFilters" x-collapse class="mt-4 pt-4 border-t border-gray-200">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date début</label>
                        <input type="date" x-model="filters.dateDebut" @change="fetchData()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date fin</label>
                        <input type="date" x-model="filters.dateFin" @change="fetchData()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Montant min</label>
                        <input type="number" x-model="filters.montantMin" @change="fetchData()" step="0.01"
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500"
                            placeholder="0,00">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Montant max</label>
                        <input type="number" x-model="filters.montantMax" @change="fetchData()" step="0.01"
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500"
                            placeholder="999 999,99">
                    </div>
                </div>
            </div>
        </div>

        {{-- DATA TABLE --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <template x-for="col in columns" :key="col.field">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition select-none"
                                    @click="toggleSort(col.field)">
                                    <div class="flex items-center gap-1">
                                        <span x-text="col.label"></span>
                                        <span x-show="sortField === col.field"
                                            x-text="sortDirection === 'asc' ? '↑' : '↓'" class="text-blue-600"></span>
                                    </div>
                                </th>
                            </template>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-if="loading">
                            <tr>
                                <td :colspan="columns.length + 1" class="px-4 py-12 text-center text-gray-400">
                                    <svg class="animate-spin h-8 w-8 mx-auto mb-2 text-blue-500"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Chargement...
                                </td>
                            </tr>
                        </template>
                        <template x-if="!loading && items.length === 0">
                            <tr>
                                <td :colspan="columns.length + 1" class="px-4 py-12 text-center text-gray-400">Aucun
                                    escompte trouvé</td>
                            </tr>
                        </template>
                        <template x-for="item in items" :key="item.id">
                            <tr class="hover:bg-blue-50/30 transition">
                                <td class="px-4 py-3 font-medium text-gray-600" x-text="item.ordre_saisie"></td>
                                <td class="px-4 py-3 text-gray-600" x-text="item.numero_effet || '—'"></td>
                                <td class="px-4 py-3 text-gray-600" x-text="item.nom_tireur || '—'"></td>
                                <td class="px-4 py-3 font-medium text-gray-800" x-text="item.libelle"></td>
                                <td class="px-4 py-3 text-gray-600" x-text="formatDate(item.date_remise)"></td>
                                <td class="px-4 py-3 font-semibold text-gray-800" x-text="formatMontant(item.montant)">
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                        :class="{ 'bg-green-100 text-green-700': item
                                            .statut === 'ACTIF', 'bg-gray-100 text-gray-600': item
                                                .statut === 'TERMINE', 'bg-orange-100 text-orange-700': item
                                                .statut === 'SUSPENDU' }"
                                        x-text="item.statut"></span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button @click="openEditModal(item)"
                                            class="p-1.5 rounded-lg hover:bg-blue-100 text-gray-400 hover:text-blue-600 transition"
                                            title="Modifier">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button @click="deleteItem(item)"
                                            class="p-1.5 rounded-lg hover:bg-red-100 text-gray-400 hover:text-red-600 transition"
                                            title="Supprimer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="px-4 py-3 border-t border-gray-200 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <span>Afficher</span>
                    <select x-model="limit" @change="page = 1; fetchData()"
                        class="border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-blue-500">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span>sur <span class="font-semibold" x-text="total"></span> résultats</span>
                </div>
                <div class="flex items-center gap-1">
                    <button @click="page = Math.max(1, page - 1); fetchData()" :disabled="page <= 1"
                        class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition">Précédent</button>
                    <span class="px-3 py-1.5 text-sm text-gray-600">Page <span x-text="page"></span> / <span
                            x-text="totalPages"></span></span>
                    <button @click="page = Math.min(totalPages, page + 1); fetchData()" :disabled="page >= totalPages"
                        class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition">Suivant</button>
                </div>
            </div>
        </div>

        {{-- ESCOMPTE MODAL (Create/Edit) --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" @click="showModal = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6" @click.stop>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-800"
                        x-text="editingItem ? 'Modifier l\'escompte' : 'Nouvel escompte'"></h2>
                    <button @click="showModal = false"
                        class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom du Tireur</label>
                        <input type="text" x-model="form.nom_tireur" maxlength="255"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ex: Société ABC">
                        <p x-show="formErrors.nom_tireur" class="mt-1 text-xs text-red-600" x-text="formErrors.nom_tireur"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de remise <span
                                class="text-red-500">*</span></label>
                        <input type="date" x-model="form.date_remise"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p x-show="formErrors.date_remise" class="mt-1 text-xs text-red-600"
                            x-text="formErrors.date_remise"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Libellé <span
                                class="text-red-500">*</span></label>
                        <input type="text" x-model="form.libelle" maxlength="255"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ex: Escompte commercial">
                        <p x-show="formErrors.libelle" class="mt-1 text-xs text-red-600" x-text="formErrors.libelle"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant (DH) <span
                                class="text-red-500">*</span></label>
                        <input type="number" x-model="form.montant" step="0.01" min="0"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="0,00">
                        <p x-show="formErrors.montant" class="mt-1 text-xs text-red-600" x-text="formErrors.montant"></p>
                        <div x-show="form.montant > 0" class="mt-2 p-3 rounded-xl text-xs"
                            :class="montantExceedsAuth ? 'bg-red-50 text-red-700 border border-red-200' :
                                'bg-blue-50 text-blue-700 border border-blue-200'">
                            <p>Impact : Nouveau cumul = <span class="font-semibold"
                                    x-text="formatMontant(impactPreview.newCumul)"></span></p>
                            <p>Encours restant = <span class="font-semibold"
                                    x-text="formatMontant(impactPreview.newEncours)"></span></p>
                            <p x-show="montantExceedsAuth" class="font-semibold mt-1">⚠️ Dépasse l'autorisation bancaire !
                            </p>
                        </div>
                    </div>
                    <div x-show="editingItem">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                        <select x-model="form.statut"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="ACTIF">ACTIF</option>
                            <option value="TERMINE">TERMINÉ</option>
                            <option value="SUSPENDU">SUSPENDU</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="showModal = false"
                        class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition">Annuler</button>
                    <button @click="submitForm()" :disabled="submitting"
                        class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition disabled:opacity-50">
                        <span x-show="!submitting" x-text="editingItem ? 'Modifier' : 'Créer'"></span>
                        <span x-show="submitting">Enregistrement...</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- EXPORT MODAL --}}
        <div x-show="showExportModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" @click="showExportModal = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-800">Exporter les escomptes</h2>
                    <button @click="showExportModal = false"
                        class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Format d'export</label>
                        <div class="flex gap-3">
                            <label class="flex items-center gap-2 px-4 py-3 border rounded-xl cursor-pointer transition"
                                :class="exportFormat === 'csv' ? 'border-blue-500 bg-blue-50' :
                                    'border-gray-300 hover:border-gray-400'">
                                <input type="radio" x-model="exportFormat" value="csv" class="text-blue-600">
                                <span class="text-sm font-medium">CSV</span>
                            </label>
                            <label class="flex items-center gap-2 px-4 py-3 border rounded-xl cursor-pointer transition"
                                :class="exportFormat === 'xlsx' ? 'border-blue-500 bg-blue-50' :
                                    'border-gray-300 hover:border-gray-400'">
                                <input type="radio" x-model="exportFormat" value="xlsx" class="text-blue-600">
                                <span class="text-sm font-medium">Excel (XLSX)</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="showExportModal = false"
                        class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition">Annuler</button>
                    <button @click="doExport()"
                        class="px-4 py-2.5 text-sm font-medium text-white bg-green-600 rounded-xl hover:bg-green-700 transition">Télécharger</button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            function escomptesPage() {
                return {
                    items: [],
                    total: 0,
                    page: 1,
                    limit: 10,
                    totalPages: 1,
                    loading: false,
                    submitting: false,
                    sortField: 'ordre_saisie',
                    sortDirection: 'asc',
                    filters: {
                        recherche: '',
                        dateDebut: '',
                        dateFin: '',
                        montantMin: '',
                        montantMax: ''
                    },
                    showFilters: false,
                    showModal: false,
                    showExportModal: false,
                    editingItem: null,
                    exportFormat: 'csv',
                    form: {
                        date_remise: '',
                        libelle: '',
                        montant: '',
                        nom_tireur: '',
                        statut: 'ACTIF'
                    },
                    formErrors: {},
                    kpi: {
                        cumulTotal: 0,
                        encoursRestant: 0,
                        pourcentageUtilisation: 0,
                        nombreEscomptes: 0,
                        autorisationBancaire: 200000
                    },
                    columns: [{
                            field: 'ordre_saisie',
                            label: 'Ordre'
                        },
                        {
                            field: 'numero_effet',
                            label: 'N° Effet'
                        },
                        {
                            field: 'nom_tireur',
                            label: 'Nom Tireur'
                        },
                        {
                            field: 'libelle',
                            label: 'Libellé'
                        },
                        {
                            field: 'date_remise',
                            label: 'Date Remise'
                        },
                        {
                            field: 'montant',
                            label: 'Montant'
                        },
                        {
                            field: 'statut',
                            label: 'Statut'
                        },
                    ],

                    get activeFilterCount() {
                        let c = 0;
                        if (this.filters.recherche) c++;
                        if (this.filters.dateDebut) c++;
                        if (this.filters.dateFin) c++;
                        if (this.filters.montantMin) c++;
                        if (this.filters.montantMax) c++;
                        return c;
                    },

                    get montantExceedsAuth() {
                        const currentMontant = parseFloat(this.form.montant) || 0;
                        const cumul = this.kpi.cumulTotal - (this.editingItem ? parseFloat(this.editingItem.montant) : 0);
                        return (cumul + currentMontant) > this.kpi.autorisationBancaire;
                    },

                    get impactPreview() {
                        const currentMontant = parseFloat(this.form.montant) || 0;
                        const cumul = this.kpi.cumulTotal - (this.editingItem ? parseFloat(this.editingItem.montant) : 0);
                        const newCumul = cumul + currentMontant;
                        return {
                            newCumul,
                            newEncours: this.kpi.autorisationBancaire - newCumul
                        };
                    },

                    formatMontant(v) {
                        return window.ffFormatMontant(v);
                    },
                    formatDate(d) {
                        return window.ffFormatDate(d);
                    },

                    async init() {
                        await this.fetchKpi();
                        await this.fetchData();
                        this._kpiInterval = setInterval(() => this.fetchKpi(), 30000);
                        window.addEventListener('config-updated', () => {
                            this.fetchKpi();
                            this.fetchData();
                        });
                    },

                    async fetchKpi() {
                        try {
                            const res = await fetch('/api/dashboard/kpi', {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                }
                            });
                            if (res.ok) this.kpi = await res.json();
                        } catch (e) {}
                    },

                    async fetchData() {
                        this.loading = true;
                        const params = new URLSearchParams({
                            page: this.page,
                            limit: this.limit,
                            sortField: this.sortField,
                            sortDirection: this.sortDirection
                        });
                        for (const [k, v] of Object.entries(this.filters)) {
                            if (v) params.set(k, v);
                        }
                        try {
                            const res = await fetch('/api/escomptes?' + params, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                }
                            });
                            if (res.ok) {
                                const data = await res.json();
                                this.items = data.data;
                                this.total = data.total;
                                this.totalPages = data.totalPages;
                            }
                        } catch (e) {} finally {
                            this.loading = false;
                        }
                    },

                    toggleSort(field) {
                        if (this.sortField === field) {
                            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                        } else {
                            this.sortField = field;
                            this.sortDirection = 'asc';
                        }
                        this.fetchData();
                    },

                    resetFilters() {
                        this.filters = {
                            recherche: '',
                            dateDebut: '',
                            dateFin: '',
                            montantMin: '',
                            montantMax: ''
                        };
                        this.page = 1;
                        this.fetchData();
                    },

                    openCreateModal() {
                        this.editingItem = null;
                        this.form = {
                            date_remise: new Date().toISOString().split('T')[0],
                            libelle: '',
                            montant: '',
                            nom_tireur: '',
                            statut: 'ACTIF'
                        };
                        this.formErrors = {};
                        this.showModal = true;
                    },

                    openEditModal(item) {
                        this.editingItem = item;
                        this.form = {
                            date_remise: item.date_remise?.split('T')[0] || item.date_remise,
                            libelle: item.libelle,
                            montant: item.montant,
                            nom_tireur: item.nom_tireur || '',
                            statut: item.statut || 'ACTIF'
                        };
                        this.formErrors = {};
                        this.showModal = true;
                    },

                    async submitForm() {
                        this.formErrors = {};
                        this.submitting = true;
                        const method = this.editingItem ? 'PUT' : 'POST';
                        const url = this.editingItem ? `/api/escomptes/${this.editingItem.id}` : '/api/escomptes';
                        try {
                            const res = await fetch(url, {
                                method,
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                },
                                body: JSON.stringify({
                                    ...this.form,
                                    montant: parseFloat(this.form.montant),
                                    nom_tireur: this.form.nom_tireur || null,
                                    statut: this.editingItem ? this.form.statut : undefined
                                })
                            });
                            if (res.ok) {
                                this.showModal = false;
                                this.fetchData();
                                this.fetchKpi();
                                window.dispatchEvent(new CustomEvent('show-toast', {
                                    detail: { message: this.editingItem ? 'Escompte modifié avec succès' : 'Escompte créé avec succès' }
                                }));
                            } else if (res.status === 422) {
                                const err = await res.json();
                                this.formErrors = {};
                                for (const [k, v] of Object.entries(err.errors || {})) {
                                    this.formErrors[k] = Array.isArray(v) ? v[0] : v;
                                }
                            }
                        } catch (e) {} finally {
                            this.submitting = false;
                        }
                    },

                    deleteItem(item) {
                        const self = this;
                        window.dispatchEvent(new CustomEvent('confirm-delete', {
                            detail: {
                                message: `Êtes-vous sûr de vouloir supprimer l'escompte "${item.libelle}" ?`,
                                callback: async () => {
                                    const res = await fetch(`/api/escomptes/${item.id}`, {
                                        method: 'DELETE',
                                        headers: {
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                        }
                                    });
                                    if (res.ok) {
                                        self.fetchData();
                                        self.fetchKpi();
                                        window.dispatchEvent(new CustomEvent('show-toast', {
                                            detail: { message: 'Escompte supprimé avec succès' }
                                        }));
                                    }
                                }
                            }
                        }));
                    },

                    doExport() {
                        const params = new URLSearchParams({
                            format: this.exportFormat
                        });
                        for (const [k, v] of Object.entries(this.filters)) {
                            if (v) params.set(k, v);
                        }
                        window.location.href = '/api/escomptes/export?' + params;
                        this.showExportModal = false;
                    }
                }
            }
        </script>
    @endpush
@endsection
