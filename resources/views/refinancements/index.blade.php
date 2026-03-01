@extends('layouts.app')
@section('content')
    <div x-data="refinancementsPage()" x-init="init()">

        {{-- KPI CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Cumul Refinancé</span>
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-800" x-text="formatMontant(kpi.cumulRefinancements)"></p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre de
                        refinancements</span>
                    <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-800" x-text="kpi.nombreRefinancements"></p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Intérêts</span>
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-800" x-text="formatMontant(totalInterets)"></p>
            </div>
        </div>

        {{-- TOOLBAR --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
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
                                        <span x-show="sortField === col.field" x-text="sortDirection === 'asc' ? '↑' : '↓'"
                                            class="text-blue-600"></span>
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
                                    <svg class="animate-spin h-8 w-8 mx-auto mb-2 text-blue-500" fill="none"
                                        viewBox="0 0 24 24">
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
                                    refinancement trouvé</td>
                            </tr>
                        </template>
                        <template x-for="item in items" :key="item.id">
                            <tr class="hover:bg-blue-50/30 transition">
                                <td class="px-4 py-3 font-medium text-gray-600" x-text="item.ordre_saisie"></td>
                                <td class="px-4 py-3 font-medium text-gray-800" x-text="item.libelle"></td>
                                <td class="px-4 py-3 font-semibold text-gray-800"
                                    x-text="formatMontant(item.montant_refinance)"></td>
                                <td class="px-4 py-3 text-gray-600" x-text="item.taux_interet + ' %'"></td>
                                <td class="px-4 py-3 text-gray-600" x-text="item.duree_en_mois + ' mois'"></td>
                                <td class="px-4 py-3 text-gray-600" x-text="formatDate(item.date_refinancement)"></td>
                                <td class="px-4 py-3 text-gray-600" x-text="formatMontant(item.encours_refinance)"></td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                        :class="{ 'bg-green-100 text-green-700': item
                                            .statut === 'ACTIF', 'bg-gray-100 text-gray-600': item
                                                .statut === 'TERMINE', 'bg-orange-100 text-orange-700': item
                                                .statut === 'SUSPENDU' }"
                                        x-text="item.statut"></span>
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-800"
                                    x-text="formatMontant(item.total_interets)"></td>
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
                        class="border border-gray-300 rounded-lg px-2 py-1 text-sm">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span>sur <span class="font-semibold" x-text="total"></span> résultats</span>
                </div>
                <div class="flex items-center gap-1">
                    <button @click="page = Math.max(1, page - 1); fetchData()" :disabled="page <= 1"
                        class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-40 transition">Précédent</button>
                    <span class="px-3 py-1.5 text-sm text-gray-600">Page <span x-text="page"></span> / <span
                            x-text="totalPages"></span></span>
                    <button @click="page = Math.min(totalPages, page + 1); fetchData()" :disabled="page >= totalPages"
                        class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-40 transition">Suivant</button>
                </div>
            </div>
        </div>

        {{-- CREATE/EDIT MODAL --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" @click="showModal = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto"
                @click.stop>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-800"
                        x-text="editingItem ? 'Modifier le refinancement' : 'Nouveau refinancement'"></h2>
                    <button @click="showModal = false"
                        class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Libellé <span
                                class="text-red-500">*</span></label>
                        <input type="text" x-model="form.libelle"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500"
                            placeholder="Ex: Refinancement Crédit Immobilier">
                        <p x-show="formErrors.libelle" class="mt-1 text-xs text-red-600" x-text="formErrors.libelle"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant Refinancé (DH) <span
                                class="text-red-500">*</span></label>
                        <input type="number" x-model="form.montant_refinance" step="0.01" @input="calcInterets()"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500"
                            placeholder="0,00">
                        <p x-show="formErrors.montant_refinance" class="mt-1 text-xs text-red-600"
                            x-text="formErrors.montant_refinance"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Taux Intérêt (%) <span
                                class="text-red-500">*</span></label>
                        <input type="number" x-model="form.taux_interet" step="0.01" min="0" max="100"
                            @input="calcInterets()"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500"
                            placeholder="0,00">
                        <p x-show="formErrors.taux_interet" class="mt-1 text-xs text-red-600"
                            x-text="formErrors.taux_interet"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Refinancement <span
                                class="text-red-500">*</span></label>
                        <input type="date" x-model="form.date_refinancement"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                        <p x-show="formErrors.date_refinancement" class="mt-1 text-xs text-red-600"
                            x-text="formErrors.date_refinancement"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Durée (mois) <span
                                class="text-red-500">*</span></label>
                        <input type="number" x-model="form.duree_en_mois" min="1" max="360"
                            @input="calcInterets()"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500"
                            placeholder="12">
                        <p x-show="formErrors.duree_en_mois" class="mt-1 text-xs text-red-600"
                            x-text="formErrors.duree_en_mois"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Encours Refinancé (DH) <span
                                class="text-red-500">*</span></label>
                        <input type="number" x-model="form.encours_refinance" step="0.01" min="0"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500"
                            placeholder="0,00">
                        <p x-show="formErrors.encours_refinance" class="mt-1 text-xs text-red-600"
                            x-text="formErrors.encours_refinance"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Frais de Dossier (DH)</label>
                        <input type="number" x-model="form.frais_dossier" step="0.01" min="0"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500"
                            placeholder="0,00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut <span
                                class="text-red-500">*</span></label>
                        <select x-model="form.statut"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="ACTIF">ACTIF</option>
                            <option value="TERMINE">TERMINE</option>
                            <option value="SUSPENDU">SUSPENDU</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Conditions</label>
                        <textarea x-model="form.conditions" maxlength="500" rows="3"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500"
                            placeholder="Conditions spécifiques..."></textarea>
                        <p class="mt-1 text-xs text-gray-400"><span x-text="(form.conditions || '').length"></span>/500
                            caractères</p>
                    </div>
                </div>

                {{-- Total Interets Preview --}}
                <div class="mt-4 p-4 bg-indigo-50 border border-indigo-200 rounded-xl">
                    <p class="text-sm font-medium text-indigo-800">Total Intérêts calculé :</p>
                    <p class="text-xl font-bold text-indigo-700" x-text="formatMontant(calculatedInterets)"></p>
                    <p class="text-xs text-indigo-600 mt-1">Formule : montant × (taux / 100) × (durée / 12)</p>
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
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Exporter les refinancements</h2>
                <div class="flex gap-3 mb-6">
                    <label class="flex items-center gap-2 px-4 py-3 border rounded-xl cursor-pointer transition"
                        :class="exportFormat === 'csv' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'">
                        <input type="radio" x-model="exportFormat" value="csv" class="text-blue-600"><span
                            class="text-sm font-medium">CSV</span>
                    </label>
                    <label class="flex items-center gap-2 px-4 py-3 border rounded-xl cursor-pointer transition"
                        :class="exportFormat === 'xlsx' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'">
                        <input type="radio" x-model="exportFormat" value="xlsx" class="text-blue-600"><span
                            class="text-sm font-medium">Excel</span>
                    </label>
                </div>
                <div class="flex justify-end gap-3">
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
            function refinancementsPage() {
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
                        recherche: ''
                    },
                    showModal: false,
                    showExportModal: false,
                    editingItem: null,
                    exportFormat: 'csv',
                    form: {
                        libelle: '',
                        montant_refinance: '',
                        taux_interet: '',
                        date_refinancement: '',
                        duree_en_mois: 12,
                        encours_refinance: '',
                        frais_dossier: 0,
                        conditions: '',
                        statut: 'ACTIF'
                    },
                    formErrors: {},
                    kpi: {
                        cumulRefinancements: 0,
                        nombreRefinancements: 0
                    },
                    totalInterets: 0,
                    calculatedInterets: 0,
                    columns: [{
                            field: 'ordre_saisie',
                            label: 'Ordre'
                        },
                        {
                            field: 'libelle',
                            label: 'Libellé'
                        },
                        {
                            field: 'montant_refinance',
                            label: 'Montant Refinancé'
                        },
                        {
                            field: 'taux_interet',
                            label: 'Taux (%)'
                        },
                        {
                            field: 'duree_en_mois',
                            label: 'Durée'
                        },
                        {
                            field: 'date_refinancement',
                            label: 'Date'
                        },
                        {
                            field: 'encours_refinance',
                            label: 'Encours'
                        },
                        {
                            field: 'statut',
                            label: 'Statut'
                        },
                        {
                            field: 'total_interets',
                            label: 'Total Intérêts'
                        },
                    ],

                    formatMontant(v) {
                        return v != null ? new Intl.NumberFormat('fr-FR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }).format(v) + ' DH' : '0,00 DH';
                    },
                    formatDate(d) {
                        if (!d) return '';
                        return new Date(d).toLocaleDateString('fr-FR', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric'
                        });
                    },

                    calcInterets() {
                        const m = parseFloat(this.form.montant_refinance) || 0;
                        const t = parseFloat(this.form.taux_interet) || 0;
                        const d = parseInt(this.form.duree_en_mois) || 0;
                        this.calculatedInterets = Math.round(m * (t / 100) * (d / 12) * 100) / 100;
                    },

                    async init() {
                        await this.fetchKpi();
                        await this.fetchData();
                        this._kpiInterval = setInterval(() => this.fetchKpi(), 30000);
                        window.addEventListener('config-updated', () => this.fetchKpi());
                    },

                    async fetchKpi() {
                        try {
                            const res = await fetch('/api/dashboard/kpi', {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                }
                            });
                            if (res.ok) {
                                const d = await res.json();
                                this.kpi = d;
                            }
                        } catch (e) {}
                        // Compute total interets from items
                        this.totalInterets = this.items.reduce((s, i) => s + parseFloat(i.total_interets || 0), 0);
                    },

                    async fetchData() {
                        this.loading = true;
                        const params = new URLSearchParams({
                            page: this.page,
                            limit: this.limit,
                            sortField: this.sortField,
                            sortDirection: this.sortDirection
                        });
                        if (this.filters.recherche) params.set('recherche', this.filters.recherche);
                        try {
                            const res = await fetch('/api/refinancements?' + params, {
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
                                this.totalInterets = this.items.reduce((s, i) => s + parseFloat(i.total_interets || 0), 0);
                            }
                        } catch (e) {} finally {
                            this.loading = false;
                        }
                    },

                    toggleSort(field) {
                        if (this.sortField === field) this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                        else {
                            this.sortField = field;
                            this.sortDirection = 'asc';
                        }
                        this.fetchData();
                    },

                    openCreateModal() {
                        this.editingItem = null;
                        this.form = {
                            libelle: '',
                            montant_refinance: '',
                            taux_interet: '',
                            date_refinancement: new Date().toISOString().split('T')[0],
                            duree_en_mois: 12,
                            encours_refinance: '',
                            frais_dossier: 0,
                            conditions: '',
                            statut: 'ACTIF'
                        };
                        this.formErrors = {};
                        this.calculatedInterets = 0;
                        this.showModal = true;
                    },

                    openEditModal(item) {
                        this.editingItem = item;
                        this.form = {
                            libelle: item.libelle,
                            montant_refinance: item.montant_refinance,
                            taux_interet: item.taux_interet,
                            date_refinancement: item.date_refinancement?.split('T')[0] || item.date_refinancement,
                            duree_en_mois: item.duree_en_mois,
                            encours_refinance: item.encours_refinance,
                            frais_dossier: item.frais_dossier || 0,
                            conditions: item.conditions || '',
                            statut: item.statut
                        };
                        this.formErrors = {};
                        this.calcInterets();
                        this.showModal = true;
                    },

                    async submitForm() {
                        this.formErrors = {};
                        this.submitting = true;
                        const method = this.editingItem ? 'PUT' : 'POST';
                        const url = this.editingItem ? `/api/refinancements/${this.editingItem.id}` : '/api/refinancements';
                        const body = {
                            ...this.form,
                            montant_refinance: parseFloat(this.form.montant_refinance),
                            taux_interet: parseFloat(this.form.taux_interet),
                            duree_en_mois: parseInt(this.form.duree_en_mois),
                            encours_refinance: parseFloat(this.form.encours_refinance),
                            frais_dossier: parseFloat(this.form.frais_dossier) || 0
                        };
                        try {
                            const res = await fetch(url, {
                                method,
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                },
                                body: JSON.stringify(body)
                            });
                            if (res.ok) {
                                this.showModal = false;
                                this.fetchData();
                                this.fetchKpi();
                                window.dispatchEvent(new CustomEvent('show-toast', {
                                    detail: { message: this.editingItem ? 'Refinancement modifié' : 'Refinancement créé' }
                                }));
                            } else if (res.status === 422) {
                                const err = await res.json();
                                for (const [k, v] of Object.entries(err.errors || {})) this.formErrors[k] = Array.isArray(
                                    v) ? v[0] : v;
                            }
                        } catch (e) {} finally {
                            this.submitting = false;
                        }
                    },

                    deleteItem(item) {
                        const self = this;
                        window.dispatchEvent(new CustomEvent('confirm-delete', {
                            detail: {
                                message: `Supprimer "${item.libelle}" ?`,
                                callback: async () => {
                                    const res = await fetch(`/api/refinancements/${item.id}`, {
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
                                            detail: { message: 'Refinancement supprimé' }
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
                        if (this.filters.recherche) params.set('recherche', this.filters.recherche);
                        window.location.href = '/api/refinancements/export?' + params;
                        this.showExportModal = false;
                    }
                }
            }
        </script>
    @endpush
@endsection
