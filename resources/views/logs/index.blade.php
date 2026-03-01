@extends('layouts.app')
@section('content')
    <div x-data="logsPage()" x-init="init()">

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Journal d'audit</h2>
                <p class="text-sm text-gray-500 mt-1">Historique des actions effectuées dans l'application</p>
            </div>
        </div>

        {{-- FILTERS --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
                <div class="xl:col-span-2">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" x-model.debounce.300ms="filters.search" @input="fetchData()"
                            placeholder="Rechercher dans les logs..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <select x-model="filters.category" @change="fetchData()"
                    class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Toutes catégories</option>
                    <option value="escompte">Escompte</option>
                    <option value="refinancement">Refinancement</option>
                    <option value="configuration">Configuration</option>
                    <option value="auth">Auth</option>
                    <option value="data">Data</option>
                    <option value="system">Système</option>
                    <option value="error">Erreur</option>
                </select>
                <select x-model="filters.action" @change="fetchData()"
                    class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Toutes actions</option>
                    <option value="CREATE">CREATE</option>
                    <option value="UPDATE">UPDATE</option>
                    <option value="DELETE">DELETE</option>
                    <option value="LOGIN">LOGIN</option>
                    <option value="LOGOUT">LOGOUT</option>
                    <option value="EXPORT">EXPORT</option>
                </select>
                <select x-model="filters.severity" @change="fetchData()"
                    class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Toutes sévérités</option>
                    <option value="CRITICAL">CRITICAL</option>
                    <option value="HIGH">HIGH</option>
                    <option value="MEDIUM">MEDIUM</option>
                    <option value="LOW">LOW</option>
                    <option value="info">Info</option>
                    <option value="warning">Warning</option>
                </select>

            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                <select x-model="filters.entityType" @change="fetchData()"
                    class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous types d'entité</option>
                    <option value="escompte">Escompte</option>
                    <option value="refinancement">Refinancement</option>
                    <option value="configuration">Configuration</option>
                    <option value="user">Utilisateur</option>
                    <option value="export">Export</option>
                </select>
                <input type="date" x-model="filters.dateStart" @change="fetchData()"
                    class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                    placeholder="Date début">
                <input type="date" x-model="filters.dateEnd" @change="fetchData()"
                    class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                    placeholder="Date fin">
            </div>
        </div>

        {{-- LOG ENTRIES --}}
        <div class="space-y-3">
            <template x-if="loading">
                <div class="bg-white rounded-2xl p-12 text-center text-gray-400 shadow-sm border border-gray-100">
                    <svg class="animate-spin h-8 w-8 mx-auto mb-2 text-blue-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Chargement...
                </div>
            </template>
            <template x-if="!loading && items.length === 0">
                <div class="bg-white rounded-2xl p-12 text-center text-gray-400 shadow-sm border border-gray-100">
                    Aucun log trouvé
                </div>
            </template>
            <template x-for="item in items" :key="item.id">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                    <div class="flex items-start gap-3">
                        {{-- Severity Badge --}}
                        <div class="flex-shrink-0 mt-0.5">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold"
                                :class="{
                                    'bg-red-100 text-red-700': item.severity === 'CRITICAL',
                                    'bg-orange-100 text-orange-700': item.severity === 'HIGH',
                                    'bg-yellow-100 text-yellow-700': item.severity === 'MEDIUM',
                                    'bg-blue-100 text-blue-700': item.severity === 'LOW',
                                    'bg-gray-100 text-gray-600': item.severity === 'info',
                                    'bg-amber-100 text-amber-700': item.severity === 'warning'
                                }"
                                x-text="item.severity"></span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                {{-- Action Badge --}}
                                <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg"
                                    x-text="item.action"></span>
                                {{-- Category --}}
                                <span class="text-xs text-gray-500" x-text="item.category"></span>
                                {{-- Timestamp --}}
                                <span class="text-xs text-gray-400 ml-auto" x-text="relativeTime(item.timestamp)"
                                    :title="item.timestamp"></span>
                            </div>

                            {{-- Message --}}
                            <p class="text-sm font-medium text-gray-800" x-text="item.message || item.description"></p>
                            <p x-show="item.description && item.message" class="text-xs text-gray-500 mt-0.5"
                                x-text="item.description"></p>

                            {{-- Entity info --}}
                            <div x-show="item.entity_type" class="flex items-center gap-2 mt-2 text-xs text-gray-500">
                                <span class="px-2 py-0.5 bg-gray-100 rounded text-gray-600"
                                    x-text="item.entity_type"></span>
                                <span x-show="item.entity_id" x-text="'#' + item.entity_id" class="font-mono"></span>
                                <span x-show="item.user_id">par <span class="font-semibold"
                                        x-text="item.user_id"></span></span>
                            </div>

                            {{-- Changes (expandable) --}}
                            <div x-show="item.changes" class="mt-3" x-data="{ expanded: false }">
                                <button @click="expanded = !expanded"
                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                                    <svg class="w-3 h-3 transition-transform" :class="expanded ? 'rotate-90' : ''"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                    Voir les modifications
                                </button>
                                <div x-show="expanded" x-collapse
                                    class="mt-2 p-3 bg-gray-50 rounded-xl text-xs font-mono overflow-x-auto">
                                    <pre x-text="JSON.stringify(item.changes, null, 2)" class="whitespace-pre-wrap text-gray-700"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-4 bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3 flex flex-wrap items-center justify-between gap-3"
            x-show="total > 0">
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <span>Afficher</span>
                <select x-model="limit" @change="page = 1; fetchData()"
                    class="border border-gray-300 rounded-lg px-2 py-1 text-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>sur <span class="font-semibold" x-text="total"></span> logs</span>
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

    @push('scripts')
        <script>
            function logsPage() {
                return {
                    items: [],
                    total: 0,
                    page: 1,
                    limit: 50,
                    totalPages: 1,
                    loading: false,
                    filters: {
                        search: '',
                        category: '',
                        action: '',
                        severity: '',
                        entityType: '',
                        dateStart: '',
                        dateEnd: ''
                    },

                    relativeTime(dateStr) {
                        if (!dateStr) return '';
                        const now = new Date();
                        const d = new Date(dateStr);
                        const diffMs = now - d;
                        const diffMin = Math.floor(diffMs / 60000);
                        const diffH = Math.floor(diffMin / 60);
                        const diffD = Math.floor(diffH / 24);
                        if (diffMin < 1) return 'À l\'instant';
                        if (diffMin < 60) return `Il y a ${diffMin} min`;
                        if (diffH < 24) return `Il y a ${diffH}h`;
                        if (diffD < 7) return `Il y a ${diffD}j`;
                        return d.toLocaleDateString('fr-FR', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric'
                        });
                    },

                    async init() {
                        await this.fetchData();
                    },

                    async fetchData() {
                        this.loading = true;
                        const params = new URLSearchParams({
                            page: this.page,
                            limit: this.limit
                        });
                        for (const [k, v] of Object.entries(this.filters)) {
                            if (v) params.set(k, v);
                        }
                        try {
                            const res = await fetch('/api/logs?' + params, {
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

                    resetFilters() {
                        this.filters = {
                            search: '',
                            category: '',
                            action: '',
                            severity: '',
                            entityType: '',
                            dateStart: '',
                            dateEnd: ''
                        };
                        this.page = 1;
                        this.fetchData();
                    }
                }
            }
        </script>
    @endpush
@endsection
